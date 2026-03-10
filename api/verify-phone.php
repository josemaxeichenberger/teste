<?php
/**
 * API de Verificação de Telefone
 * @author GustavoChaconDeveloper
 * @description Endpoint para verificar código SMS do usuário
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
    
    if (empty($data['userId']) || empty($data['code'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Dados incompletos.'
        ]);
        exit;
    }
    
    try {
        $conn = getConnection();
        
        // Buscar código SMS válido e não expirado
        $stmt = $conn->prepare("
            SELECT 
                cs.id,
                cs.codigo,
                cs.expira_em,
                cs.tentativas,
                cs.bloqueado,
                u.id as usuario_id
            FROM codigos_sms cs
            INNER JOIN usuarios u ON cs.usuario_id = u.id
            WHERE cs.usuario_id = ? 
                AND cs.usado = 0
                AND cs.bloqueado = 0
            ORDER BY cs.criado_em DESC
            LIMIT 1
        ");
        $stmt->execute([$data['userId']]);
        $smsCode = $stmt->fetch();
        
        if (!$smsCode) {
            echo json_encode([
                'success' => false,
                'message' => 'Código não encontrado ou já foi usado.'
            ]);
            exit;
        }
        
        // Verificar se está bloqueado por tentativas
        if ($smsCode['bloqueado']) {
            echo json_encode([
                'success' => false,
                'message' => 'Código bloqueado por excesso de tentativas. Solicite um novo código.'
            ]);
            exit;
        }
        
        // PRIMEIRO: Verificar se o código está correto
        if ($smsCode['codigo'] !== $data['code']) {
            // Incrementar tentativas
            $tentativas = $smsCode['tentativas'] + 1;
            $bloqueado = $tentativas >= 3 ? 1 : 0; // Bloquear após 3 tentativas
            
            $stmt = $conn->prepare("
                UPDATE codigos_sms 
                SET tentativas = ?, bloqueado = ? 
                WHERE id = ?
            ");
            $stmt->execute([$tentativas, $bloqueado, $smsCode['id']]);
            
            if ($bloqueado) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Código inválido. Você excedeu o número de tentativas. Solicite um novo código.'
                ]);
            } else {
                $tentativasRestantes = 3 - $tentativas;
                echo json_encode([
                    'success' => false,
                    'message' => "Código inválido. Você tem $tentativasRestantes tentativa(s) restante(s)."
                ]);
            }
            exit;
        }
        
        // DEPOIS: Verificar expiração (só se o código estiver correto)
        // Usar MySQL NOW() para comparação de timezone consistente
        $stmt = $conn->prepare("
            SELECT IF(expira_em < NOW(), 1, 0) as expirado 
            FROM codigos_sms 
            WHERE id = ?
        ");
        $stmt->execute([$smsCode['id']]);
        $expiracao = $stmt->fetch();
        
        if ($expiracao && $expiracao['expirado'] == 1) {
            echo json_encode([
                'success' => false,
                'message' => 'Código expirado (válido por 3 minutos). Solicite um novo código.'
            ]);
            exit;
        }
        
        // Código válido! Marcar como usado
        $stmt = $conn->prepare("
            UPDATE codigos_sms 
            SET usado = 1, usado_em = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$smsCode['id']]);
        
        // Marcar telefone como verificado no usuário E resetar contadores de tentativas
        $stmt = $conn->prepare("
            UPDATE usuarios 
            SET telefone_verificado = 1, 
                status = 'ativo',
                tentativas_reenvio_sms = 0,
                tentativas_reenvio_email = 0,
                bloqueio_sms_ate = NULL,
                bloqueio_email_ate = NULL
            WHERE id = ?
        ");
        $stmt->execute([$data['userId']]);
        
        // Log da atividade
        $stmt = $conn->prepare("
            INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip) 
            VALUES (?, 'telefone_verificado', 'Telefone verificado com sucesso via SMS', ?)
        ");
        $stmt->execute([$data['userId'], $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Telefone verificado com sucesso!'
        ]);
        
    } catch (Exception $e) {
        error_log("Erro na verificação: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao verificar código.'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
}
