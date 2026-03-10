<?php
/**
 * API de Recuperação de Senha
 * @author GustavoChaconDeveloper
 * @description Endpoint para solicitar recuperação de senha via email
 */

// ============================================================================
// CONFIGURAÇÕES INICIAIS
// ============================================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../conexao/conexao.php';
require_once '../helpers/EmailSMTP.php';

// ============================================================================
// GARANTIR TABELA DE TOKENS DE RECUPERAÇÃO
// ============================================================================
function garantirTabelaRecuperacaoSenha() {
    try {
        $conn = getConnection();
        
        $sql = "
            CREATE TABLE IF NOT EXISTS password_reset_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                expira_em DATETIME NOT NULL,
                usado TINYINT(1) DEFAULT 0,
                usado_em DATETIME NULL,
                criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                ip_solicitacao VARCHAR(45) NULL,
                INDEX idx_token (token),
                INDEX idx_expira_em (expira_em),
                INDEX idx_usuario_id (usuario_id),
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        
        $conn->exec($sql);
        return true;
        
    } catch (PDOException $e) {
        error_log("Erro ao criar tabela password_reset_tokens: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// PROCESSAR SOLICITAÇÃO DE RECUPERAÇÃO
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validar entrada
    if (empty($data['email'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Email é obrigatório.'
        ]);
        exit;
    }
    
    $email = trim($data['email']);
    
    // Validar formato do email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email inválido.'
        ]);
        exit;
    }
    
    try {
        $conn = getConnection();
        
        // Garantir que a tabela existe
        garantirTabelaRecuperacaoSenha();
        
        // Buscar usuário por email
        $stmt = $conn->prepare("SELECT id, primeiro_nome, idioma FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // SEGURANÇA: Sempre retornar sucesso mesmo se email não existir
        // Isso evita que atacantes descubram emails cadastrados
        if (!$user) {
            error_log("Tentativa de recuperação para email não cadastrado: $email");
            
            // Retorna sucesso mas não envia email
            echo json_encode([
                'success' => true,
                'message' => 'Se o email estiver cadastrado, você receberá instruções para recuperar sua senha.'
            ]);
            exit;
        }
        
        // Verificar se há muitas tentativas recentes (proteção anti-spam)
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as tentativas,
                MIN(criado_em) as primeira_tentativa
            FROM password_reset_tokens 
            WHERE usuario_id = ? 
            AND criado_em > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");
        $stmt->execute([$user['id']]);
        $controle = $stmt->fetch();
        
        if ($controle['tentativas'] >= 3) {
            // Calcular tempo restante até poder solicitar novamente (usando MySQL)
            $minutosRestantes = 15; // Valor padrão
            
            if ($controle['primeira_tentativa']) {
                $stmt = $conn->prepare("
                    SELECT TIMESTAMPDIFF(MINUTE, NOW(), DATE_ADD(?, INTERVAL 15 MINUTE)) as minutos_restantes
                ");
                $stmt->execute([$controle['primeira_tentativa']]);
                $tempo = $stmt->fetch();
                
                if ($tempo && isset($tempo['minutos_restantes'])) {
                    $minutosRestantes = max(1, (int)$tempo['minutos_restantes']); // Mínimo 1 minuto
                }
            }
            
            // Mensagem dinâmica baseada no tempo real restante
            $mensagem = $minutosRestantes === 1 
                ? 'Muitas solicitações recentes. Aguarde 1 minuto e tente novamente.'
                : "Muitas solicitações recentes. Aguarde {$minutosRestantes} minutos e tente novamente.";
            
            echo json_encode([
                'success' => false,
                'message' => $mensagem,
                'rateLimited' => true,
                'attemptsUsed' => (int)$controle['tentativas'],
                'attemptsLimit' => 3,
                'waitMinutes' => $minutosRestantes
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Invalidar tokens anteriores não usados deste usuário
        $stmt = $conn->prepare("
            UPDATE password_reset_tokens 
            SET usado = 1, usado_em = NOW() 
            WHERE usuario_id = ? AND usado = 0
        ");
        $stmt->execute([$user['id']]);
        
        // Gerar token único e seguro
        $token = bin2hex(random_bytes(32)); // 64 caracteres hexadecimais
        
        // Definir expiração (5 minutos a partir de agora)
        // Usar NOW() do MySQL para evitar problemas de timezone
        $minutosExpiracao = 5;
        
        // Salvar token no banco usando DATE_ADD do MySQL
        $stmt = $conn->prepare("
            INSERT INTO password_reset_tokens (usuario_id, token, expira_em, ip_solicitacao) 
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE), ?)
        ");
        $stmt->execute([
            $user['id'],
            $token,
            $minutosExpiracao,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        // Construir link de recuperação (página WordPress)
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // URL da página WordPress com template de reset password
        $resetLink = "$protocol://$host/reset-password/?token=$token";
        
        // Log da ação
        $stmt = $conn->prepare("
            INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip) 
            VALUES (?, 'password_reset_requested', 'Solicitação de recuperação de senha', ?)
        ");
        $stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        
        // Enviar email com link de recuperação
        $emailSMTP = new EmailSMTP();
        $language = $user['idioma'] ?? 'pt';
        
        $emailResult = $emailSMTP->sendPasswordResetEmail(
            $email,
            $user['primeiro_nome'],
            $resetLink,
            $token,
            $language
        );
        
        if (!$emailResult['success']) {
            error_log("Erro ao enviar email de recuperação: " . ($emailResult['error'] ?? 'Erro desconhecido'));
            
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao enviar email. Tente novamente mais tarde.'
            ]);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Se o email estiver cadastrado, você receberá instruções para recuperar sua senha.',
            'debug' => [
                'emailSent' => true,
                'expiresIn' => '5 minutos'
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Erro na recuperação de senha: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao processar solicitação. Tente novamente mais tarde.'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
}
