<?php

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$client_id     = "Client_Id_9763bd4bc932898e50ee520c0d315af0d98bdf93";
$client_secret = "Client_Secret_8b833bc6ef75e461292b1fbed5e8205c6f9f091d";
$base_url      = "https://pix.api.efipay.com.br";
$certificado   = realpath(__DIR__ . "/../producao.pfx") ?: (dirname(__DIR__) . DIRECTORY_SEPARATOR . "producao.pfx");

$txid = isset($_GET['txid']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['txid']) : '';

if (empty($txid)) {
    echo json_encode(['status' => 'ERRO', 'mensagem' => 'txid invalido']);
    exit;
}

if (!file_exists($certificado)) {
    echo json_encode(['status' => 'ERRO', 'mensagem' => 'certificado nao encontrado']);
    exit;
}

// Autenticação EFI Pay
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $base_url . "/oauth/token",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query(["grant_type" => "client_credentials"]),
    CURLOPT_HTTPHEADER     => [
        "Authorization: Basic " . base64_encode($client_id . ":" . $client_secret),
        "Content-Type: application/x-www-form-urlencoded"
    ],
    CURLOPT_SSLCERT        => $certificado,
    CURLOPT_SSLCERTTYPE    => "P12",
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);
$auth = json_decode(curl_exec($ch), true);
curl_close($ch);

if (empty($auth["access_token"])) {
    echo json_encode(['status' => 'ERRO', 'mensagem' => 'falha na autenticacao']);
    exit;
}

$token = $auth["access_token"];

// Consultar cobrança
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $base_url . "/v2/cob/" . $txid,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        "Authorization: Bearer " . $token,
        "Content-Type: application/json"
    ],
    CURLOPT_SSLCERT        => $certificado,
    CURLOPT_SSLCERTTYPE    => "P12",
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);
$cob = json_decode(curl_exec($ch), true);
curl_close($ch);

$status = $cob['status'] ?? 'DESCONHECIDO';

// Se pagamento confirmado, inserir tickets no banco
if ($status === 'CONCLUIDA') {
    require_once __DIR__ . '/../conexao/conexao.php';

    try {
        $pdo = new PDO(
            'mysql:host=' . POKER_DB_HOST . ';dbname=' . POKER_DB_NAME . ';charset=' . POKER_DB_CHARSET,
            POKER_DB_USER,
            POKER_DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Verificar se este txid já foi processado (evita duplicatas)
        $check = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE origem = ?");
        $check->execute(['compra_pix_' . $txid]);
        $jaProcessado = (int) $check->fetchColumn() > 0;

        if (!$jaProcessado) {
            // Recuperar usuário e quantidade da sessão ou da cobrança
            $userId   = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
            $valor    = isset($cob['valor']['original']) ? floatval($cob['valor']['original']) : 0;
            $preco    = 25.00;
            $qty      = ($valor > 0 && $preco > 0) ? max(1, (int) round($valor / $preco)) : 1;

            if ($userId) {
                $stmt = $pdo->prepare(
                    "INSERT INTO tickets 
                        (usuario_id, tipo, descricao, origem, status, data_obtencao)
                     VALUES 
                        (:uid, 'challenge_entry', 'Ticket comprado via PIX', :origem, 'disponivel', NOW())"
                );

                for ($i = 0; $i < $qty; $i++) {
                    $stmt->execute([
                        ':uid'    => $userId,
                        ':origem' => 'compra_pix_' . $txid,
                    ]);
                }

                echo json_encode([
                    'status'          => $status,
                    'tickets_inseridos' => $qty,
                ]);
                exit;
            }
        }
    } catch (PDOException $e) {
        // Erro no banco não bloqueia a resposta de status
        error_log('Erro ao inserir tickets PIX: ' . $e->getMessage());
    }
}

echo json_encode(['status' => $status]);


$txid = isset($_GET['txid']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['txid']) : '';

if (empty($txid)) {
    echo json_encode(['status' => 'ERRO', 'mensagem' => 'txid invalido']);
    exit;
}

if (!file_exists($certificado)) {
    echo json_encode(['status' => 'ERRO', 'mensagem' => 'certificado nao encontrado']);
    exit;
}

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $base_url . "/oauth/token",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query(["grant_type" => "client_credentials"]),
    CURLOPT_HTTPHEADER     => [
        "Authorization: Basic " . base64_encode($client_id . ":" . $client_secret),
        "Content-Type: application/x-www-form-urlencoded"
    ],
    CURLOPT_SSLCERT        => $certificado,
    CURLOPT_SSLCERTTYPE    => "P12",
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);
$auth = json_decode(curl_exec($ch), true);
curl_close($ch);

if (empty($auth["access_token"])) {
    echo json_encode(['status' => 'ERRO', 'mensagem' => 'falha na autenticacao']);
    exit;
}

$token = $auth["access_token"];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $base_url . "/v2/cob/" . $txid,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        "Authorization: Bearer " . $token,
        "Content-Type: application/json"
    ],
    CURLOPT_SSLCERT        => $certificado,
    CURLOPT_SSLCERTTYPE    => "P12",
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);
$cob = json_decode(curl_exec($ch), true);
curl_close($ch);

echo json_encode([
    'status' => $cob['status'] ?? 'DESCONHECIDO'
]);