<?php
/**
 * API para buscar perguntas do questionário
 * Retorna todas as perguntas ativas com suas opções
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../conexao/conexao.php';

try {
    $conn = getConnection();
    
    // Buscar todas as perguntas ativas ordenadas
    $sql = "SELECT 
                id,
                texto_pergunta,
                ordem_pergunta,
                tipo_pergunta
            FROM questionario_perguntas
            WHERE esta_ativa = 1
            ORDER BY ordem_pergunta ASC";
    
    $stmt = $conn->query($sql);
    $perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Para cada pergunta, buscar suas opções
    foreach ($perguntas as &$pergunta) {
        $sqlOpcoes = "SELECT 
                        id,
                        texto_opcao,
                        ordem_opcao
                      FROM questionario_opcoes
                      WHERE pergunta_id = :pergunta_id
                      ORDER BY ordem_opcao ASC";
        
        $stmtOpcoes = $conn->prepare($sqlOpcoes);
        $stmtOpcoes->execute(['pergunta_id' => $pergunta['id']]);
        $pergunta['opcoes'] = $stmtOpcoes->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'perguntas' => $perguntas,
            'total' => count($perguntas)
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar perguntas: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
