<?php
/**
 * API para recuperar progresso do questionário
 * Busca progresso salvo do usuário
 */

// Configurar timezone para evitar diferenças de horário
date_default_timezone_set('America/Sao_Paulo');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once __DIR__ . '/../conexao/conexao.php';

try {
    // Receber userId via GET ou POST
    $userId = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['userId'])) {
        $userId = intval($_GET['userId']);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $userId = isset($data['userId']) ? intval($data['userId']) : null;
    }
    
    if (!$userId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'userId não fornecido.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $conn = getConnection();
    
    // Buscar progresso do usuário
    $sqlProgress = "SELECT 
                        pergunta_atual,
                        track_atual,
                        esta_completo,
                        adiado_ate,
                        contador_adiar,
                        iniciado_em,
                        concluido_em,
                        atualizado_em
                    FROM questionario_progresso
                    WHERE usuario_id = :usuario_id
                    LIMIT 1";
    
    $stmtProgress = $conn->prepare($sqlProgress);
    $stmtProgress->execute(['usuario_id' => $userId]);
    $progresso = $stmtProgress->fetch(PDO::FETCH_ASSOC);
    
    // Verificar se está adiado
    $estaAdiado = false;
    $tempoRestante = 0;
    
    if ($progresso && $progresso['adiado_ate']) {
        $agora = time();
        $adiadoAte = strtotime($progresso['adiado_ate']);
        
        // DEBUG
        error_log("🔍 DEBUG ADIAMENTO:");
        error_log("   Agora (timestamp): " . $agora);
        error_log("   Agora (data): " . date('Y-m-d H:i:s', $agora));
        error_log("   Adiado até (DB): " . $progresso['adiado_ate']);
        error_log("   Adiado até (timestamp): " . $adiadoAte);
        error_log("   Comparação: " . ($adiadoAte > $agora ? "AINDA ADIADO" : "LIBERADO"));
        
        if ($adiadoAte > $agora) {
            $estaAdiado = true;
            $tempoRestante = $adiadoAte - $agora; // em segundos
        }
    }
    
    // Buscar respostas já salvas (incluindo tag e define_track)
    $sqlRespostas = "SELECT 
                        r.pergunta_id,
                        r.opcao_id,
                        p.texto_pergunta,
                        p.pergunta_logica,
                        o.texto_opcao,
                        o.tag,
                        o.define_track,
                        r.respondido_em
                    FROM questionario_respostas r
                    JOIN questionario_perguntas p ON r.pergunta_id = p.id
                    JOIN questionario_opcoes o ON r.opcao_id = o.id
                    WHERE r.usuario_id = :usuario_id
                    ORDER BY p.pergunta_logica ASC";
    
    $stmtRespostas = $conn->prepare($sqlRespostas);
    $stmtRespostas->execute(['usuario_id' => $userId]);
    $respostas = $stmtRespostas->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'progresso' => $progresso,
            'respostas' => $respostas,
            'temProgresso' => !empty($progresso),
            'estaCompleto' => $progresso ? (bool)$progresso['esta_completo'] : false,
            'estaAdiado' => $estaAdiado,
            'tempoRestante' => $tempoRestante,
            'adiadoAte' => $progresso && $progresso['adiado_ate'] ? $progresso['adiado_ate'] : null
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar progresso: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
