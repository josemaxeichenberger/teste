<?php
/**
 * API de Newsletter
 * @author GustavoChaconDeveloper
 * @description Processa inscrições na newsletter do footer
 */

// Start output buffering FIRST to capture any errors
ob_start();

// Error handling - captura TODOS os tipos de erro
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Function to send JSON response and exit cleanly
function sendJsonResponse($data) {
    // Clear any output buffer
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Ensure no previous output
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    }
    
    echo json_encode($data);
    exit;
}

// Capturar erros fatais
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Erro fatal no servidor',
            'debug' => $error['message'] . ' in ' . $error['file'] . ':' . $error['line']
        ]);
    }
});

// Capture all non-fatal errors and convert to exceptions
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    require_once '../conexao/conexao.php';
    require_once '../helpers/EmailSMTP.php';
} catch (Exception $e) {
    sendJsonResponse([
        'success' => false, 
        'message' => 'Erro ao carregar dependências: ' . $e->getMessage()
    ]);
}

use PHPMailer\PHPMailer\PHPMailer;

class NewsletterManager {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function subscribe($email) {
        try {
            // Validar email
            if (empty($email)) {
                return ['success' => false, 'message' => 'Por favor, insira seu email.'];
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Email inválido.'];
            }
            
            // Verificar se já está inscrito
            $stmt = $this->conn->prepare("
                SELECT id, status, confirmado 
                FROM newsletter 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                if ($existing['status'] === 'ativo') {
                    return ['success' => false, 'message' => 'Este email já está inscrito na newsletter!'];
                } else {
                    // Reativar inscrição
                    $stmt = $this->conn->prepare("
                        UPDATE newsletter 
                        SET status = 'ativo', 
                            data_confirmacao = NOW()
                        WHERE email = ?
                    ");
                    $stmt->execute([$email]);
                    
                    $this->sendWelcomeEmail($email);
                    
                    return [
                        'success' => true, 
                        'message' => 'Newsletter reativada! Verifique seu email.'
                    ];
                }
            }
            
            // Inserir novo assinante
            $token = bin2hex(random_bytes(32));
            
            $stmt = $this->conn->prepare("
                INSERT INTO newsletter (
                    email, 
                    origem, 
                    status, 
                    token_cancelamento, 
                    confirmado,
                    endereco_ip,
                    data_inscricao,
                    data_confirmacao
                ) VALUES (?, 'footer', 'ativo', ?, 1, ?, NOW(), NOW())
            ");
            
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $stmt->execute([$email, $token, $ip]);
            
            // Enviar email de boas-vindas (não falha se email não enviar)
            try {
                $this->sendWelcomeEmail($email, $token);
            } catch (Exception $emailError) {
                error_log("Aviso: Email de boas-vindas não enviado: " . $emailError->getMessage());
                // Não falha a inscrição se email não enviar
            }
            
            return [
                'success' => true,
                'message' => 'Inscrição realizada com sucesso! Verifique seu email.'
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao inscrever na newsletter: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao processar inscrição.'];
        }
    }
    
    private function sendWelcomeEmail($email, $token = null) {
        try {
            $emailHelper = new EmailSMTP();
            
            $unsubscribeLink = $token 
                ? "https://" . $_SERVER['HTTP_HOST'] . "/unsubscribe?token=" . $token
                : "#";
            
            $subject = "Bem-vindo à Newsletter Poker111! 🎉";
            
            $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #00133D 0%, #012164 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .header img { max-width: 150px; height: auto; margin-bottom: 10px; }
                    .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .button { display: inline-block; background: #ff3366; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
                    .button:hover { background: #e02050; }
                    .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <img src='https://img.lightshot.app/KKOwQwLbTOGx-UsPLA05Mg.png' alt='Poker111'>
                        <p>Newsletter</p>
                    </div>
                    <div class='content'>
                        <h2>Bem-vindo!</h2>
                        <p>Obrigado por se inscrever na nossa newsletter!</p>
                        <p>Você está agora inscrito e receberá:</p>
                        <ul>
                            <li>📰 Notícias exclusivas sobre torneios</li>
                            <li>🎯 Dicas e estratégias de poker</li>
                            <li>🎁 Ofertas especiais para membros</li>
                            <li>🏆 Atualizações sobre desafios e competições</li>
                        </ul>
                        <p>Fique atento ao seu email para não perder nenhuma novidade!</p>
                        <div style='text-align: center;'>
                            <a href='https://{$_SERVER['HTTP_HOST']}' class='button'>Visite nosso site</a>
                        </div>
                    </div>
                    <div class='footer'>
                        <p>Você está recebendo este email porque se inscreveu na newsletter de Poker111.</p>
                        <p><a href='{$unsubscribeLink}' style='color: #0066cc;'>Cancelar inscrição</a></p>
                        <p>&copy; " . date('Y') . " Poker111. Todos os direitos reservados.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            return $emailHelper->sendEmail($email, $subject, $message);
            
        } catch (Exception $e) {
            error_log("Erro ao enviar email de boas-vindas: " . $e->getMessage());
            return false;
        }
    }
}

// Processar requisição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obter conexão usando o padrão do projeto
        $conn = getConnection();
        
        if (!$conn) {
            sendJsonResponse(['success' => false, 'message' => 'Erro de conexão com banco de dados.']);
        }
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJsonResponse(['success' => false, 'message' => 'JSON inválido.']);
        }
        
        $manager = new NewsletterManager($conn);
        $result = $manager->subscribe($data['email'] ?? '');
        
        sendJsonResponse($result);
        
    } catch (Exception $e) {
        $errorDetails = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];
        error_log("Erro na API de newsletter: " . json_encode($errorDetails));
        
        sendJsonResponse([
            'success' => false, 
            'message' => 'Erro no servidor',
            'debug' => $e->getMessage() . ' (linha ' . $e->getLine() . ')'
        ]);
    }
} else {
    http_response_code(405);
    sendJsonResponse(['success' => false, 'message' => 'Método não permitido.']);
}
