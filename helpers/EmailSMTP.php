<?php
/**
 * Helper para envio de emails via SMTP
 * @author GustavoChaconDeveloper
 * @description Classe para enviar emails de verificação usando PHPMailer
 */

require_once __DIR__ . '/../api/vendor/autoload.php';
require_once __DIR__ . '/../conexao/conexao.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailSMTP {
    private $host;
    private $port;
    private $username;
    private $password;
    private $fromEmail;
    private $fromName;
    private $enabled;
    
    public function __construct() {
        // Carregar configurações do conexao.php
        $this->host = defined('SMTP_HOST') ? SMTP_HOST : '';
        $this->port = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $this->username = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
        $this->password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
        $this->fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : '';
        $this->fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Poker111';
        $this->enabled = defined('SMTP_ENABLED') ? SMTP_ENABLED : false;
    }
    
    /**
     * Enviar código de verificação por email
     * 
     * @param string $toEmail Email do destinatário
     * @param string $code Código de verificação
     * @param string $language Idioma (pt|en)
     * @return array [success, message/error]
     */
    public function sendVerificationCode($toEmail, $code, $language = 'pt') {
        try {
            // Verificar se SMTP está habilitado
            if (!$this->enabled) {
                error_log("SMTP DESABILITADO - Código seria enviado para: $toEmail | Código: $code");
                return [
                    'success' => true,
                    'mode' => 'test',
                    'message' => 'Modo teste: Email não enviado (SMTP_ENABLED=false)'
                ];
            }
            
            // Validar credenciais
            if (empty($this->host) || empty($this->username) || empty($this->password)) {
                throw new Exception('Configurações SMTP incompletas');
            }
            
            // Preparar template do email
            $template = $this->getEmailTemplate($code, $language);
            
            // Enviar email
            return $this->sendEmail($toEmail, $template['subject'], $template['body']);
            
        } catch (Exception $e) {
            error_log("Erro ao enviar email de verificação: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Enviar email de solicitação de alteração de perfil
     * 
     * @param string $toEmail Email do destinatário
     * @param array $payload Dados da solicitação
     * @param string $language Idioma (pt|en)
     * @return array [success, message/error]
     */
    public function sendProfileChangeRequest($toEmail, $payload, $language = 'pt') {
        try {
            // Verificar se SMTP está habilitado
            if (!$this->enabled) {
                error_log("SMTP DESABILITADO - Solicitação de alteração enviada para: $toEmail");
                return [
                    'success' => true,
                    'mode' => 'test',
                    'message' => 'Modo teste: Email não enviado (SMTP_ENABLED=false)'
                ];
            }

            // Validar credenciais
            if (empty($this->host) || empty($this->username) || empty($this->password)) {
                throw new Exception('Configurações SMTP incompletas');
            }

            $template = $this->getProfileChangeTemplate($payload, $language);

            return $this->sendEmail($toEmail, $template['subject'], $template['body']);

        } catch (Exception $e) {
            error_log("Erro ao enviar email de solicitação de alteração: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Enviar email via SMTP
     * 
     * @param string $toEmail Email do destinatário
     * @param string $subject Assunto
     * @param string $body Corpo do email (HTML)
     * @return array [success, message_id/error]
     */
    public function sendEmail($toEmail, $subject, $body) {
        try {
            $mail = new PHPMailer(true);
            
            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host = $this->host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->username;
            $mail->Password = $this->password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->port;
            $mail->CharSet = 'UTF-8';
            
            // Desabilitar verificação SSL em ambiente local
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Remetente
            $mail->setFrom($this->fromEmail, $this->fromName);
            
            // Destinatário
            $mail->addAddress($toEmail);
            
            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);
            
            // Enviar
            $mail->send();
            
            return [
                'success' => true,
                'mode' => 'production',
                'message' => 'Email enviado com sucesso'
            ];
            
        } catch (Exception $e) {
            throw new Exception("Erro ao enviar email: {$mail->ErrorInfo}");
        }
    }
    
    /**
     * Obter template do email de verificação
     * 
     * @param string $code Código de verificação
     * @param string $language Idioma (pt|en)
     * @return array [subject, body]
     */
    private function getEmailTemplate($code, $language = 'pt') {
        if ($language === 'pt') {
            $subject = 'Código de Verificação - Poker111';
            $body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #00133D 0%, #012164 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .header img { max-width: 150px; height: auto; margin-bottom: 10px; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .code-box { background: white; border: 2px dashed #ff3366; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
        .code { font-size: 32px; font-weight: bold; color: #ff3366; letter-spacing: 8px; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <img src='https://img.lightshot.app/KKOwQwLbTOGx-UsPLA05Mg.png' alt='Poker111' style='max-width: 150px; height: auto; margin-bottom: 10px;'>
            <p>Verificação de Conta</p>
        </div>
        <div class='content'>
            <h2>Olá!</h2>
            <p>Use o código abaixo para verificar seu cadastro:</p>
            <div class='code-box'>
                <div class='code'>$code</div>
            </div>
            <p><strong>Este código é válido por 3 minutos.</strong></p>
            <p>Se você não solicitou este código, ignore este email.</p>
        </div>
        <div class='footer'>
            <p>&copy; 2026 Poker111. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
            ";
        } else {
            $subject = 'Verification Code - Poker111';
            $body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #00133D 0%, #012164 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .header img { max-width: 150px; height: auto; margin-bottom: 10px; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .code-box { background: white; border: 2px dashed #ff3366; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
        .code { font-size: 32px; font-weight: bold; color: #ff3366; letter-spacing: 8px; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <img src='https://img.lightshot.app/KKOwQwLbTOGx-UsPLA05Mg.png' alt='Poker111' style='max-width: 150px; height: auto; margin-bottom: 10px;'>
            <p>Account Verification</p>
        </div>
        <div class='content'>
            <h2>Hello!</h2>
            <p>Use the code below to verify your account:</p>
            <div class='code-box'>
                <div class='code'>$code</div>
            </div>
            <p><strong>This code is valid for 3 minutes.</strong></p>
            <p>If you didn't request this code, please ignore this email.</p>
        </div>
        <div class='footer'>
            <p>&copy; 2026 Poker111. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
            ";
        }
        
        return [
            'subject' => $subject,
            'body' => $body
        ];
    }

    /**
     * Obter template do email de solicitação de alteração
     * 
     * @param array $payload Dados da solicitação
     * @param string $language Idioma (pt|en)
     * @return array [subject, body]
     */
    private function getProfileChangeTemplate($payload, $language = 'pt') {
        $fullName = htmlspecialchars($payload['fullName'] ?? '', ENT_QUOTES, 'UTF-8');
        $newFullName = htmlspecialchars($payload['newFullName'] ?? '', ENT_QUOTES, 'UTF-8');
        $currentEmail = htmlspecialchars($payload['currentEmail'] ?? '', ENT_QUOTES, 'UTF-8');
        $newEmail = htmlspecialchars($payload['newEmail'] ?? '', ENT_QUOTES, 'UTF-8');
        $message = nl2br(htmlspecialchars($payload['message'] ?? '', ENT_QUOTES, 'UTF-8'));

        // Determinar quais campos estão sendo alterados
        $hasEmailChange = !empty($newEmail);
        $hasNameChange = !empty($newFullName);
        $hasMessage = !empty($message);

        if ($language === 'pt') {
            $subject = 'Solicitação de alteração de dados - Poker111';
            
            // Construir o conteúdo da caixa dinamicamente
            $boxContent = '';
            if ($hasEmailChange) {
                $boxContent .= "<p><span class='label'>Email atual:</span> {$currentEmail}</p>";
                $boxContent .= "<p><span class='label'>Novo email:</span> {$newEmail}</p>";
            }
            if ($hasNameChange) {
                $boxContent .= "<p><span class='label'>Nome atual:</span> {$fullName}</p>";
                $boxContent .= "<p><span class='label'>Novo nome:</span> {$newFullName}</p>";
            }
            if ($hasMessage) {
                $boxContent .= "<p><span class='label'>Mensagem:</span> {$message}</p>";
            }
            
            $body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #00133D 0%, #012164 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .header img { max-width: 150px; height: auto; margin-bottom: 10px; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .box { background: white; border: 1px solid #e0e0e0; padding: 16px; margin: 16px 0; border-radius: 8px; }
        .label { font-weight: bold; color: #555; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <img src='https://img.lightshot.app/KKOwQwLbTOGx-UsPLA05Mg.png' alt='Poker111' style='max-width: 150px; height: auto; margin-bottom: 10px;'>
            <p>Solicitação de Alteração de Dados</p>
        </div>
        <div class='content'>
            <h2>Olá {$fullName}!</h2>
            <p>Recebemos sua solicitação de alteração de dados. O status atual é <strong>pendente</strong>.</p>
            <p>Enquanto estiver pendente, não será possível enviar uma nova solicitação.</p>
            <div class='box'>
                {$boxContent}
            </div>
            <p>Nossa equipe vai revisar sua solicitação e entraremos em contato.</p>
        </div>
        <div class='footer'>
            <p>&copy; 2026 Poker111. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
            ";
        } else {
            $subject = 'Profile change request - Poker111';
            
            // Construir o conteúdo da caixa dinamicamente
            $boxContent = '';
            if ($hasEmailChange) {
                $boxContent .= "<p><span class='label'>Current email:</span> {$currentEmail}</p>";
                $boxContent .= "<p><span class='label'>New email:</span> {$newEmail}</p>";
            }
            if ($hasNameChange) {
                $boxContent .= "<p><span class='label'>Current name:</span> {$fullName}</p>";
                $boxContent .= "<p><span class='label'>New name:</span> {$newFullName}</p>";
            }
            if ($hasMessage) {
                $boxContent .= "<p><span class='label'>Message:</span> {$message}</p>";
            }
            
            $body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #00133D 0%, #012164 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .header img { max-width: 150px; height: auto; margin-bottom: 10px; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .box { background: white; border: 1px solid #e0e0e0; padding: 16px; margin: 16px 0; border-radius: 8px; }
        .label { font-weight: bold; color: #555; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <img src='https://img.lightshot.app/KKOwQwLbTOGx-UsPLA05Mg.png' alt='Poker111' style='max-width: 150px; height: auto; margin-bottom: 10px;'>
            <p>Profile Change Request</p>
        </div>
        <div class='content'>
            <h2>Hello {$fullName}!</h2>
            <p>We received your profile change request. The current status is <strong>pending</strong>.</p>
            <p>While it is pending, you won’t be able to submit a new request.</p>
            <div class='box'>
                {$boxContent}
            </div>
            <p>Our team will review your request and get back to you.</p>
        </div>
        <div class='footer'>
            <p>&copy; 2026 Poker111. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
            ";
        }

        return [
            'subject' => $subject,
            'body' => $body
        ];
    }

    /**
     * Enviar email de recuperação de senha
     * 
     * @param string $toEmail Email do destinatário
     * @param string $firstName Nome do usuário
     * @param string $resetLink Link completo de recuperação
     * @param string $token Token de recuperação
     * @param string $language Idioma (pt|en)
     * @return array [success, message/error]
     */
    public function sendPasswordResetEmail($toEmail, $firstName, $resetLink, $token, $language = 'pt') {
        try {
            // Verificar se SMTP está habilitado
            if (!$this->enabled) {
                error_log("SMTP DESABILITADO - Email de recuperação seria enviado para: $toEmail");
                return [
                    'success' => true,
                    'mode' => 'test',
                    'message' => 'Modo teste: Email não enviado (SMTP_ENABLED=false)'
                ];
            }

            // Validar credenciais
            if (empty($this->host) || empty($this->username) || empty($this->password)) {
                throw new Exception('Configurações SMTP incompletas');
            }

            // Preparar template do email
            $template = $this->getPasswordResetTemplate($firstName, $resetLink, $token, $language);

            // Enviar email
            return $this->sendEmail($toEmail, $template['subject'], $template['body']);

        } catch (Exception $e) {
            error_log("Erro ao enviar email de recuperação: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obter template do email de recuperação de senha
     * 
     * @param string $firstName Nome do usuário
     * @param string $resetLink Link completo de recuperação
     * @param string $token Token de recuperação
     * @param string $language Idioma (pt|en)
     * @return array [subject, body]
     */
    private function getPasswordResetTemplate($firstName, $resetLink, $token, $language = 'pt') {
        $firstName = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
        
        if ($language === 'pt') {
            $subject = 'Recuperação de Senha - Poker111';
            $body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #00133D 0%, #012164 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .header img { max-width: 150px; height: auto; margin-bottom: 10px; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; background: #ff3366; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
        .button:hover { background: #e02050; }
        .info-box { background: white; border-left: 4px solid #ff3366; padding: 15px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        .warning { color: #ff3366; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <img src='https://img.lightshot.app/KKOwQwLbTOGx-UsPLA05Mg.png' alt='Poker111' style='max-width: 150px; height: auto; margin-bottom: 10px;'>
            <p>Recuperação de Senha</p>
        </div>
        <div class='content'>
            <h2>Olá, {$firstName}!</h2>
            <p>Recebemos uma solicitação para redefinir a senha da sua conta Poker111.</p>
            
            <div class='info-box'>
                <p><strong>⏱️ Este link expira em 5 minutos!</strong></p>
                <p>Se você não solicitou esta recuperação, ignore este email e sua senha permanecerá inalterada.</p>
            </div>
            
            <p>Clique no botão abaixo para criar uma nova senha:</p>
            
            <div style='text-align: center;'>
                <a href='{$resetLink}' class='button'>REDEFINIR MINHA SENHA</a>
            </div>
            
            <p style='margin-top: 30px; font-size: 12px; color: #666;'>
                Ou copie e cole este link no seu navegador:<br>
                <a href='{$resetLink}' style='word-break: break-all; color: #0066cc;'>{$resetLink}</a>
            </p>
            
            <div class='info-box'>
                <p class='warning'>🔒 Segurança:</p>
                <p>• Nunca compartilhe este link com ninguém<br>
                • Este link só pode ser usado uma vez<br>
                • Após redefinir, faça login com sua nova senha</p>
            </div>
            
            <p>Se você teve algum problema, entre em contato com nosso suporte.</p>
        </div>
        <div class='footer'>
            <p>&copy; 2026 Poker111. Todos os direitos reservados.</p>
            <p>Este é um email automático, por favor não responda.</p>
        </div>
    </div>
</body>
</html>
            ";
        } else {
            $subject = 'Password Recovery - Poker111';
            $body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #00133D 0%, #012164 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .header img { max-width: 150px; height: auto; margin-bottom: 10px; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; background: #ff3366; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
        .button:hover { background: #e02050; }
        .info-box { background: white; border-left: 4px solid #ff3366; padding: 15px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        .warning { color: #ff3366; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <img src='https://img.lightshot.app/KKOwQwLbTOGx-UsPLA05Mg.png' alt='Poker111' style='max-width: 150px; height: auto; margin-bottom: 10px;'>
            <p>Password Recovery</p>
        </div>
        <div class='content'>
            <h2>Hello, {$firstName}!</h2>
            <p>We received a request to reset your Poker111 account password.</p>
            
            <div class='info-box'>
                <p><strong>⏱️ This link expires in 5 minutes!</strong></p>
                <p>If you didn't request this recovery, ignore this email and your password will remain unchanged.</p>
            </div>
            
            <p>Click the button below to create a new password:</p>
            
            <div style='text-align: center;'>
                <a href='{$resetLink}' class='button'>RESET MY PASSWORD</a>
            </div>
            
            <p style='margin-top: 30px; font-size: 12px; color: #666;'>
                Or copy and paste this link in your browser:<br>
                <a href='{$resetLink}' style='word-break: break-all; color: #0066cc;'>{$resetLink}</a>
            </p>
            
            <div class='info-box'>
                <p class='warning'>🔒 Security:</p>
                <p>• Never share this link with anyone<br>
                • This link can only be used once<br>
                • After resetting, log in with your new password</p>
            </div>
            
            <p>If you had any problems, contact our support.</p>
        </div>
        <div class='footer'>
            <p>&copy; 2026 Poker111. All rights reserved.</p>
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>
            ";
        }

        return [
            'subject' => $subject,
            'body' => $body
        ];
    }
    
    /**
     * Enviar email de confirmação de cancelamento de conta
     * 
     * @param string $toEmail Email do destinatário
     * @param string $firstName Nome do usuário
     * @param string $deletionDate Data de efetivação do cancelamento
     * @param string $language Idioma (pt|en)
     * @return array [success, message/error]
     */
    public function sendAccountDeletionConfirmation($toEmail, $firstName, $deletionDate, $language = 'pt') {
        try {
            // Verificar se SMTP está habilitado
            if (!$this->enabled) {
                error_log("SMTP DESABILITADO - Email de cancelamento seria enviado para: $toEmail");
                return [
                    'success' => true,
                    'mode' => 'test',
                    'message' => 'Modo teste: Email não enviado (SMTP_ENABLED=false)'
                ];
            }
            
            // Validar credenciais
            if (empty($this->host) || empty($this->username) || empty($this->password)) {
                throw new Exception('Configurações SMTP incompletas');
            }
            
            // Preparar template do email
            $template = $this->getAccountDeletionTemplate($firstName, $deletionDate, $language);
            
            // Enviar email
            return $this->sendEmail($toEmail, $template['subject'], $template['body']);
            
        } catch (Exception $e) {
            error_log("Erro ao enviar email de cancelamento: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Template de email de cancelamento de conta
     */
    private function getAccountDeletionTemplate($firstName, $deletionDate, $language) {
        // Formatar data
        $deletionDateFormatted = date('d/m/Y', strtotime($deletionDate));
        
        if ($language === 'pt') {
            $subject = 'Confirmação de Cancelamento de Conta - Poker111';
            $body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #00133D 0%, #012164 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .header img { max-width: 150px; height: auto; margin-bottom: 10px; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .info-box { background: white; border-left: 4px solid #ff3366; padding: 15px; margin: 20px 0; }
        .warning-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        .button { display: inline-block; background: #00d4ff; color: #00133D; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <img src='https://img.lightshot.app/KKOwQwLbTOGx-UsPLA05Mg.png' alt='Poker111' style='max-width: 150px; height: auto; margin-bottom: 10px;'>
            <p>Solicitação de Cancelamento de Conta</p>
        </div>
        <div class='content'>
            <h2>Olá, {$firstName}!</h2>
            <p>Recebemos sua solicitação de cancelamento de conta.</p>
            
            <div class='warning-box'>
                <p><strong>⏱️ Período de Espera: 7 dias</strong></p>
                <p>Sua conta foi desativada e será <strong>permanentemente excluída em {$deletionDateFormatted}</strong>.</p>
            </div>
            
            <h3>O que acontece agora?</h3>
            <ul>
                <li>✅ Sua conta foi <strong>desativada imediatamente</strong></li>
                <li>⏳ Você tem <strong>7 dias para reativar</strong> sua conta</li>
                <li>🗑️ Após 7 dias, todos os seus dados serão <strong>permanentemente excluídos</strong></li>
                <li>📧 Você não receberá mais emails promocionais</li>
            </ul>
            
            <div class='info-box'>
    <h3>Mudou de Ideia?</h3>
    <p>
        Se você deseja <strong>reativar sua conta</strong>, é necessário entrar em contato com nossa equipe de suporte dentro do prazo de 7 dias a partir desta solicitação.
        Envie uma mensagem ao suporte antes de {$deletionDateFormatted} para solicitar a reativação.
    </p>
    <div style='text-align: center; margin-top: 20px;'>
        <a href='https://poker111.digital/contact' class='button'>CONTATAR SUPORTE</a>
    </div>
</div>
            
            <p style='margin-top: 30px;'>Sentiremos sua falta! Se houver algo que possamos melhorar, adoraríamos ouvir seu feedback.</p>
            
            <p>Equipe Poker111</p>
        </div>
        <div class='footer'>
            <p>&copy; 2026 Poker111. Todos os direitos reservados.</p>
            <p>Este é um email automático, por favor não responda.</p>
        </div>
    </div>
</body>
</html>
            ";
        } else {
            $subject = 'Account Deletion Confirmation - Poker111';
            $body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #00133D 0%, #012164 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .header img { max-width: 150px; height: auto; margin-bottom: 10px; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .info-box { background: white; border-left: 4px solid #ff3366; padding: 15px; margin: 20px 0; }
        .warning-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        .button { display: inline-block; background: #00d4ff; color: #00133D; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <img src='https://img.lightshot.app/KKOwQwLbTOGx-UsPLA05Mg.png' alt='Poker111' style='max-width: 150px; height: auto; margin-bottom: 10px;'>
            <p>Account Deletion Request</p>
        </div>
        <div class='content'>
            <h2>Hello, {$firstName}!</h2>
            <p>We received your account deletion request.</p>
            
            <div class='warning-box'>
                <p><strong>⏱️ Waiting Period: 7 days</strong></p>
                <p>Your account has been deactivated and will be <strong>permanently deleted on {$deletionDateFormatted}</strong>.</p>
            </div>
            
            <h3>What happens now?</h3>
            <ul>
                <li>✅ Your account was <strong>deactivated immediately</strong></li>
                <li>⏳ You have <strong>7 days to reactivate</strong> your account</li>
                <li>🗑️ After 7 days, all your data will be <strong>permanently deleted</strong></li>
                <li>📧 You will no longer receive promotional emails</li>
            </ul>
            
            <div class='info-box'>
    <h3>Changed Your Mind?</h3>
    <p>
        If you wish to <strong>reactivate your account</strong>, you must contact our support team within 7 days of this request.
        Please send a message to our support team before {$deletionDateFormatted} to request account reactivation.
    </p>
    <div style='text-align: center; margin-top: 20px;'>
        <a href='https://poker111.digital/contact' class='button'>CONTACT SUPPORT</a>
    </div>
</div>
            
            <p style='margin-top: 30px;'>We'll miss you! If there's anything we can improve, we'd love to hear your feedback.</p>
            
            <p>Poker111 Team</p>
        </div>
        <div class='footer'>
            <p>&copy; 2026 Poker111. All rights reserved.</p>
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>
            ";
        }
        
        return [
            'subject' => $subject,
            'body' => $body
        ];
    }
}
