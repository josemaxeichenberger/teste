<?php
/**
 * API de Contato
 * @author GustavoChaconDeveloper
 * @description Processa formulário de contato e newsletter
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../conexao/conexao.php';
require_once '../helpers/EmailSMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

class ContactManager {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function processContact($data) {
        try {
            $validation = $this->validateInput($data);
            if (!$validation['success']) {
                return $validation;
            }
            
            $this->conn->beginTransaction();
            
            $contactId = $this->saveContact($data);
            if (!$contactId) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Erro ao salvar mensagem.'];
            }
            
            if (isset($data['newsletter']) && $data['newsletter'] === true) {
                $this->processNewsletter($data);
            }
            
            $this->conn->commit();
            
            $this->sendEmails($data, $contactId);
            
            return [
                'success' => true,
                'message' => 'Mensagem enviada com sucesso! Você receberá uma confirmação por email.',
                'contact_id' => $contactId
            ];
            
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Erro ao processar contato: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao processar sua mensagem.'];
        }
    }
    
    private function validateInput($data) {
        $errors = [];
        
        if (empty($data['firstName'])) $errors[] = 'Primeiro nome é obrigatório.';
        if (empty($data['lastName'])) $errors[] = 'Sobrenome é obrigatório.';
        if (empty($data['email'])) {
            $errors[] = 'Email é obrigatório.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido.';
        }
        if (empty($data['subject'])) $errors[] = 'Assunto é obrigatório.';
        if (!isset($data['termsAgree']) || $data['termsAgree'] !== true) {
            $errors[] = 'Você deve aceitar os Termos de Uso.';
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(' ', $errors)];
        }
        
        return ['success' => true];
    }
    
    private function saveContact($data) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO contatos (
                    primeiro_nome, sobrenome, email, assunto, mensagem,
                    aceite_termos, inscrito_newsletter, endereco_ip, user_agent, status
                ) VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, 'novo')
            ");
            
            $stmt->execute([
                trim($data['firstName']),
                trim($data['lastName']),
                strtolower(trim($data['email'])),
                $data['subject'],
                isset($data['message']) ? trim($data['message']) : null,
                isset($data['newsletter']) && $data['newsletter'] === true ? 1 : 0,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro ao salvar contato: " . $e->getMessage());
            return false;
        }
    }
    
    private function processNewsletter($data) {
        try {
            $email = strtolower(trim($data['email']));
            
            $stmt = $this->conn->prepare("SELECT id, status FROM newsletter WHERE email = ?");
            $stmt->execute([$email]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                if ($existing['status'] === 'cancelado') {
                    $stmt = $this->conn->prepare("
                        UPDATE newsletter SET status = 'ativo', data_inscricao = NOW(),
                        primeiro_nome = ?, sobrenome = ? WHERE id = ?
                    ");
                    $stmt->execute([
                        trim($data['firstName']),
                        trim($data['lastName']),
                        $existing['id']
                    ]);
                }
                return ['success' => true];
            }
            
            $token = bin2hex(random_bytes(32));
            
            $stmt = $this->conn->prepare("
                INSERT INTO newsletter (
                    email, primeiro_nome, sobrenome, origem, status,
                    endereco_ip, token_cancelamento, confirmado
                ) VALUES (?, ?, ?, 'contact_form', 'ativo', ?, ?, 1)
            ");
            
            $stmt->execute([
                $email,
                trim($data['firstName']),
                trim($data['lastName']),
                $_SERVER['REMOTE_ADDR'] ?? null,
                $token
            ]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Erro ao processar newsletter: " . $e->getMessage());
            return ['success' => false];
        }
    }
    
    private function sendEmails($data, $contactId) {
        try {
            $firstName = trim($data['firstName']);
            $email = strtolower(trim($data['email']));
            $subject = $this->getSubjectLabel($data['subject']);
            
            $this->sendConfirmationEmail($email, $firstName, $subject);
            $this->sendAdminNotification($data, $contactId, $subject);
        } catch (Exception $e) {
            error_log("Erro ao enviar emails: " . $e->getMessage());
        }
    }
    
    private function sendConfirmationEmail($email, $firstName, $subject) {
        $htmlBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(90deg, #001845 0%, #003399 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Poker111</h1>
                </div>
                <div class='content'>
                    <h2>Olá, $firstName!</h2>
                    <p>Obrigado por entrar em contato conosco!</p>
                    <p>Recebemos sua mensagem sobre: <strong>$subject</strong></p>
                    <p>Nossa equipe irá analisar sua solicitação e retornaremos o mais breve possível.</p>
                    <p>Tempo médio de resposta: <strong>24-48 horas</strong></p>
                    <div style='background: #e8f4ff; padding: 15px; border-left: 4px solid #003399; margin: 20px 0;'>
                        <p style='margin: 0;'><strong>💡 Dica:</strong> Enquanto isso, explore nossos desafios!</p>
                    </div>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Poker111. Todos os direitos reservados.</p>
                </div>
            </div>
        </body>
        </html>";
        
        $this->sendCustomEmail($email, 'Confirmação de Contato - Poker111', $htmlBody);
    }
    
    private function sendAdminNotification($data, $contactId, $subjectLabel) {
        $firstName = htmlspecialchars(trim($data['firstName']));
        $lastName = htmlspecialchars(trim($data['lastName']));
        $email = htmlspecialchars(strtolower(trim($data['email'])));
        $message = isset($data['message']) ? nl2br(htmlspecialchars(trim($data['message']))) : '<em>Nenhuma mensagem</em>';
        $newsletter = isset($data['newsletter']) && $data['newsletter'] === true ? 'Sim ✅' : 'Não';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Desconhecido';
        $date = date('d/m/Y H:i:s');
        
        $htmlBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 700px; margin: 0 auto; padding: 20px; }
                .header { background: #001845; color: white; padding: 20px; border-radius: 10px 10px 0 0; }
                .content { background: #ffffff; padding: 30px; border: 1px solid #ddd; }
                .info-row { margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
                .info-label { font-weight: bold; color: #003399; }
                .message-box { background: #f5f5f5; padding: 15px; border-left: 4px solid #003399; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>🔔 Nova Mensagem de Contato #$contactId</h2>
                </div>
                <div class='content'>
                    <h3>Informações do Contato:</h3>
                    
                    <div class='info-row'>
                        <span class='info-label'>Nome Completo:</span> $firstName $lastName
                    </div>
                    
                    <div class='info-row'>
                        <span class='info-label'>Email:</span> <a href='mailto:$email'>$email</a>
                    </div>
                    
                    <div class='info-row'>
                        <span class='info-label'>Assunto:</span> $subjectLabel
                    </div>
                    
                    <div class='info-row'>
                        <span class='info-label'>Newsletter:</span> $newsletter
                    </div>
                    
                    <div class='info-row'>
                        <span class='info-label'>Data/Hora:</span> $date
                    </div>
                    
                    <div class='info-row'>
                        <span class='info-label'>IP:</span> $ip
                    </div>
                    
                    <h3>Mensagem:</h3>
                    <div class='message-box'>$message</div>
                </div>
            </div>
        </body>
        </html>";
        
        $this->sendCustomEmail('gustavoalveschacon5@gmail.com', "Nova Mensagem #$contactId - Poker111", $htmlBody);
    }
    
    private function sendCustomEmail($toEmail, $subject, $htmlBody) {
        try {
            if (!defined('SMTP_ENABLED') || !SMTP_ENABLED) {
                error_log("SMTP desabilitado - Email para: $toEmail");
                return ['success' => true, 'mode' => 'test'];
            }
            
            $mail = new PHPMailer(true);
            
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';
            
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($toEmail);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);
            
            $mail->send();
            
            return ['success' => true];
        } catch (Exception $e) {
            error_log("Erro ao enviar email: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function getSubjectLabel($subjectKey) {
        $subjects = [
            'player-application' => 'Player applications & committee review',
            'coaching' => 'Coaching, academy & training',
            'account' => 'Account, payments & contracts',
            'technical' => 'Technical or website issue',
            'business' => 'Business & media partnerships',
            'general' => 'General inquiry or feedback'
        ];
        
        return $subjects[$subjectKey] ?? $subjectKey;
    }
}

// ============================================================================
// PROCESSAR REQUISIÇÃO
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
        exit;
    }
    
    try {
        $conn = getConnection();
        $contactManager = new ContactManager($conn);
        $result = $contactManager->processContact($data);
        
        echo json_encode($result);
    } catch (Exception $e) {
        error_log("Erro fatal em contact.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erro ao processar solicitação.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
}
