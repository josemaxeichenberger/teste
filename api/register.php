<?php
/**
 * API de Registro de Usuário
 * @author GustavoChaconDeveloper
 * @description Endpoint para criar novos usuários no sistema
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
// CLASSE DE REGISTRO
// ============================================================================
class UserRegistration {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Registrar novo usuário
     */
    public function register($data) {
        try {
            // Validar dados de entrada
            $validation = $this->validateInput($data);
            if (!$validation['success']) {
                return $validation;
            }
            
            // Verificar se email já existe
            if ($this->emailExists($data['email'])) {
                // Se email existe, verificar se está bloqueado
                $userId = $this->getUserIdByEmail($data['email']);
                if ($userId) {
                    $bloqueioInfo = $this->checkBlocking($userId, $data['verificationMethod'] ?? 'sms');
                    if ($bloqueioInfo['blocked']) {
                        return [
                            'success' => false,
                            'message' => $bloqueioInfo['message']
                        ];
                    }
                }
                
                return [
                    'success' => false,
                    'message' => 'Este email já está cadastrado.'
                ];
            }
            
            // Verificar se telefone já existe
            if ($this->phoneExists($data['phone'], $data['countryCode'])) {
                return [
                    'success' => false,
                    'message' => 'Este telefone já está cadastrado.'
                ];
            }
            
            // Criar usuário
            $userId = $this->createUser($data);
            
            if ($userId) {
                // Criar perfil do usuário
                $this->createUserProfile($userId);
                
                // Gerar código de verificação
                $verificationCode = $this->generateVerificationCode();
                
                // Verificar método de verificação escolhido
                $verificationMethod = $data['verificationMethod'] ?? 'sms';
                
                // Salvar código e enviar via método escolhido
                $this->saveVerificationCode($userId, $verificationCode, $verificationMethod);
                
                // Log da atividade
                $this->logActivity($userId, 'user_registered', "Novo usuário registrado - Método: $verificationMethod");
                
                return [
                    'success' => true,
                    'message' => 'Cadastro realizado com sucesso!',
                    'data' => [
                        'userId' => $userId,
                        'verificationRequired' => true,
                        'verificationMethod' => $verificationMethod,
                        'verificationCode' => $verificationCode // Remover em produção
                    ]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Erro ao criar usuário. Tente novamente.'
            ];
            
        } catch (Exception $e) {
            error_log("Erro no registro: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente mais tarde.',
                'debug' => $e->getMessage() // Adicionar para debug
            ];
        }
    }
    
    /**
     * Validar dados de entrada
     */
    private function validateInput($data) {
        $errors = [];
        
        // Validar email
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido.';
        }
        
        // Validar senha
        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors[] = 'A senha deve ter no mínimo 8 caracteres.';
        }
        
        if (!preg_match('/[A-Z]/', $data['password'])) {
            $errors[] = 'A senha deve conter pelo menos uma letra maiúscula.';
        }
        
        // Validar nome
        if (empty($data['firstName']) || strlen($data['firstName']) < 2) {
            $errors[] = 'Nome inválido.';
        }
        
        if (empty($data['lastName']) || strlen($data['lastName']) < 2) {
            $errors[] = 'Sobrenome inválido.';
        }
        
        // Validar telefone
        if (empty($data['phone'])) {
            $errors[] = 'Telefone é obrigatório.';
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $errors
            ];
        }
        
        return ['success' => true];
    }
    
    /**
     * Verificar se email já existe
     */
    private function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Obter userId pelo email
     */
    private function getUserIdByEmail($email) {
        $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : null;
    }
    
    /**
     * Verificar se usuário está bloqueado
     */
    private function checkBlocking($userId, $method) {
        $tentativasField = $method === 'email' ? 'tentativas_reenvio_email' : 'tentativas_reenvio_sms';
        $bloqueioField = $method === 'email' ? 'bloqueio_email_ate' : 'bloqueio_sms_ate';
        $methodName = $method === 'email' ? 'Email' : 'SMS';
        
        $stmt = $this->conn->prepare("
            SELECT $tentativasField as tentativas,
                   $bloqueioField as bloqueio,
                   IF($bloqueioField > NOW(), 1, 0) as esta_bloqueado,
                   CEIL(TIMESTAMPDIFF(SECOND, NOW(), $bloqueioField) / 60) as minutos_restantes
            FROM usuarios 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        if ($result && $result['esta_bloqueado']) {
            return [
                'blocked' => true,
                'message' => "Limite de reenvios de $methodName atingido. Aguarde {$result['minutos_restantes']} minutos."
            ];
        }
        
        return ['blocked' => false];
    }
    
    /**
     * Verificar se telefone já existe
     */
    private function phoneExists($phone, $countryCode) {
        $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE telefone = ? AND codigo_pais = ?");
        $stmt->execute([$phone, $countryCode]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Criar usuário no banco
     */
    private function createUser($data) {
        // Definir método de verificação
        $verificationMethod = $data['verificationMethod'] ?? 'sms';
        
        // Inicializar tentativas: 1 no método escolhido, 0 no outro
        $tentativasSMS = ($verificationMethod === 'sms') ? 1 : 0;
        $tentativasEmail = ($verificationMethod === 'email') ? 1 : 0;
        
        $sql = "INSERT INTO usuarios (
            email, 
            senha_hash, 
            primeiro_nome, 
            sobrenome, 
            telefone, 
            codigo_pais,
            idioma,
            status,
            tentativas_reenvio_sms,
            tentativas_reenvio_email
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pendente', ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        $language = $data['language'] ?? 'pt';
        
        $stmt->execute([
            $data['email'],
            $passwordHash,
            $data['firstName'],
            $data['lastName'],
            $data['phone'],
            $data['countryCode'],
            $language,
            $tentativasSMS,
            $tentativasEmail
        ]);
        
        return $this->conn->lastInsertId();
    }
    
    /**
     * Criar perfil do usuário
     */
    private function createUserProfile($userId) {
        $sql = "INSERT INTO perfis_usuarios (usuario_id) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
    }
    
    /**
     * Gerar código de verificação
     */
    private function generateVerificationCode() {
        return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Salvar código SMS na tabela codigos_sms
     */
    private function saveVerificationCode($userId, $code, $method = 'sms') {
        // Buscar dados do usuário
        $stmt = $this->conn->prepare("SELECT telefone, codigo_pais, idioma FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return false;
        }
        
        // Invalidar códigos anteriores do mesmo usuário
        $sql = "UPDATE codigos_sms SET usado = 1 WHERE usuario_id = ? AND usado = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        
        // Inserir novo código com expiração de 3 minutos
        $sql = "INSERT INTO codigos_sms (
                    usuario_id, 
                    codigo, 
                    telefone, 
                    codigo_pais, 
                    tipo_envio,
                    expira_em,
                    endereco_ip
                ) VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 3 MINUTE), ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $userId,
            $code,
            $user['telefone'],
            $user['codigo_pais'],
            $method,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        // Verificar método de verificação
        if ($method === 'sms') {
            // ENVIAR SMS VIA TWILIO
            require_once __DIR__ . '/../helpers/TwilioSMS.php';
            $twilio = new TwilioSMS();
            
            $fullPhone = $user['codigo_pais'] . $user['telefone'];
            $language = $user['idioma'] ?? 'pt';
            
            $smsResult = $twilio->sendVerificationCode($fullPhone, $code, $language);
            
            if ($smsResult['success']) {
                error_log("SMS enviado com sucesso ({$smsResult['mode']}): SID=" . ($smsResult['sid'] ?? 'N/A'));
            } else {
                error_log("Erro ao enviar SMS: " . ($smsResult['error'] ?? 'Desconhecido'));
                // Continuar mesmo com erro no SMS (código já foi salvo no banco)
            }
        } else if ($method === 'email') {
            // ENVIAR EMAIL VIA SMTP
            require_once __DIR__ . '/../helpers/EmailSMTP.php';
            $emailSMTP = new EmailSMTP();
            
            // Buscar email do usuário
            $stmt = $this->conn->prepare("SELECT email FROM usuarios WHERE id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch();
            
            if ($userData) {
                $language = $user['idioma'] ?? 'pt';
                
                $emailResult = $emailSMTP->sendVerificationCode($userData['email'], $code, $language);
                
                if ($emailResult['success']) {
                    error_log("Email enviado com sucesso ({$emailResult['mode']}): " . $userData['email']);
                } else {
                    error_log("Erro ao enviar email: " . ($emailResult['error'] ?? 'Desconhecido'));
                    // Continuar mesmo com erro no email (código já foi salvo no banco)
                }
            }
        }
        
        return true;
    }
    
    /**
     * Log de atividade
     */
    private function logActivity($userId, $action, $description) {
        $sql = "INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip, user_agent) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $userId,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
}

// ============================================================================
// PROCESSAR REQUISIÇÃO
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter dados JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if ($data === null) {
        echo json_encode([
            'success' => false,
            'message' => 'Dados inválidos.'
        ]);
        exit;
    }
    
    // Processar registro
    try {
        $conn = getConnection();
        $registration = new UserRegistration($conn);
        $result = $registration->register($data);
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao processar requisição.'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
}
