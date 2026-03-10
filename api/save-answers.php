<?php
/**
 * API para salvar respostas do questionário
 * Salva as respostas do usuário no banco de dados
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
    
    // LOG DEBUG - remover depois de testar
    error_log("📊 save-answers.php - JSON recebido: " . $json);
    error_log("📊 save-answers.php - Data decodificado: " . print_r($data, true));
    
    // Verificar se userId foi enviado
    if (!isset($data['userId']) || empty($data['userId'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Usuário não identificado. Faça login para continuar.',
            'debug' => [
                'received' => $data,
                'json' => $json
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $userId = intval($data['userId']);
    
    // Validar se usuário existe no banco
    $conn = getConnection();
    $sqlCheckUser = "SELECT id FROM usuarios WHERE id = :user_id LIMIT 1";
    $stmtCheckUser = $conn->prepare($sqlCheckUser);
    $stmtCheckUser->execute(['user_id' => $userId]);
    
    if ($stmtCheckUser->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Usuário não encontrado no banco de dados.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!isset($data['respostas']) || !is_array($data['respostas'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Dados inválidos. Formato esperado: { respostas: [...] }'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $conn = getConnection();
    $conn->beginTransaction();
    
    // Limpar respostas anteriores do usuário (se houver)
    $sqlDelete = "DELETE FROM questionario_respostas WHERE usuario_id = :usuario_id";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->execute(['usuario_id' => $userId]);
    
    // Inserir novas respostas
    $sqlInsert = "INSERT INTO questionario_respostas 
                  (usuario_id, pergunta_id, opcao_id, respondido_em) 
                  VALUES (:usuario_id, :pergunta_id, :opcao_id, NOW())";
    
    $stmtInsert = $conn->prepare($sqlInsert);
    
    $respostasSalvas = 0;
    
    foreach ($data['respostas'] as $resposta) {
        if (!isset($resposta['perguntaId']) || !isset($resposta['opcaoId'])) {
            continue; // Pular resposta inválida
        }
        
        $stmtInsert->execute([
            'usuario_id' => $userId,
            'pergunta_id' => $resposta['perguntaId'],
            'opcao_id' => $resposta['opcaoId']
        ]);
        
        $respostasSalvas++;
    }
    
    // Atualizar progresso do questionário
    $sqlProgress = "INSERT INTO questionario_progresso 
                    (usuario_id, pergunta_atual, esta_completo, iniciado_em, concluido_em, atualizado_em)
                    VALUES (:usuario_id, :total, 1, NOW(), NOW(), NOW())
                    ON DUPLICATE KEY UPDATE 
                        pergunta_atual = VALUES(pergunta_atual),
                        esta_completo = 1,
                        concluido_em = NOW(),
                        atualizado_em = NOW()";
    
    $stmtProgress = $conn->prepare($sqlProgress);
    $stmtProgress->execute([
        'usuario_id' => $userId,
        'total' => 8
    ]);
    
    // Marcar questionário como completo na tabela usuarios
    $sqlUpdateUser = "UPDATE usuarios SET questionario_completo = 1 WHERE id = :usuario_id";
    $stmtUpdateUser = $conn->prepare($sqlUpdateUser);
    $stmtUpdateUser->execute(['usuario_id' => $userId]);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Respostas salvas com sucesso!',
        'data' => [
            'respostasSalvas' => $respostasSalvas,
            'totalPerguntas' => count($data['respostas'])
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao salvar respostas: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
