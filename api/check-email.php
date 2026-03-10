<?php
/**
 * API de Verificação de Email
 * @author GustavoChaconDeveloper
 * @description Endpoint para verificar se email já está cadastrado
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
// PROCESSAR VERIFICAÇÃO
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (empty($data['email'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Email não informado.'
        ]);
        exit;
    }
    
    try {
        $conn = getConnection();
        
        // Verificar se email já existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo json_encode([
                'success' => false,
                'exists' => true,
                'message' => 'Este email já está cadastrado.'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'exists' => false,
                'message' => 'Email disponível.'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Erro ao verificar email: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao verificar email.'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
}
