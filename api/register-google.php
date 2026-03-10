<?php
/**
 * API de Registro via Google
 * @author GustavoChaconDeveloper
 * @description Endpoint para completar registro de usuário que veio do Google
 */

// ============================================================================
// CONFIGURAÇÕES INICIAIS
// ============================================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../conexao/conexao.php';
require_once '../helpers/TwilioSMS.php';
require_once '../helpers/EmailSMTP.php';

// ============================================================================
// PROCESSAR REGISTRO GOOGLE
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validar dados obrigatórios
    if (empty($data['email']) || empty($data['firstName']) || empty($data['lastName']) || empty($data['phone'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Dados incompletos. Preencha todos os campos.'
        ]);
        exit;
    }
    
    try {
        $conn = getConnection();
        
        // Verificar se email já existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$data['email']]);
        
        if ($stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Este email já está cadastrado. Tente fazer login.'
            ]);
            exit;
        }
        
        // Limpar telefone (remover formatação)
        $phone = preg_replace('/\D/', '', $data['phone']);
        $countryCode = $data['countryCode'] ?? '+55';
        
        // Verificar se telefone já existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE telefone = ? AND codigo_pais = ?");
        $stmt->execute([$phone, $countryCode]);
        
        if ($stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Este telefone já está cadastrado.'
            ]);
            exit;
        }
        
        // Gerar senha aleatória segura (usuário pode alterar depois)
        $randomPassword = bin2hex(random_bytes(16));
        $senhaHash = password_hash($randomPassword, PASSWORD_DEFAULT);
        
        // Inserir novo usuário
        $stmt = $conn->prepare("
            INSERT INTO usuarios (
                email, 
                senha_hash, 
                primeiro_nome, 
                sobrenome, 
                telefone, 
                codigo_pais,
                email_verificado,
                telefone_verificado,
                idioma,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, 1, 0, ?, 'pendente')
        ");
        
        $stmt->execute([
            $data['email'],
            $senhaHash,
            $data['firstName'],
            $data['lastName'],
            $phone,
            $countryCode,
            $data['language'] ?? 'en'
        ]);
        
        $userId = $conn->lastInsertId();
        
        // Criar perfil do usuário
        $stmt = $conn->prepare("INSERT INTO perfis_usuarios (usuario_id) VALUES (?)");
        $stmt->execute([$userId]);
        
        // Se tem avatar do Google, salvar no perfil
        if (!empty($data['picture'])) {
            $stmt = $conn->prepare("UPDATE perfis_usuarios SET url_avatar = ? WHERE usuario_id = ?");
            $stmt->execute([$data['picture'], $userId]);
        }
        
        // Gerar código de verificação
        $verificationCode = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $verificationMethod = $data['verificationMethod'] ?? 'sms';
        
        // Inserir código na tabela
        $stmt = $conn->prepare("
            INSERT INTO codigos_sms (
                usuario_id, 
                codigo, 
                telefone, 
                codigo_pais, 
                tipo_envio,
                expira_em,
                endereco_ip
            ) VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 3 MINUTE), ?)
        ");
        
        $stmt->execute([
            $userId,
            $verificationCode,
            $phone,
            $countryCode,
            $verificationMethod,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        // Enviar código de verificação
        $sendResult = null;
        
        if ($verificationMethod === 'email') {
            $emailSMTP = new EmailSMTP();
            $sendResult = $emailSMTP->sendVerificationCode($data['email'], $verificationCode, $data['language'] ?? 'en');
        } else {
            $twilioSMS = new TwilioSMS();
            $fullPhone = $countryCode . $phone;
            $sendResult = $twilioSMS->sendVerificationCode($fullPhone, $verificationCode, $data['language'] ?? 'en');
        }
        
        // Log da atividade
        $stmt = $conn->prepare("
            INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip, user_agent) 
            VALUES (?, 'user_registered_google', 'Novo usuário registrado via Google - Método: " . $verificationMethod . "', ?, ?)
        ");
        $stmt->execute([
            $userId, 
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Cadastro realizado com sucesso!',
            'data' => [
                'userId' => $userId,
                'verificationRequired' => true,
                'verificationMethod' => $verificationMethod,
                'verificationCode' => $verificationCode // Remover em produção
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Erro no registro Google: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao processar registro.',
            'debug' => $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
}
