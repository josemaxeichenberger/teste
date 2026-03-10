<?php
/**
 * API de Logout
 * @author GustavoChaconDeveloper
 * @description Endpoint para encerrar sessão do usuário
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../conexao/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    
    try {
        // Log de logout se usuário estiver logado
        if (isset($_SESSION['user_id'])) {
            $conn = getConnection();
            
            $stmt = $conn->prepare("
                INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip) 
                VALUES (?, 'logout', 'Usuário fez logout', ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        }
        
        // Destruir sessão
        session_unset();
        session_destroy();
        
        echo json_encode([
            'success' => true,
            'message' => 'Logout realizado com sucesso.'
        ]);
        
    } catch (Exception $e) {
        error_log("Erro no logout: " . $e->getMessage());
        
        // Mesmo com erro, destruir sessão
        session_unset();
        session_destroy();
        
        echo json_encode([
            'success' => true,
            'message' => 'Logout realizado.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
}
