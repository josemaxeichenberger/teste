<?php
/**
 * api/cobrar-cartao.php
 * POST: processa cobrança de cartão de crédito via EFI Pay
 *
 * Body (multipart/form-data):
 *   token          — checkout_token da sessão (CSRF)
 *   payment_token  — token gerado pelo JS EFI Pay no browser
 *   qty            — quantidade de tickets (1–100)
 *   installments   — parcelas (1–12)
 *   name           — nome do titular
 *   cpf            — CPF (só dígitos ou formatado)
 *   phone          — telefone (só dígitos ou formatado)
 *   birth          — data de nascimento (DD/MM/AAAA → convertido para AAAA-MM-DD)
 *   zipcode        — CEP (só dígitos)
 *   street         — rua
 *   number         — número
 *   neighborhood   — bairro
 *   city           — cidade
 *   state          — UF (2 letras)
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ----------- Autenticação de sessão -----------
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit;
}

// ----------- CSRF -----------
$token = $_POST['token'] ?? '';
if (
    empty($_SESSION['checkout_token']) ||
    !hash_equals((string) $_SESSION['checkout_token'], (string) $token)
) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token inválido']);
    exit;
}

// ----------- Inputs -----------
$payment_token = trim($_POST['payment_token'] ?? '');
$qty           = max(1, min(100, intval($_POST['qty']          ?? 1)));
$installments  = max(1, min(12,  intval($_POST['installments'] ?? 1)));
$name          = trim($_POST['name']         ?? '');
$cpf           = preg_replace('/\D/', '', $_POST['cpf']   ?? '');
$phone         = preg_replace('/\D/', '', $_POST['phone'] ?? '');
$birth_raw     = trim($_POST['birth'] ?? ''); // AAAA-MM-DD (já convertido pelo JS)
$zipcode       = preg_replace('/\D/', '', $_POST['zipcode']      ?? '');
$street        = trim($_POST['street']       ?? '');
$number        = trim($_POST['number']       ?? '');
$neighborhood  = trim($_POST['neighborhood'] ?? '');
$city          = trim($_POST['city']         ?? '');
$state         = strtoupper(preg_replace('/[^A-Za-z]/', '', $_POST['state'] ?? ''));

// ----------- Validações -----------
if (empty($payment_token)) {
    echo json_encode(['success' => false, 'error' => 'Token de pagamento ausente']); exit;
}
if (strlen($cpf) !== 11) {
    echo json_encode(['success' => false, 'error' => 'CPF inválido — informe 11 dígitos']); exit;
}
if (empty($name)) {
    echo json_encode(['success' => false, 'error' => 'Nome do titular é obrigatório']); exit;
}
if (strlen($phone) < 10) {
    echo json_encode(['success' => false, 'error' => 'Telefone inválido']); exit;
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth_raw)) {
    echo json_encode(['success' => false, 'error' => 'Data de nascimento inválida']); exit;
}
if (strlen($zipcode) !== 8) {
    echo json_encode(['success' => false, 'error' => 'CEP inválido']); exit;
}
if (empty($street) || empty($number) || empty($neighborhood) || empty($city) || strlen($state) !== 2) {
    echo json_encode(['success' => false, 'error' => 'Endereço de cobrança incompleto']); exit;
}

$userId    = (int) ($_SESSION['user_id']    ?? 0);
$userEmail = (string) ($_SESSION['user_email'] ?? '');

// ----------- EFI Pay config -----------
// A API de cartão usa api.efipay.com.br — sem certificado mTLS (diferente do PIX)
$client_id     = 'Client_Id_9763bd4bc932898e50ee520c0d315af0d98bdf93';
$client_secret = 'Client_Secret_8b833bc6ef75e461292b1fbed5e8205c6f9f091d';
$base_url      = 'https://cobrancas.api.efipay.com.br';

// ----------- Autenticar -----------
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $base_url . '/v1/authorize',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode(['grant_type' => 'client_credentials']),
    CURLOPT_HTTPHEADER     => [
        'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret),
        'Content-Type: application/json',
    ],
    CURLOPT_SSL_VERIFYPEER  => false,
    CURLOPT_SSL_VERIFYHOST  => false,
    CURLOPT_FOLLOWLOCATION  => true,
    CURLOPT_MAXREDIRS       => 5,
]);
$raw  = curl_exec($ch);
$err  = curl_error($ch);
curl_close($ch);
$auth = json_decode($raw, true);

if (empty($auth['access_token'])) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Falha na autenticação com o gateway de pagamento']);
    exit;
}
$access_token = $auth['access_token'];

// ----------- Helper cURL (sem certificado — API de cartão não usa mTLS) -----------
function efiCardCall(string $url, string $method, string $accessToken, ?array $body = null): array
{
    $ch   = curl_init();
    $opts = [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ];
    if ($method === 'POST') {
        $opts[CURLOPT_POST]       = true;
        $opts[CURLOPT_POSTFIELDS] = json_encode($body);
    }
    curl_setopt_array($ch, $opts);
    $resp = curl_exec($ch);
    if (curl_errno($ch)) {
        $curlErr = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Erro de conexão: ' . $curlErr);
    }
    curl_close($ch);
    return json_decode($resp, true) ?? [];
}

try {
    // 1. Criar cobrança
    $chargeResp = efiCardCall(
        $base_url . '/v1/charge',
        'POST',
        $access_token,
        [
            'items' => [[
                'name'  => 'Entry Ticket',
                'value' => 2500 * $qty,   // R$25,00 × quantidade, em centavos
            ]],
        ]
    );

    if (empty($chargeResp['data']['charge_id'])) {
        $msg = $chargeResp['message'] ?? ($chargeResp['data']['message'] ?? 'Falha ao criar cobrança');
        throw new RuntimeException($msg . ' | RAW: ' . json_encode($chargeResp));
    }

    $charge_id = (int) $chargeResp['data']['charge_id'];

    // 2. Pagar com cartão de crédito
    $payResp = efiCardCall(
        $base_url . '/v1/charge/' . $charge_id . '/pay',
        'POST',
        $access_token,
        [
            'payment' => [
                'credit_card' => [
                    'customer' => [
                        'name'         => $name,
                        'cpf'          => $cpf,
                        'email'        => $userEmail,
                        'phone_number' => $phone,
                        'birth'        => $birth_raw,
                    ],
                    'billing_address' => [
                        'street'       => $street,
                        'number'       => $number,
                        'neighborhood' => $neighborhood,
                        'zipcode'      => $zipcode,
                        'city'         => $city,
                        'state'        => $state,
                    ],
                    'installments'  => $installments,
                    'payment_token' => $payment_token,
                    'message'       => 'Compra de ' . $qty . ' ticket' . ($qty > 1 ? 's' : '') . ' — Poker111',
                ],
            ],
        ]
    );

    $payCode   = $payResp['code']         ?? null;
    $payData   = $payResp['data']         ?? [];
    $payStatus = $payData['status']       ?? ($payData['payment']['status'] ?? null);

    if ($payCode !== 200 || !in_array($payStatus, ['approved', 'paid'], true)) {
        $reason = $payData['reason'] ?? ($payResp['message'] ?? ($payData['message'] ?? 'Pagamento não autorizado'));
        throw new RuntimeException($reason . ' | RAW_PAY: ' . json_encode($payResp));
    }

    // 3. Inserir tickets no BD
    require_once __DIR__ . '/../conexao/conexao.php';

    $pdo = new PDO(
        'mysql:host=' . POKER_DB_HOST . ';dbname=' . POKER_DB_NAME . ';charset=' . POKER_DB_CHARSET,
        POKER_DB_USER,
        POKER_DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $origem = 'compra_cartao_' . $charge_id;

    $dup = $pdo->prepare('SELECT COUNT(*) FROM tickets WHERE origem = ?');
    $dup->execute([$origem]);

    if ((int) $dup->fetchColumn() === 0) {
        $ins = $pdo->prepare(
            'INSERT INTO tickets (usuario_id, tipo, descricao, origem, status, data_obtencao)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );
        for ($i = 0; $i < $qty; $i++) {
            $ins->execute([
                $userId,
                'challenge_entry',
                'Ticket comprado via Cartão de Crédito',
                $origem,
                'disponivel',
            ]);
        }
    }

    echo json_encode([
        'success'   => true,
        'charge_id' => $charge_id,
        'qty'       => $qty,
        'status'    => $payStatus,
    ]);

} catch (RuntimeException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
