<?php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Valida se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit;
}

// Somente POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

// Valida o token de checkout contra a sessão (proteção CSRF)
$token        = isset($_POST['token']) ? $_POST['token'] : '';
$sessionToken = isset($_SESSION['checkout_token']) ? $_SESSION['checkout_token'] : '';

if (
    empty($token) ||
    empty($sessionToken) ||
    $token !== $sessionToken ||
    !preg_match('/^[a-f0-9]{16}$/', $token)
) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Token inválido']);
    exit;
}

// Valida quantidade
$qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
if ($qty < 1 || $qty > 100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Quantidade inválida']);
    exit;
}

$pricePerTicket = 25.00;
$total          = $qty * $pricePerTicket;

// Credenciais EFI Pay
$client_id     = "Client_Id_9763bd4bc932898e50ee520c0d315af0d98bdf93";
$client_secret = "Client_Secret_8b833bc6ef75e461292b1fbed5e8205c6f9f091d";
$base_url      = "https://pix.api.efipay.com.br";
$certificado   = realpath(__DIR__ . "/../producao.pfx") ?: (dirname(__DIR__) . DIRECTORY_SEPARATOR . "producao.pfx");

if (!file_exists($certificado)) {
    echo json_encode(['success' => false, 'error' => 'Certificado não encontrado: ' . $certificado]);
    exit;
}

// Função auxiliar para chamadas autenticadas
function chamarApiPix($url, $method, $token, $certificado, $body = null) {
    $ch   = curl_init();
    $opts = [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer " . $token,
            "Content-Type: application/json"
        ],
        CURLOPT_SSLCERT        => $certificado,
        CURLOPT_SSLCERTTYPE    => "P12",
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ];
    if ($method === "POST") {
        $opts[CURLOPT_POST]       = true;
        $opts[CURLOPT_POSTFIELDS] = json_encode($body);
    }
    curl_setopt_array($ch, $opts);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return null;
    }
    curl_close($ch);
    return json_decode($response, true);
}

// 1. Autenticação
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
    echo json_encode(['success' => false, 'error' => 'Falha na autenticação do gateway de pagamento']);
    exit;
}
$accessToken = $auth["access_token"];

// 2. Criar cobrança PIX
$descricao = "Poker111 - " . $qty . " ticket" . ($qty > 1 ? 's' : '') . " de entrada";

$cobranca = [
    "calendario" => [
        "expiracao" => 3600
    ],
    "devedor" => [
        "cpf"  => "12345678909",
        "nome" => isset($_SESSION['user_name']) ? substr(strip_tags($_SESSION['user_name']), 0, 40) : "Cliente"
    ],
    "valor" => [
        "original" => number_format($total, 2, '.', '')
        // "original" => "0.01" se caso quiser realizar um teste
    ],
    "chave"              => "468d32e4-9c2b-475f-a553-8995878838e0",
    "solicitacaoPagador" => $descricao
];

$cob = chamarApiPix($base_url . "/v2/cob", "POST", $accessToken, $certificado, $cobranca);

if (empty($cob["loc"]["id"])) {
    echo json_encode(['success' => false, 'error' => 'Falha ao criar cobrança PIX']);
    exit;
}

$locId = $cob["loc"]["id"];
$txid  = $cob["txid"];

// 3. Buscar QR Code e Pix Copia e Cola
$qrcode = chamarApiPix($base_url . "/v2/loc/" . $locId . "/qrcode", "GET", $accessToken, $certificado);

if (empty($qrcode["qrcode"])) {
    echo json_encode(['success' => false, 'error' => 'Falha ao gerar QR Code PIX']);
    exit;
}

echo json_encode([
    'success'        => true,
    'txid'           => $txid,
    'qrcode_img'     => $qrcode["imagemQrcode"] ?? '',
    'pix_copia_cola' => $qrcode["qrcode"],
    'valor'          => number_format($total, 2, ',', '.'),
    'expiracao'      => 3600
]);
