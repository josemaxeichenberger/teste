<?php
/**
 * API para salvar progresso do questionário
 * Salva progresso parcial enquanto usuário responde
 */

// Configurar timezone para evitar diferenças de horário
date_default_timezone_set('America/Sao_Paulo');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../conexao/conexao.php';

try {
    // Receber dados JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Verificar se userId foi enviado
    if (!isset($data['userId']) || empty($data['userId'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Usuário não identificado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $userId = intval($data['userId']);
    $perguntaAtual = isset($data['perguntaAtual']) ? intval($data['perguntaAtual']) : 1;
    $totalPerguntas = isset($data['totalPerguntas']) ? intval($data['totalPerguntas']) : 8;
    $trackAtual = isset($data['trackAtual']) ? $data['trackAtual'] : null;
    $respostas = isset($data['respostas']) ? $data['respostas'] : [];
    
    $conn = getConnection();
    $conn->beginTransaction();
    
    // Calcular percentual de conclusão
    $percentualConclusao = ($perguntaAtual / $totalPerguntas) * 100;
    
    // Salvar/atualizar progresso
    $sqlProgress = "INSERT INTO questionario_progresso 
                    (usuario_id, pergunta_atual, track_atual, esta_completo, iniciado_em, atualizado_em)
                    VALUES (:usuario_id, :pergunta_atual, :track_atual, 0, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE 
                        pergunta_atual = VALUES(pergunta_atual),
                        track_atual = VALUES(track_atual),
                        atualizado_em = NOW()";
    
    $stmtProgress = $conn->prepare($sqlProgress);
    $stmtProgress->execute([
        'usuario_id' => $userId,
        'pergunta_atual' => $perguntaAtual,
        'track_atual' => $trackAtual
    ]);
    
    // Salvar respostas parciais (limpar e reinserir)
    if (!empty($respostas)) {
        // Limpar respostas anteriores
        $sqlDelete = "DELETE FROM questionario_respostas WHERE usuario_id = :usuario_id";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->execute(['usuario_id' => $userId]);
        
        // Inserir respostas atuais
        $sqlInsert = "INSERT INTO questionario_respostas 
                      (usuario_id, pergunta_id, opcao_id, respondido_em) 
                      VALUES (:usuario_id, :pergunta_id, :opcao_id, NOW())";
        
        $stmtInsert = $conn->prepare($sqlInsert);
        
        foreach ($respostas as $resposta) {
            if (isset($resposta['perguntaId']) && isset($resposta['opcaoId'])) {
                $stmtInsert->execute([
                    'usuario_id' => $userId,
                    'pergunta_id' => $resposta['perguntaId'],
                    'opcao_id' => $resposta['opcaoId']
                ]);
            }
        }
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Progresso salvo com sucesso!',
        'data' => [
            'trackAtual' => $trackAtual,
            'perguntaAtual' => $perguntaAtual,
            'percentualConclusao' => round($percentualConclusao, 2)
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao salvar progresso: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
