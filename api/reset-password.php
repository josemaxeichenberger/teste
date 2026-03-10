<?php
/**
 * API de Redefinição de Senha
 * @author GustavoChaconDeveloper
 * @description Endpoint para redefinir senha usando token de recuperação
 */

// ============================================================================
// CONFIGURAÇÕES INICIAIS
// ============================================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../conexao/conexao.php';

// ============================================================================
// MÉTODO GET - VALIDAR TOKEN
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($_GET['token'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Token não fornecido.'
        ]);
        exit;
    }
    
    $token = $_GET['token'];
    
    try {
        $conn = getConnection();
        
        // Buscar token no banco
        $stmt = $conn->prepare("
            SELECT 
                prt.id,
                prt.usuario_id,
                prt.expira_em,
                prt.usado, 
                u.email,
                u.primeiro_nome,
                (prt.expira_em > NOW()) as is_valid,
                (prt.expira_em <= NOW()) as is_expired
            FROM password_reset_tokens prt
            INNER JOIN usuarios u ON prt.usuario_id = u.id
            WHERE prt.token = ? 
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $tokenData = $stmt->fetch();
        
        if (!$tokenData) {
            echo json_encode([
                'success' => false,
                'valid' => false,
                'message' => 'Token inválido.'
            ]);
            exit;
        }
        
        // Verificar se já foi usado
        if ($tokenData['usado']) {
            echo json_encode([
                'success' => false,
                'valid' => false,
                'message' => 'Este link já foi utilizado. Solicite uma nova recuperação de senha.'
            ]);
            exit;
        }
        
        // Verificar se expirou (usando comparação MySQL NOW())
        if ($tokenData['is_expired']) {
            echo json_encode([
                'success' => false,
                'valid' => false,
                'expired' => true,
                'message' => 'Este link expirou. Solicite uma nova recuperação de senha.'
            ]);
            exit;
        }
        
        // Token válido
        echo json_encode([
            'success' => true,
            'valid' => true,
            'message' => 'Token válido. Você pode redefinir sua senha.',
            'data' => [
                'email' => $tokenData['email'],
                'firstName' => $tokenData['primeiro_nome']
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Erro ao validar token: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao validar token. Tente novamente.'
        ]);
    }
    
    exit;
}

// ============================================================================
// MÉTODO POST - REDEFINIR SENHA
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validar entrada
    if (empty($data['token']) || empty($data['newPassword']) || empty($data['confirmPassword'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Dados incompletos.'
        ]);
        exit;
    }
    
    $token = $data['token'];
    $newPassword = $data['newPassword'];
    $confirmPassword = $data['confirmPassword'];
    
    // Verificar se as senhas coincidem
    if ($newPassword !== $confirmPassword) {
        echo json_encode([
            'success' => false,
            'message' => 'As senhas não coincidem.'
        ]);
        exit;
    }
    
    // Validar força da senha
    if (strlen($newPassword) < 8) {
        echo json_encode([
            'success' => false,
            'message' => 'A senha deve ter no mínimo 8 caracteres.'
        ]);
        exit;
    }
    
    if (!preg_match('/[A-Z]/', $newPassword)) {
        echo json_encode([
            'success' => false,
            'message' => 'A senha deve conter pelo menos uma letra maiúscula.'
        ]);
        exit;
    }
    
    if (!preg_match('/[a-z]/', $newPassword)) {
        echo json_encode([
            'success' => false,
            'message' => 'A senha deve conter pelo menos uma letra minúscula.'
        ]);
        exit;
    }
    
    if (!preg_match('/[0-9]/', $newPassword)) {
        echo json_encode([
            'success' => false,
            'message' => 'A senha deve conter pelo menos um número.'
        ]);
        exit;
    }
    
    try {
        $conn = getConnection();
        
        // Buscar e validar token
        $stmt = $conn->prepare("
            SELECT 
                prt.id,
                prt.usuario_id,
                prt.expira_em,
                prt.usado,
                u.email,
                (prt.expira_em > NOW()) as is_valid,
                (prt.expira_em <= NOW()) as is_expired
            FROM password_reset_tokens prt
            INNER JOIN usuarios u ON prt.usuario_id = u.id
            WHERE prt.token = ? 
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $tokenData = $stmt->fetch();
        
        if (!$tokenData) {
            echo json_encode([
                'success' => false,
                'message' => 'Token inválido.'
            ]);
            exit;
        }
        
        // Verificar se já foi usado
        if ($tokenData['usado']) {
            echo json_encode([
                'success' => false,
                'message' => 'Este link já foi utilizado. Solicite uma nova recuperação de senha.'
            ]);
            exit;
        }
        
        // Verificar se expirou (usando comparação MySQL NOW())
        if ($tokenData['is_expired']) {
            echo json_encode([
                'success' => false,
                'expired' => true,
                'message' => 'Este link expirou. Solicite uma nova recuperação de senha.'
            ]);
            exit;
        }
        
        // Iniciar transação
        $conn->beginTransaction();
        
        // Hash da nova senha
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        
        // Atualizar senha do usuário
        $stmt = $conn->prepare("
            UPDATE usuarios 
            SET senha_hash = ?, data_atualizacao = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$passwordHash, $tokenData['usuario_id']]);
        
        // Marcar token como usado
        $stmt = $conn->prepare("
            UPDATE password_reset_tokens 
            SET usado = 1, usado_em = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$tokenData['id']]);
        
        // Log da ação
        $stmt = $conn->prepare("
            INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip) 
            VALUES (?, 'password_reset_success', 'Senha redefinida com sucesso via token', ?)
        ");
        $stmt->execute([$tokenData['usuario_id'], $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        
        // Commit
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Senha redefinida com sucesso! Você já pode fazer login com sua nova senha.'
        ]);
        
    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        
        error_log("Erro ao redefinir senha: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao redefinir senha. Tente novamente mais tarde.'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
}
