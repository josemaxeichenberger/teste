<?php
/**
 * API de Reenvio de Código SMS
 * @author GustavoChaconDeveloper
 * @description Endpoint para reenviar código de verificação
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
// PROCESSAR REENVIO
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (empty($data['userId'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de usuário não fornecido.'
        ]);
        exit;
    }
    
    try {
        $conn = getConnection();
        
        // Verificar se o usuário existe e não está verificado
        $stmt = $conn->prepare("
            SELECT id, email, telefone, codigo_pais, idioma, telefone_verificado 
            FROM usuarios 
            WHERE id = ?
        ");
        $stmt->execute([$data['userId']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo json_encode([
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ]);
            exit;
        }
        
        if ($user['telefone_verificado']) {
            echo json_encode([
                'success' => false,
                'message' => 'Telefone já verificado.'
            ]);
            exit;
        }
        
        // Detectar método de verificação pelo sessionStorage (ou parâmetro)
        $verificationMethod = $data['verificationMethod'] ?? 'sms';
        
        // Definir campos de controle baseado no método
        $tentativasField = $verificationMethod === 'email' ? 'tentativas_reenvio_email' : 'tentativas_reenvio_sms';
        $bloqueioField = $verificationMethod === 'email' ? 'bloqueio_email_ate' : 'bloqueio_sms_ate';
        
        // Buscar dados de tentativas e bloqueio do usuário COM VERIFICAÇÃO NO MYSQL
        $stmt = $conn->prepare("
            SELECT 
                $tentativasField as tentativas, 
                $bloqueioField as bloqueio,
                IF($bloqueioField IS NOT NULL AND $bloqueioField > NOW(), 1, 0) as esta_bloqueado,
                CEIL(TIMESTAMPDIFF(SECOND, NOW(), $bloqueioField) / 60) as minutos_restantes
            FROM usuarios 
            WHERE id = ?
        ");
        $stmt->execute([$data['userId']]);
        $controle = $stmt->fetch();
        
        if (!$controle) {
            echo json_encode([
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ]);
            exit;
        }
        
        $tentativasAtuais = $controle['tentativas'] ?? 0;
        $bloqueioAte = $controle['bloqueio'];
        $estaBloqueado = (int)$controle['esta_bloqueado'];
        $minutosRestantes = (int)$controle['minutos_restantes'];
        
        // Log para debug
        error_log("Resend - UserId: {$data['userId']}, Método: $verificationMethod, Tentativas: $tentativasAtuais, Bloqueio: " . ($bloqueioAte ?? 'NULL') . ", Bloqueado: $estaBloqueado");
        
        // VERIFICAR SE ESTÁ BLOQUEADO (usando resultado do MySQL)
        if ($estaBloqueado === 1) {
            $methodName = $verificationMethod === 'email' ? 'Email' : 'SMS';
            
            error_log("Resend BLOQUEADO - Método: $methodName, Minutos restantes: $minutosRestantes");
            
            echo json_encode([
                'success' => false,
                'message' => "Limite de reenvios de $methodName atingido. Aguarde $minutosRestantes minuto(s).",
                'blocked' => true,
                'remainingMinutes' => $minutosRestantes
            ]);
            exit;
        }
        
        // Resetar contador se bloqueio expirou (usando MySQL NOW())
        if ($bloqueioAte && !$estaBloqueado) {
            $stmt = $conn->prepare("
                UPDATE usuarios 
                SET $tentativasField = 0, $bloqueioField = NULL 
                WHERE id = ? AND ($bloqueioField IS NULL OR $bloqueioField <= NOW())
            ");
            $stmt->execute([$data['userId']]);
            $tentativasAtuais = 0;
            error_log("Resend - Bloqueio expirado, resetando contador");
        }
        
        // Incrementar contador de tentativas
        $novasTentativas = $tentativasAtuais + 1;
        
        error_log("Resend - Tentativas atuais: $tentativasAtuais, Novas tentativas: $novasTentativas");
        
        // Se já passou de 3 tentativas (ou seja, esta seria a 4ª), bloquear e NÃO ENVIAR
        // Permite: 1ª, 2ª, 3ª tentativa. Bloqueia na 4ª.
        if ($novasTentativas > 3) {
            $stmt = $conn->prepare("
                UPDATE usuarios 
                SET $tentativasField = ?, 
                    $bloqueioField = DATE_ADD(NOW(), INTERVAL 15 MINUTE) 
                WHERE id = ?
            ");
            $stmt->execute([$novasTentativas, $data['userId']]);
            
            $methodName = $verificationMethod === 'email' ? 'Email' : 'SMS';
            
            error_log("Resend - LIMITE ATINGIDO! Bloqueando método: $methodName por 15 minutos");
            
            echo json_encode([
                'success' => false,
                'message' => "Limite de 3 reenvios de $methodName atingido. Aguarde 15 minutos.",
                'blocked' => true,
                'remainingMinutes' => 15
            ]);
            exit;
        }
        
        // Se ainda não atingiu 3, apenas incrementar contador E ENVIAR
        $stmt = $conn->prepare("
            UPDATE usuarios 
            SET $tentativasField = ? 
            WHERE id = ?
        ");
        $stmt->execute([$novasTentativas, $data['userId']]);
        
        // Invalidar códigos anteriores
        $stmt = $conn->prepare("
            UPDATE codigos_sms 
            SET usado = 1 
            WHERE usuario_id = ? AND usado = 0
        ");
        $stmt->execute([$data['userId']]);
        
        // Gerar novo código
        $newCode = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // Inserir novo código com expiração de 3 minutos
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
            $data['userId'],
            $newCode,
            $user['telefone'],
            $user['codigo_pais'],
            $verificationMethod,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        // Log da atividade
        $logMessage = $verificationMethod === 'email' ? 'Código Email reenviado' : 'Código SMS reenviado';
        $stmt = $conn->prepare("
            INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip) 
            VALUES (?, 'codigo_reenviado', ?, ?)
        ");
        $stmt->execute([$data['userId'], $logMessage, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        
        // ENVIAR CÓDIGO CONFORME MÉTODO ESCOLHIDO
        if ($verificationMethod === 'sms') {
            // ENVIAR SMS VIA TWILIO
            require_once __DIR__ . '/../helpers/TwilioSMS.php';
            $twilio = new TwilioSMS();
            
            $fullPhone = $user['codigo_pais'] . $user['telefone'];
            $language = $user['idioma'] ?? 'pt';
            
            $smsResult = $twilio->sendVerificationCode($fullPhone, $newCode, $language);
            
            if ($smsResult['success']) {
                error_log("SMS reenviado com sucesso ({$smsResult['mode']}): SID=" . ($smsResult['sid'] ?? 'N/A'));
            } else {
                error_log("Erro ao reenviar SMS: " . ($smsResult['error'] ?? 'Desconhecido'));
            }
        } else if ($verificationMethod === 'email') {
            // ENVIAR EMAIL VIA SMTP
            require_once __DIR__ . '/../helpers/EmailSMTP.php';
            $emailSMTP = new EmailSMTP();
            
            $language = $user['idioma'] ?? 'pt';
            
            $emailResult = $emailSMTP->sendVerificationCode($user['email'], $newCode, $language);
            
            if ($emailResult['success']) {
                error_log("Email reenviado com sucesso ({$emailResult['mode']}): " . $user['email']);
            } else {
                error_log("Erro ao reenviar email: " . ($emailResult['error'] ?? 'Desconhecido'));
            }
        }
        
        $successMessage = $verificationMethod === 'email' 
            ? 'Novo código enviado para seu email!' 
            : 'Novo código enviado por SMS!';
        
        echo json_encode([
            'success' => true,
            'message' => $successMessage,
            'data' => [
                'verificationCode' => $newCode, // Remover em produção
                'expiresIn' => 180 // segundos (3 minutos)
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Erro ao reenviar código: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao reenviar código. Tente novamente.'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
}
