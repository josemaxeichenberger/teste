<?php
/**
 * API para verificar se telefone já existe
 * @author GustavoChaconDeveloper
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../conexao/conexao.php';

try {
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    // Pegar dados JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['phone']) || !isset($data['countryCode'])) {
        throw new Exception('Dados incompletos');
    }
    
    $phone = $data['phone'];
    $countryCode = $data['countryCode'];
    
    // Buscar no banco
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE telefone = ? AND codigo_pais = ?");
    $stmt->execute([$phone, $countryCode]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'exists' => true,
            'message' => 'Este número de telefone já está cadastrado.'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'exists' => false,
            'message' => 'Telefone disponível.'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
