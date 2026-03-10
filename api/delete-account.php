<?php
/**
 * API de Cancelamento de Conta
 * @author GustavoChaconDeveloper
 * @description Endpoint para solicitar cancelamento/desativação de conta
 */

// ============================================================================
// CONFIGURAÇÕES INICIAIS
// ============================================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../conexao/conexao.php';
require_once '../helpers/EmailSMTP.php';

// ============================================================================
// VERIFICAR AUTENTICAÇÃO
// ============================================================================
function verificarAutenticacao() {
    $is_logged_in = isset($_SESSION['logged_in']) && ($_SESSION['logged_in'] === true || $_SESSION['logged_in'] == 1);
    $has_user_id = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    
    if (!$is_logged_in || !$has_user_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Você precisa estar logado para realizar esta ação.'
        ]);
        exit;
    }
    
    return $_SESSION['user_id'];
}

// ============================================================================
// CRIAR TABELA DE SOLICITAÇÕES DE CANCELAMENTO
// ============================================================================
function garantirTabelaCancelamentos() {
    try {
        $conn = getConnection();
        
        $sql = "
            CREATE TABLE IF NOT EXISTS solicitacoes_cancelamento (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                motivos TEXT NOT NULL COMMENT 'JSON com os motivos selecionados',
                feedback_adicional TEXT NULL,
                data_solicitacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                data_efetivacao DATETIME NULL COMMENT 'Data em que a conta será deletada (7 dias após solicitação)',
                status VARCHAR(20) NOT NULL DEFAULT 'pendente' COMMENT 'pendente, reativado, efetivado',
                endereco_ip VARCHAR(45) NULL,
                user_agent TEXT NULL,
                INDEX idx_usuario_id (usuario_id),
                INDEX idx_status (status),
                INDEX idx_data_efetivacao (data_efetivacao),
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $conn->exec($sql);
        return true;
        
    } catch (PDOException $e) {
        error_log("Erro ao criar tabela solicitacoes_cancelamento: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// PROCESSAR SOLICITAÇÃO DE CANCELAMENTO
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = verificarAutenticacao();
    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validar entrada
    if (empty($data['reasons']) || !is_array($data['reasons'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Por favor, selecione pelo menos um motivo para o cancelamento.'
        ]);
        exit;
    }
    
    try {
        $conn = getConnection();
        
        // Garantir que a tabela existe
        garantirTabelaCancelamentos();
        
        // Buscar dados do usuário
        $stmt = $conn->prepare("
            SELECT 
                id, 
                email, 
                primeiro_nome, 
                sobrenome,
                status,
                idioma
            FROM usuarios 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo json_encode([
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ]);
            exit;
        }
        
        // Verificar se já existe uma solicitação pendente
        $stmt = $conn->prepare("
            SELECT id 
            FROM solicitacoes_cancelamento 
            WHERE usuario_id = ? 
            AND status = 'pendente'
        ");
        $stmt->execute([$userId]);
        
        if ($stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Você já possui uma solicitação de cancelamento pendente.'
            ]);
            exit;
        }
        
        // Preparar dados
        $motivos = json_encode($data['reasons'], JSON_UNESCAPED_UNICODE);
        $feedback = isset($data['feedback']) ? trim($data['feedback']) : null;
        $dataEfetivacao = date('Y-m-d H:i:s', strtotime('+7 days'));
        $enderecoIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Iniciar transação
        $conn->beginTransaction();
        
        // Inserir solicitação de cancelamento
        $stmt = $conn->prepare("
            INSERT INTO solicitacoes_cancelamento 
            (usuario_id, motivos, feedback_adicional, data_efetivacao, endereco_ip, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $motivos,
            $feedback,
            $dataEfetivacao,
            $enderecoIp,
            $userAgent
        ]);
        
        $solicitacaoId = $conn->lastInsertId();
        
        // Atualizar status do usuário para "inativo"
        $stmt = $conn->prepare("
            UPDATE usuarios 
            SET status = 'inativo',
                data_atualizacao = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        
        // Log da atividade
        $stmt = $conn->prepare("
            INSERT INTO logs_atividades 
            (usuario_id, acao, descricao, endereco_ip) 
            VALUES (?, 'account_deletion_requested', ?, ?)
        ");
        $stmt->execute([
            $userId,
            'Solicitação de cancelamento de conta. Efetivação em: ' . $dataEfetivacao,
            $enderecoIp
        ]);
        
        // Commit da transação
        $conn->commit();
        
        // Enviar email de confirmação
        $emailSMTP = new EmailSMTP();
        $idioma = $user['idioma'] ?? 'pt';
        
        try {
            $emailSMTP->sendAccountDeletionConfirmation(
                $user['email'],
                $user['primeiro_nome'],
                $dataEfetivacao,
                $idioma
            );
        } catch (Exception $e) {
            error_log("Erro ao enviar email de confirmação de cancelamento: " . $e->getMessage());
            // Não falhar a operação se o email não for enviado
        }
        
        // Limpar sessão
        session_unset();
        session_destroy();
        
        echo json_encode([
            'success' => true,
            'message' => 'Solicitação de cancelamento registrada com sucesso.',
            'data' => [
                'solicitacaoId' => $solicitacaoId,
                'dataEfetivacao' => $dataEfetivacao,
                'diasRestantes' => 7
            ]
        ]);
        
    } catch (PDOException $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        
        error_log("Erro ao processar cancelamento de conta: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao processar solicitação. Por favor, tente novamente.'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
}
