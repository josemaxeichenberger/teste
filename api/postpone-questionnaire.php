<?php
/**
 * API para adiar questionário
 * Salva que o usuário escolheu "responder mais tarde"
 */

// Configurar timezone para evitar diferenças de horário
date_default_timezone_set('America/Sao_Paulo');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../conexao/conexao.php';

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!isset($data['userId']) || empty($data['userId'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Usuário não identificado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $userId = intval($data['userId']);
    $minutosAdiamento = isset($data['minutos']) ? intval($data['minutos']) : 15;
    
    $conn = getConnection();
    
    // Calcular quando o questionário estará disponível novamente (15 minutos)
    $adiadoAte = date('Y-m-d H:i:s', strtotime("+{$minutosAdiamento} minutes"));
    
    // Salvar/atualizar adiamento
    $sql = "INSERT INTO questionario_progresso 
            (usuario_id, adiado_ate, contador_adiar, iniciado_em, atualizado_em)
            VALUES (:usuario_id, :adiado_ate, 1, NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
                adiado_ate = :adiado_ate2,
                contador_adiar = contador_adiar + 1,
                atualizado_em = NOW()";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'usuario_id' => $userId,
        'adiado_ate' => $adiadoAte,
        'adiado_ate2' => $adiadoAte
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Questionário adiado com sucesso!',
        'data' => [
            'adiadoAte' => $adiadoAte,
            'minutosAdiamento' => $minutosAdiamento
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao adiar questionário: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
