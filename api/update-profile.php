<?php
/**
 * API de Atualização de Perfil
 * @author GustavoChaconDeveloper
 * @description API modular para atualizar dados do perfil do usuário
 * 
 * CAMINHOS DE IMAGENS:
 * - Avatares: wp-content/themes/teste/imagens/avatares/avatar1.webp
 * - Formato: WebP (avatar1.webp até avatar12.webp)
 * - O sistema busca em múltiplos caminhos para garantir compatibilidade
 */

// ============================================================================
// CONFIGURAÇÕES INICIAIS
// ============================================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../conexao/conexao.php';
require_once '../helpers/EmailSMTP.php';

// ============================================================================
// VERIFICAR AUTENTICAÇÃO
// ============================================================================
function verificarAutenticacao() {
    $is_logged_in = isset($_SESSION['logged_in']) && ($_SESSION['logged_in'] === true || $_SESSION['logged_in'] == 1 || $_SESSION['logged_in'] === '1');
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
// FUNÇÃO: ATUALIZAR NICKNAME
// ============================================================================
function atualizarNickname($userId, $nickname) {
    try {
        // Validações
        if (empty($nickname)) {
            return [
                'success' => false,
                'message' => 'O nickname não pode estar vazio.'
            ];
        }
        
        $nickname = trim($nickname);
        
        if (strlen($nickname) < 3) {
            return [
                'success' => false,
                'message' => 'O nickname deve ter pelo menos 3 caracteres.'
            ];
        }
        
        if (strlen($nickname) > 20) {
            return [
                'success' => false,
                'message' => 'O nickname deve ter no máximo 20 caracteres.'
            ];
        }
        
        // Verificar se contém apenas caracteres permitidos (letras, números, underscore)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $nickname)) {
            return [
                'success' => false,
                'message' => 'O nickname só pode conter letras, números e underscore (_).'
            ];
        }
        
        $conn = getConnection();
        
        // Verificar se o nickname já está sendo usado por outro usuário
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nickname = ? AND id != ?");
        $stmt->execute([$nickname, $userId]);
        
        if ($stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Este nickname já está sendo usado. Por favor, escolha outro.'
            ];
        }
        
        // Atualizar o nickname
        $stmt = $conn->prepare("UPDATE usuarios SET nickname = ?, data_atualizacao = NOW() WHERE id = ?");
        $stmt->execute([$nickname, $userId]);
        
        // Log da atividade
        $stmt = $conn->prepare("
            INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip, user_agent) 
            VALUES (?, 'update_nickname', ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            "Nickname alterado para: {$nickname}",
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        return [
            'success' => true,
            'message' => 'Nickname atualizado com sucesso!',
            'data' => [
                'nickname' => $nickname
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao atualizar nickname: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erro ao atualizar o nickname. Por favor, tente novamente.'
        ];
    }
}

// ============================================================================
// FUNÇÃO: ATUALIZAR AVATAR
// ============================================================================
function atualizarAvatar($userId, $avatar) {
    try {
        // Log para debug
        error_log("=== DEBUG AVATAR ===");
        error_log("Avatar recebido: " . $avatar);
        error_log("Tipo: " . gettype($avatar));
        
        // Validações
        if (empty($avatar)) {
            return [
                'success' => false,
                'message' => 'Nenhum avatar foi selecionado.'
            ];
        }
        
        // Verificar formato do nome do arquivo (avatar1.webp até avatar12.webp)
        if (!preg_match('/^avatar([1-9]|1[0-2])\.webp$/', $avatar)) {
            error_log("REGEX FALHOU para: " . $avatar);
            return [
                'success' => false,
                'message' => 'Formato de avatar inválido. Esperado: avatar1.webp até avatar12.webp'
            ];
        }
        
        error_log("REGEX OK!");
        
        // Verificar se o arquivo de avatar existe no tema
        // Caminho relativo: wp-content/themes/teste/imagens/avatares/avatar1.webp
        $possiblePaths = [
            $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/teste/imagens/avatares/' . $avatar,
            dirname(__DIR__) . '/wp-content/themes/teste/imagens/avatares/' . $avatar,
            __DIR__ . '/../wp-content/themes/teste/imagens/avatares/' . $avatar,
        ];
        
        error_log("Verificando caminhos:");
        $avatarExists = false;
        foreach ($possiblePaths as $path) {
            error_log("  - " . $path . " => " . (file_exists($path) ? "EXISTE" : "NÃO EXISTE"));
            if (file_exists($path)) {
                $avatarExists = true;
                break;
            }
        }
        
        if (!$avatarExists) {
            return [
                'success' => false,
                'message' => 'Avatar não encontrado no tema. Verifique se o arquivo existe: wp-content/themes/teste/imagens/avatares/' . $avatar
            ];
        }
        
        error_log("Avatar existe! Atualizando banco...");
        
        $conn = getConnection();
        
        // Atualizar o avatar
        $stmt = $conn->prepare("UPDATE usuarios SET url_avatar = ?, data_atualizacao = NOW() WHERE id = ?");
        $stmt->execute([$avatar, $userId]);
        
        // Log da atividade
        $stmt = $conn->prepare("
            INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip, user_agent) 
            VALUES (?, 'update_avatar', ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            "Avatar alterado para: {$avatar}",
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        return [
            'success' => true,
            'message' => 'Avatar atualizado com sucesso!',
            'data' => [
                'avatar' => $avatar
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao atualizar avatar: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erro ao atualizar o avatar. Por favor, tente novamente.'
        ];
    }
}

// ============================================================================
// FUNÇÃO: ATUALIZAR TELEFONE
// ============================================================================
function atualizarTelefone($userId, $codigoPais, $telefone) {
    try {
        // Validações
        if (empty($codigoPais) || empty($telefone)) {
            return [
                'success' => false,
                'message' => 'Código do país e telefone são obrigatórios.'
            ];
        }
        
        // Limpar o telefone (remover espaços e caracteres especiais)
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        
        if (strlen($telefone) < 8 || strlen($telefone) > 15) {
            return [
                'success' => false,
                'message' => 'Número de telefone inválido.'
            ];
        }
        
        // Validar código do país
        if (!preg_match('/^\+\d{1,4}$/', $codigoPais)) {
            return [
                'success' => false,
                'message' => 'Código do país inválido.'
            ];
        }
        
        $conn = getConnection();
        
        // Verificar se o telefone já está sendo usado por outro usuário
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE codigo_pais = ? AND telefone = ? AND id != ?");
        $stmt->execute([$codigoPais, $telefone, $userId]);
        
        if ($stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Este telefone já está cadastrado em outra conta.'
            ];
        }
        
        // Atualizar o telefone (marcar como não verificado)
        $stmt = $conn->prepare("
            UPDATE usuarios 
            SET codigo_pais = ?, 
                telefone = ?, 
                telefone_verificado = 0,
                data_atualizacao = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$codigoPais, $telefone, $userId]);
        
        // Log da atividade
        $stmt = $conn->prepare("
            INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip, user_agent) 
            VALUES (?, 'update_phone', ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            "Telefone alterado para: {$codigoPais} {$telefone}",
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        return [
            'success' => true,
            'message' => 'Telefone atualizado! Um código de verificação foi enviado.',
            'requiresVerification' => true,
            'data' => [
                'codigoPais' => $codigoPais,
                'telefone' => $telefone
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao atualizar telefone: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erro ao atualizar o telefone. Por favor, tente novamente.'
        ];
    }
}

// ============================================================================
// FUNÇÃO: GARANTIR TABELA DE SOLICITAÇÕES
// ============================================================================
function garantirTabelaSolicitacoes($conn) {
    $sql = "
        CREATE TABLE IF NOT EXISTS solicitacoes_alteracao (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            tipo_alteracao VARCHAR(20) NOT NULL DEFAULT 'both',
            nome_completo_novo VARCHAR(150) NULL,
            email_novo VARCHAR(190) NULL,
            mensagem TEXT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pendente',
            data_solicitacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            endereco_ip VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            INDEX idx_usuario_id (usuario_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";

    $conn->exec($sql);

    $columnCheck = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'solicitacoes_alteracao' AND COLUMN_NAME = 'tipo_alteracao'");
    $columnCheck->execute();
    if ((int)$columnCheck->fetchColumn() === 0) {
        $conn->exec("ALTER TABLE solicitacoes_alteracao ADD COLUMN tipo_alteracao VARCHAR(20) NOT NULL DEFAULT 'both' AFTER usuario_id");
    }
}

// ============================================================================
// FUNÇÃO: SOLICITAR ALTERAÇÃO DE PERFIL (NOME/EMAIL)
// ============================================================================
function solicitarAlteracaoPerfil($userId, $data) {
    try {
        $novoNome = trim($data['newFullName'] ?? '');
        $novoEmail = trim($data['newEmail'] ?? '');
        $mensagem = trim($data['message'] ?? '');
        $idioma = $data['language'] ?? 'pt';

        if ($novoEmail !== '' && !filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Email inválido.'
            ];
        }

        if ($novoNome !== '' && strlen($novoNome) < 3) {
            return [
                'success' => false,
                'message' => 'O nome completo deve ter pelo menos 3 caracteres.'
            ];
        }

        if ($novoNome === '' && $novoEmail === '') {
            return [
                'success' => false,
                'message' => 'Informe o novo nome completo e/ou o novo email.'
            ];
        }

        $conn = getConnection();
        garantirTabelaSolicitacoes($conn);

        $hasName = $novoNome !== '';
        $hasEmail = $novoEmail !== '';
        if ($hasName && $hasEmail) {
            $tipoAlteracao = 'both';
        } elseif ($hasName) {
            $tipoAlteracao = 'name';
        } else {
            $tipoAlteracao = 'email';
        }

        // Bloquear nova solicitação se já existir pendente do mesmo tipo
        if ($tipoAlteracao === 'name') {
            $stmt = $conn->prepare("SELECT id FROM solicitacoes_alteracao WHERE usuario_id = ? AND status = 'pendente' AND tipo_alteracao IN ('name','both') LIMIT 1");
            $stmt->execute([$userId]);
        } elseif ($tipoAlteracao === 'email') {
            $stmt = $conn->prepare("SELECT id FROM solicitacoes_alteracao WHERE usuario_id = ? AND status = 'pendente' AND tipo_alteracao IN ('email','both') LIMIT 1");
            $stmt->execute([$userId]);
        } else {
            $stmt = $conn->prepare("SELECT id FROM solicitacoes_alteracao WHERE usuario_id = ? AND status = 'pendente' LIMIT 1");
            $stmt->execute([$userId]);
        }
        if ($stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Você já possui uma solicitação pendente. Aguarde a análise da equipe.'
            ];
        }

        // Buscar dados atuais do usuário
        $stmt = $conn->prepare("SELECT email, primeiro_nome, sobrenome FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            return [
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ];
        }

        $nomeAtual = trim($usuario['primeiro_nome'] . ' ' . $usuario['sobrenome']);
        $emailAtual = $usuario['email'];

        // Registrar solicitação
        $stmt = $conn->prepare("
            INSERT INTO solicitacoes_alteracao
                (usuario_id, tipo_alteracao, nome_completo_novo, email_novo, mensagem, status, endereco_ip, user_agent)
            VALUES
                (?, ?, ?, ?, ?, 'pendente', ?, ?)
        ");
        $stmt->execute([
            $userId,
            $tipoAlteracao,
            $novoNome !== '' ? $novoNome : null,
            $novoEmail !== '' ? $novoEmail : null,
            $mensagem !== '' ? $mensagem : null,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);

        // Log da atividade
        $stmt = $conn->prepare("
            INSERT INTO logs_atividades (usuario_id, acao, descricao, endereco_ip, user_agent)
            VALUES (?, 'request_profile_change', ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            'Solicitação de alteração de dados enviada (status pendente)',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);

        // Enviar email de confirmação
        $emailHelper = new EmailSMTP();
        $emailPayload = [
            'fullName' => $nomeAtual,
            'newFullName' => $novoNome,
            'currentEmail' => $emailAtual,
            'newEmail' => $novoEmail,
            'message' => $mensagem
        ];
        $emailResult = $emailHelper->sendProfileChangeRequest($emailAtual, $emailPayload, $idioma);

        if (empty($emailResult['success'])) {
            return [
                'success' => false,
                'message' => 'Solicitação registrada, mas houve erro ao enviar o email. Tente novamente.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Solicitação enviada! Status: pendente.'
        ];

    } catch (Exception $e) {
        error_log("Erro ao solicitar alteração de perfil: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erro ao enviar a solicitação. Por favor, tente novamente.'
        ];
    }
}

// ============================================================================
// FUNÇÃO: OBTER STATUS DA SOLICITAÇÃO
// ============================================================================
function obterStatusSolicitacao($userId, $type = 'all') {
    try {
        $conn = getConnection();
        garantirTabelaSolicitacoes($conn);

        if ($type === 'name') {
            $stmt = $conn->prepare("SELECT status FROM solicitacoes_alteracao WHERE usuario_id = ? AND tipo_alteracao IN ('name','both') AND status = 'pendente' ORDER BY data_solicitacao DESC LIMIT 1");
            $stmt->execute([$userId]);
        } elseif ($type === 'email') {
            $stmt = $conn->prepare("SELECT status FROM solicitacoes_alteracao WHERE usuario_id = ? AND tipo_alteracao IN ('email','both') AND status = 'pendente' ORDER BY data_solicitacao DESC LIMIT 1");
            $stmt->execute([$userId]);
        } else {
            $stmt = $conn->prepare("SELECT status FROM solicitacoes_alteracao WHERE usuario_id = ? AND status = 'pendente' ORDER BY data_solicitacao DESC LIMIT 1");
            $stmt->execute([$userId]);
        }
        $row = $stmt->fetch();

        if ($row) {
            return [
                'success' => true,
                'status' => $row['status']
            ];
        }

        return [
            'success' => true,
            'status' => 'none'
        ];

    } catch (Exception $e) {
        error_log("Erro ao obter status da solicitação: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erro ao consultar status.'
        ];
    }
}

// ============================================================================
// PROCESSAR REQUISIÇÃO
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar autenticação
    $userId = verificarAutenticacao();
    
    // Obter dados da requisição
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Verificar tipo de atualização
    if (!isset($data['action'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Ação não especificada.'
        ]);
        exit;
    }
    
    $action = $data['action'];
    $response = null;
    
    // Executar ação apropriada
    switch ($action) {
        case 'update_nickname':
            if (!isset($data['nickname'])) {
                $response = [
                    'success' => false,
                    'message' => 'Nickname não fornecido.'
                ];
            } else {
                $response = atualizarNickname($userId, $data['nickname']);
            }
            break;
            
        case 'update_avatar':
            if (!isset($data['avatar'])) {
                $response = [
                    'success' => false,
                    'message' => 'Avatar não fornecido.'
                ];
            } else {
                $response = atualizarAvatar($userId, $data['avatar']);
            }
            break;
            
        case 'update_phone':
            if (!isset($data['codigoPais']) || !isset($data['telefone'])) {
                $response = [
                    'success' => false,
                    'message' => 'Dados do telefone incompletos.'
                ];
            } else {
                $response = atualizarTelefone($userId, $data['codigoPais'], $data['telefone']);
            }
            break;

        case 'request_profile_change':
            $response = solicitarAlteracaoPerfil($userId, $data);
            break;

        case 'get_profile_change_status':
            $response = obterStatusSolicitacao($userId, $data['type'] ?? 'all');
            break;
            
        default:
            $response = [
                'success' => false,
                'message' => 'Ação inválida.'
            ];
            break;
    }
    
    echo json_encode($response);
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
}
