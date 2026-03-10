<?php
/**
 * API - Verificar e Enviar SMS para Alteração de Telefone
 * @author GustavoChaconDeveloper
 * @description Verifica se o novo telefone já existe e envia código SMS
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../conexao/conexao.php';
require_once '../helpers/TwilioSMS.php';

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validar entrada
    if (empty($data['phone']) || empty($data['countryCode'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Telefone e código do país são obrigatórios.'
        ]);
        exit;
    }
    
    // Verificar se usuário está logado
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Você precisa estar logado para alterar o telefone.'
        ]);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $newPhone = preg_replace('/\D/', '', $data['phone']); // Apenas dígitos
    $countryCode = $data['countryCode'];
    $language = $data['language'] ?? 'pt';
    
    // Validar formato do telefone
    if (empty($newPhone) || strlen($newPhone) < 8) {
        echo json_encode([
            'success' => false,
            'message' => $language === 'pt' 
                ? 'Número de telefone inválido.' 
                : 'Invalid phone number.'
        ]);
        exit;
    }
    
    try {
        $conn = getConnection();
        
        // Buscar telefone atual do usuário
        $stmt = $conn->prepare("SELECT telefone, codigo_pais FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $currentUser = $stmt->fetch();
        
        if (!$currentUser) {
            echo json_encode([
                'success' => false,
                'message' => $language === 'pt' ? 'Usuário não encontrado.' : 'User not found.'
            ]);
            exit;
        }
        
        // Verificar se o novo telefone é diferente do atual
        $currentPhone = preg_replace('/\D/', '', $currentUser['telefone']);
        $currentCountryCode = $currentUser['codigo_pais'];
        
        if ($newPhone === $currentPhone && $countryCode === $currentCountryCode) {
            echo json_encode([
                'success' => false,
                'message' => $language === 'pt' 
                    ? 'Este já é o seu telefone atual.' 
                    : 'This is already your current phone number.'
            ]);
            exit;
        }
        
        // Verificar se o novo telefone já está cadastrado por outro usuário
        $stmt = $conn->prepare("
            SELECT id, primeiro_nome 
            FROM usuarios 
            WHERE telefone = ? 
            AND codigo_pais = ? 
            AND id != ?
            AND telefone_verificado = 1
        ");
        $stmt->execute([$newPhone, $countryCode, $userId]);
        $existingUser = $stmt->fetch();
        
        if ($existingUser) {
            echo json_encode([
                'success' => false,
                'message' => $language === 'pt' 
                    ? 'Este número de telefone já está sendo usado por outro usuário.' 
                    : 'This phone number is already in use by another user.',
                'error_code' => 'PHONE_IN_USE'
            ]);
            exit;
        }
        
        // Gerar código de verificação de 4 dígitos
        $verificationCode = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // Salvar código temporário no banco com expiração de 3 minutos
        // Usar NOW() + INTERVAL para garantir timezone correto do MySQL
        $expiresAt = null; // Será calculado no SQL
        
        // Verificar se já existe um código pendente
        $stmt = $conn->prepare("
            SELECT id FROM codigos_verificacao_telefone 
            WHERE usuario_id = ? 
            AND tipo = 'phone_change' 
            AND usado = 0 
            AND expira_em > NOW()
        ");
        $stmt->execute([$userId]);
        $existingCode = $stmt->fetch();
        
        if ($existingCode) {
            // Invalidar código anterior
            $stmt = $conn->prepare("
                UPDATE codigos_verificacao_telefone 
                SET usado = 1 
                WHERE usuario_id = ? 
                AND tipo = 'phone_change' 
                AND usado = 0
            ");
            $stmt->execute([$userId]);
        }
        
        // Inserir novo código com expiração calculada pelo MySQL (3 minutos)
        $stmt = $conn->prepare("
            INSERT INTO codigos_verificacao_telefone 
            (usuario_id, codigo, telefone_novo, codigo_pais_novo, tipo, expira_em, criado_em) 
            VALUES (?, ?, ?, ?, 'phone_change', DATE_ADD(NOW(), INTERVAL 3 MINUTE), NOW())
        ");
        $stmt->execute([
            $userId,
            $verificationCode,
            $newPhone,
            $countryCode
        ]);
        
        // Enviar SMS via Twilio
        $twilioSMS = new TwilioSMS();
        $fullPhoneNumber = $countryCode . $newPhone;
        
        $smsResult = $twilioSMS->sendVerificationCode($fullPhoneNumber, $verificationCode, $language);
        
        if ($smsResult['success']) {
            // Log de envio bem-sucedido
            $stmt = $conn->prepare("
                INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip) 
                VALUES (?, 'phone_change_sms_sent', ?, ?)
            ");
            $stmt->execute([
                $userId,
                "SMS enviado para $fullPhoneNumber",
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => $language === 'pt' 
                    ? 'Código de verificação enviado via SMS.' 
                    : 'Verification code sent via SMS.',
                'phone' => $countryCode . ' ' . $newPhone,
                'expiresIn' => 180, // 3 minutos em segundos
                'testMode' => $smsResult['test_mode'] ?? false,
                'testNumber' => $smsResult['test_number'] ?? null
            ]);
        } else {
            // Erro ao enviar SMS
            echo json_encode([
                'success' => false,
                'message' => $language === 'pt' 
                    ? 'Erro ao enviar SMS. Tente novamente.' 
                    : 'Error sending SMS. Please try again.',
                'error' => $smsResult['error'] ?? 'Unknown error'
            ]);
        }
        
    } catch (PDOException $e) {
        error_log("Erro no check-phone-change: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $language === 'pt' 
                ? 'Erro ao processar solicitação.' 
                : 'Error processing request.'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
}
