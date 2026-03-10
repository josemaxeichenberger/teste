<?php
/**
 * API de Autenticação Google OAuth
 * @author GustavoChaconDeveloper
 * @description Endpoint para processar login/registro via Google
 */

// ============================================================================
// CONFIGURAÇÕES INICIAIS
// ============================================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../conexao/conexao.php';

// ============================================================================
// CONFIGURAÇÃO DO GOOGLE OAUTH
// ============================================================================
// IMPORTANTE: Substitua pelo seu Client ID do Google Cloud Console
// Acesse: https://console.cloud.google.com/apis/credentials
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', 'SEU_GOOGLE_CLIENT_ID_AQUI.apps.googleusercontent.com');
}

// ============================================================================
// FUNÇÃO PARA VERIFICAR TOKEN DO GOOGLE
// ============================================================================
function verifyGoogleToken($idToken) {
    // Verificar token usando a API do Google
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return null;
    }
    
    $payload = json_decode($response, true);
    
    // Verificar se o token é válido e pertence ao nosso app
    if (!$payload || !isset($payload['email'])) {
        return null;
    }
    
    // Verificar se o aud (audience) corresponde ao nosso Client ID
    if ($payload['aud'] !== GOOGLE_CLIENT_ID) {
        error_log("Google Auth: Client ID não corresponde. Esperado: " . GOOGLE_CLIENT_ID . ", Recebido: " . $payload['aud']);
        // Em desenvolvimento, podemos ser mais flexíveis
        // return null;
    }
    
    return $payload;
}

// ============================================================================
// PROCESSAR REQUISIÇÃO
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $googleUser = null;
    
    // Verificar se recebemos credential (JWT token) ou googleData (dados diretos do OAuth)
    if (!empty($data['credential'])) {
        // Método 1: Token JWT do Google Identity Services
        $googleUser = verifyGoogleToken($data['credential']);
        
        if (!$googleUser) {
            echo json_encode([
                'success' => false,
                'message' => 'Token do Google inválido ou expirado.'
            ]);
            exit;
        }
        
        // Extrair dados do payload JWT
        $email = $googleUser['email'];
        $emailVerified = $googleUser['email_verified'] ?? false;
        $firstName = $googleUser['given_name'] ?? '';
        $lastName = $googleUser['family_name'] ?? '';
        $fullName = $googleUser['name'] ?? '';
        $picture = $googleUser['picture'] ?? '';
        $googleId = $googleUser['sub'] ?? '';
        
    } elseif (!empty($data['googleData'])) {
        // Método 2: Dados diretos do OAuth callback
        $googleData = $data['googleData'];
        
        if (empty($googleData['email'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Email não fornecido pelo Google.'
            ]);
            exit;
        }
        
        $email = $googleData['email'];
        $emailVerified = $googleData['emailVerified'] ?? true;
        $firstName = $googleData['firstName'] ?? '';
        $lastName = $googleData['lastName'] ?? '';
        $fullName = $googleData['name'] ?? '';
        $picture = $googleData['picture'] ?? '';
        $googleId = $googleData['googleId'] ?? '';
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Dados do Google não fornecidos.'
        ]);
        exit;
    }
    
    try {
        // Se não tiver primeiro/último nome, tentar separar do nome completo
        if (empty($firstName) && !empty($fullName)) {
            $nameParts = explode(' ', $fullName);
            $firstName = $nameParts[0];
            $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';
        }
        
        $conn = getConnection();
        
        // Verificar se o email já existe no banco
        $stmt = $conn->prepare("
            SELECT 
                id, 
                email, 
                primeiro_nome, 
                sobrenome,
                telefone,
                codigo_pais,
                telefone_verificado, 
                status,
                tipo_conta,
                idioma
            FROM usuarios 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        $existingUser = $stmt->fetch();
        
        if ($existingUser) {
            // ========================================
            // USUÁRIO JÁ EXISTE - FAZER LOGIN
            // ========================================
            
            // Verificar status da conta
            if ($existingUser['status'] === 'inativo') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Sua conta está inativa. Entre em contato com o suporte.'
                ]);
                exit;
            }
            
            if ($existingUser['status'] === 'suspenso') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Sua conta está suspensa. Entre em contato com o suporte.'
                ]);
                exit;
            }
            
            // Verificar se telefone está verificado
            if (!$existingUser['telefone_verificado']) {
                echo json_encode([
                    'success' => true,
                    'requiresVerification' => true,
                    'userId' => $existingUser['id'],
                    'message' => 'Você precisa verificar seu telefone antes de fazer login.',
                    'phone' => $existingUser['codigo_pais'] . ' ' . $existingUser['telefone'],
                    'email' => $existingUser['email'],
                    'userData' => [
                        'firstName' => $existingUser['primeiro_nome'],
                        'lastName' => $existingUser['sobrenome'],
                        'countryCode' => $existingUser['codigo_pais'],
                        'email' => $existingUser['email']
                    ]
                ]);
                exit;
            }
            
            // Login bem-sucedido! Criar sessão
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $existingUser['id'];
            $_SESSION['user_email'] = $existingUser['email'];
            $_SESSION['user_name'] = $existingUser['primeiro_nome'] . ' ' . $existingUser['sobrenome'];
            $_SESSION['user_type'] = $existingUser['tipo_conta'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_method'] = 'google';
            
            // Atualizar último login
            $stmt = $conn->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
            $stmt->execute([$existingUser['id']]);
            
            // Log de login
            $stmt = $conn->prepare("
                INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip, user_agent) 
                VALUES (?, 'login_google', 'Login realizado via Google', ?, ?)
            ");
            $stmt->execute([
                $existingUser['id'], 
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
            
            echo json_encode([
                'success' => true,
                'isNewUser' => false,
                'message' => 'Login realizado com sucesso!',
                'data' => [
                    'userId' => $existingUser['id'],
                    'name' => $existingUser['primeiro_nome'] . ' ' . $existingUser['sobrenome'],
                    'email' => $existingUser['email'],
                    'accountType' => $existingUser['tipo_conta'],
                    'language' => $existingUser['idioma'],
                    'redirectTo' => 'dashboard.html'
                ]
            ]);
            
        } else {
            // ========================================
            // USUÁRIO NÃO EXISTE - PREPARAR REGISTRO
            // ========================================
            
            // Retornar dados do Google para preencher o formulário
            echo json_encode([
                'success' => true,
                'isNewUser' => true,
                'message' => 'Complete seu cadastro para continuar.',
                'googleData' => [
                    'email' => $email,
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'picture' => $picture,
                    'googleId' => $googleId,
                    'emailVerified' => $emailVerified
                ]
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Erro no Google Auth: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao processar autenticação Google.',
            'debug' => $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
}
