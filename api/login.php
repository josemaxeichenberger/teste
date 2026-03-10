<?php
/**
 * API de Login
 * @author GustavoChaconDeveloper
 * @description Endpoint para autenticação de usuários
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
// PROCESSAR LOGIN
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validar entrada
    if (empty($data['email']) || empty($data['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Email e senha são obrigatórios.'
        ]);
        exit;
    }
    
    try {
        $conn = getConnection();
        
        // Buscar usuário por email
        $stmt = $conn->prepare("
            SELECT 
                id, 
                email, 
                senha_hash, 
                primeiro_nome, 
                sobrenome,
                telefone,
                codigo_pais,
                email_verificado,
                telefone_verificado, 
                status,
                tipo_conta,
                idioma
            FROM usuarios 
            WHERE email = ?
        ");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo json_encode([
                'success' => false,
                'message' => 'Email ou senha incorretos.'
            ]);
            exit;
        }
        
        // Verificar senha
        if (!password_verify($data['password'], $user['senha_hash'])) {
            // Log de tentativa falha
            $stmt = $conn->prepare("
                INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip) 
                VALUES (?, 'login_failed', 'Tentativa de login com senha incorreta', ?)
            ");
            $stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
            
            echo json_encode([
                'success' => false,
                'message' => 'Email ou senha incorretos.'
            ]);
            exit;
        }
        
        // Verificar status da conta
        if ($user['status'] === 'inativo') {
            echo json_encode([
                'success' => false,
                'message' => 'Sua conta está inativa. Entre em contato com o suporte.'
            ]);
            exit;
        }
        
        if ($user['status'] === 'suspenso') {
            echo json_encode([
                'success' => false,
                'message' => 'Sua conta está suspensa. Entre em contato com o suporte.'
            ]);
            exit;
        }
        
        // Verificar se telefone está verificado
        if (!$user['telefone_verificado']) {
            echo json_encode([
                'success' => true,
                'requiresVerification' => true,
                'userId' => $user['id'],
                'message' => 'Você precisa verificar seu telefone antes de fazer login.',
                'phone' => $user['codigo_pais'] . ' ' . $user['telefone'],
                'userData' => [
                    'firstName' => $user['primeiro_nome'],
                    'lastName' => $user['sobrenome'],
                    'countryCode' => $user['codigo_pais']
                ]
            ]);
            exit;
        }
        
        // Login bem-sucedido! Criar sessão
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['primeiro_nome'] . ' ' . $user['sobrenome'];
        $_SESSION['user_type'] = $user['tipo_conta'];
        $_SESSION['logged_in'] = true;
        
        error_log("Login bem-sucedido: {$user['email']} (ID: {$user['id']})");
        
        // Atualizar último login
        $stmt = $conn->prepare("
            UPDATE usuarios 
            SET ultimo_login = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$user['id']]);
        
        // Log de login bem-sucedido
        $stmt = $conn->prepare("
            INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip) 
            VALUES (?, 'login_success', 'Login realizado com sucesso', ?)
        ");
        $stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'data' => [
                'userId' => $user['id'],
                'name' => $user['primeiro_nome'] . ' ' . $user['sobrenome'],
                'email' => $user['email'],
                'accountType' => $user['tipo_conta'],
                'language' => $user['idioma'],
                'redirectTo' => 'dashboard.html' // Ou página de destino
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Erro no login: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao processar login. Tente novamente.',
            'debug' => $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
}
