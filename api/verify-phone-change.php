<?php
/**
 * API - Verificar Código e Atualizar Telefone
 * @author GustavoChaconDeveloper
 * @description Valida código SMS e atualiza telefone do usuário
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../conexao/conexao.php';

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validar entrada
    if (empty($data['code'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Código de verificação é obrigatório.'
        ]);
        exit;
    }
    
    // Verificar se usuário está logado
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Você precisa estar logado.'
        ]);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $code = $data['code'];
    $language = $data['language'] ?? 'pt';
    
    try {
        $conn = getConnection();
        
        // Buscar código de verificação válido
        $stmt = $conn->prepare("
            SELECT id, telefone_novo, codigo_pais_novo, tentativas 
            FROM codigos_verificacao_telefone 
            WHERE usuario_id = ? 
            AND codigo = ? 
            AND tipo = 'phone_change'
            AND usado = 0 
            AND expira_em > NOW()
            ORDER BY criado_em DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId, $code]);
        $verification = $stmt->fetch();
        
        if (!$verification) {
            // Verificar se existe código mas expirado
            $stmt = $conn->prepare("
                SELECT id, expira_em 
                FROM codigos_verificacao_telefone 
                WHERE usuario_id = ? 
                AND codigo = ? 
                AND tipo = 'phone_change'
                AND usado = 0
                ORDER BY criado_em DESC 
                LIMIT 1
            ");
            $stmt->execute([$userId, $code]);
            $expiredCode = $stmt->fetch();
            
            if ($expiredCode) {
                echo json_encode([
                    'success' => false,
                    'message' => $language === 'pt' 
                        ? 'Código expirado. Solicite um novo código.' 
                        : 'Code expired. Request a new code.',
                    'error_code' => 'CODE_EXPIRED'
                ]);
            } else {
                // Incrementar tentativas se houver código ativo
                $stmt = $conn->prepare("
                    UPDATE codigos_verificacao_telefone 
                    SET tentativas = tentativas + 1 
                    WHERE usuario_id = ? 
                    AND tipo = 'phone_change'
                    AND usado = 0 
                    AND expira_em > NOW()
                ");
                $stmt->execute([$userId]);
                
                echo json_encode([
                    'success' => false,
                    'message' => $language === 'pt' 
                        ? 'Código inválido. Tente novamente.' 
                        : 'Invalid code. Try again.',
                    'error_code' => 'CODE_INVALID'
                ]);
            }
            exit;
        }
        
        // Verificar número de tentativas (máximo 5)
        if ($verification['tentativas'] >= 5) {
            // Invalidar código
            $stmt = $conn->prepare("
                UPDATE codigos_verificacao_telefone 
                SET usado = 1 
                WHERE id = ?
            ");
            $stmt->execute([$verification['id']]);
            
            echo json_encode([
                'success' => false,
                'message' => $language === 'pt' 
                    ? 'Número máximo de tentativas excedido. Solicite um novo código.' 
                    : 'Maximum attempts exceeded. Request a new code.',
                'error_code' => 'MAX_ATTEMPTS'
            ]);
            exit;
        }
        
        // Código válido! Atualizar telefone do usuário
        $newPhone = $verification['telefone_novo'];
        $newCountryCode = $verification['codigo_pais_novo'];
        
        // Iniciar transação
        $conn->beginTransaction();
        
        try {
            // Atualizar telefone do usuário
            $stmt = $conn->prepare("
                UPDATE usuarios 
                SET telefone = ?, 
                    codigo_pais = ?,
                    telefone_verificado = 1,
                    atualizado_em = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$newPhone, $newCountryCode, $userId]);
            
            // Marcar código como usado
            $stmt = $conn->prepare("
                UPDATE codigos_verificacao_telefone 
                SET usado = 1, 
                    usado_em = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$verification['id']]);
            
            // Log de alteração bem-sucedida
            $stmt = $conn->prepare("
                INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip) 
                VALUES (?, 'phone_changed', ?, ?)
            ");
            $stmt->execute([
                $userId,
                "Telefone alterado para $newCountryCode $newPhone",
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => $language === 'pt' 
                    ? 'Telefone atualizado com sucesso!' 
                    : 'Phone updated successfully!',
                'newPhone' => $newCountryCode . ' ' . $newPhone
            ]);
            
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
        
    } catch (PDOException $e) {
        error_log("Erro no verify-phone-change: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $language === 'pt' 
                ? 'Erro ao processar verificação.' 
                : 'Error processing verification.'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
}
