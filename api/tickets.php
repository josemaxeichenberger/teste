<?php
/**
 * API de Gerenciamento de Tickets
 * @author GustavoChaconDeveloper
 * @description API para adicionar, usar e consultar tickets dos usuários
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../conexao/conexao.php';
require_once '../helpers/TicketManager.php';

// ============================================================================
// VERIFICAR AUTENTICAÇÃO (PARA AÇÕES DO USUÁRIO)
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
// PROCESSAR REQUISIÇÕES GET
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? null;
    
    switch ($action) {
        case 'contar':
            // Contar tickets disponíveis
            $userId = verificarAutenticacao();
            $quantidade = TicketManager::contarTicketsDisponiveis($userId);
            
            echo json_encode([
                'success' => true,
                'quantidade' => $quantidade
            ]);
            break;
            
        case 'listar':
            // Listar tickets
            $userId = verificarAutenticacao();
            $status = $_GET['status'] ?? null;
            $tickets = TicketManager::listarTickets($userId, $status);
            
            echo json_encode([
                'success' => true,
                'tickets' => $tickets,
                'total' => count($tickets)
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Ação não especificada'
            ]);
            break;
    }
    exit;
}

// ============================================================================
// PROCESSAR REQUISIÇÕES POST
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!isset($data['action'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Ação não especificada.'
        ]);
        exit;
    }
    
    $action = $data['action'];
    $response = null;
    
    switch ($action) {
        case 'adicionar':
            // ADMIN: Adicionar tickets a um usuário
            // Verificar se é admin (você pode adicionar verificação de permissão aqui)
            $userId = $data['userId'] ?? null;
            $quantidade = $data['quantidade'] ?? 1;
            $tipo = $data['tipo'] ?? 'challenge_entry';
            $origem = $data['origem'] ?? 'admin';
            $descricao = $data['descricao'] ?? null;
            
            if (!$userId) {
                $response = [
                    'success' => false,
                    'message' => 'ID do usuário não fornecido'
                ];
                break;
            }
            
            $response = TicketManager::adicionarTickets(
                $userId, 
                $quantidade, 
                $tipo, 
                $origem, 
                $descricao
            );
            break;
            
        case 'usar':
            // Usar um ticket
            $userId = verificarAutenticacao();
            $usadoEm = $data['usadoEm'] ?? null;
            
            $response = TicketManager::usarTicket($userId, $usadoEm);
            break;
            
        case 'limpar_expirados':
            // Limpar tickets expirados (pode ser executado por cron/admin)
            $quantidade = TicketManager::limparTicketsExpirados();
            
            $response = [
                'success' => true,
                'message' => "Tickets expirados limpos",
                'quantidade' => $quantidade
            ];
            break;
            
        default:
            $response = [
                'success' => false,
                'message' => 'Ação inválida'
            ];
            break;
    }
    
    echo json_encode($response);
    exit;
}

// Método não permitido
echo json_encode([
    'success' => false,
    'message' => 'Método não permitido'
]);
