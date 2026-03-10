<?php
/**
 * API para alteração de senha do usuário
 * 
 * Recebe: currentPassword, newPassword
 * Retorna: JSON com success e message
 */

// Habilitar exibição de erros para debug (REMOVER EM PRODUÇÃO)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir conexão
$conexao_encontrada = false;
$caminhos_conexao = [
    __DIR__ . '/../conexao/conexao.php',
    __DIR__ . '/../../conexao/conexao.php',
    $_SERVER['DOCUMENT_ROOT'] . '/conexao/conexao.php',
    dirname(__DIR__) . '/conexao/conexao.php',
];

foreach ($caminhos_conexao as $caminho) {
    if (file_exists($caminho)) {
        require_once $caminho;
        $conexao_encontrada = true;
        error_log("Conexão encontrada em: $caminho");
        break;
    }
}

if (!$conexao_encontrada) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao conectar com o banco de dados',
        'debug' => 'Arquivo conexao.php não encontrado'
    ]);
    exit;
}

// Verificar se o método é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido'
    ]);
    exit;
}

// Verificar se está logado
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Usuário não autenticado'
    ]);
    exit;
}

// Obter dados do POST
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['currentPassword']) || !isset($data['newPassword'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Dados incompletos'
    ]);
    exit;
}

$currentPassword = $data['currentPassword'];
$newPassword = $data['newPassword'];
$userId = $_SESSION['user_id'];

// Validar nova senha
if (strlen($newPassword) < 8) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'A nova senha deve ter pelo menos 8 caracteres'
    ]);
    exit;
}

// Validar requisitos da senha
if (!preg_match('/[A-Z]/', $newPassword) || 
    !preg_match('/[a-z]/', $newPassword) || 
    !preg_match('/[0-9]/', $newPassword)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'A senha deve conter pelo menos uma letra maiúscula, uma minúscula e um número'
    ]);
    exit;
}

try {
    // Verificar se a função getConnection existe
    if (!function_exists('getConnection')) {
        throw new Exception('Função getConnection() não encontrada');
    }
    
    $pdo = getConnection();
    
    if (!$pdo) {
        throw new Exception('Falha ao obter conexão PDO');
    }
    
    // Buscar senha atual do usuário
    $stmt = $pdo->prepare("SELECT senha_hash FROM usuarios WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Usuário não encontrado'
        ]);
        exit;
    }
    
    // Verificar senha atual
    if (!password_verify($currentPassword, $user['senha_hash'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Senha atual incorreta'
        ]);
        exit;
    }
    
    // Hash da nova senha
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Atualizar senha no banco
    $stmt = $pdo->prepare("UPDATE usuarios SET senha_hash = ?, data_atualizacao = NOW() WHERE id = ?");
    $success = $stmt->execute([$hashedPassword, $userId]);
    
    if (!$success) {
        throw new Exception('Falha ao executar UPDATE');
    }
    
    // Log da alteração (opcional)
    error_log("Senha alterada com sucesso para o usuário ID: $userId");
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Senha alterada com sucesso!'
    ]);
    
} catch (PDOException $e) {
    error_log('Erro PDO ao alterar senha: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar solicitação',
        'debug' => $e->getMessage(),
        'error_type' => 'PDOException'
    ]);
} catch (Exception $e) {
    error_log('Erro ao alterar senha: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar solicitação',
        'debug' => $e->getMessage(),
        'error_type' => 'Exception'
    ]);
}
