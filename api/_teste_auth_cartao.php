<?php
header('Content-Type: application/json; charset=utf-8');

$client_id     = 'Client_Id_9763bd4bc932898e50ee520c0d315af0d98bdf93';
$client_secret = 'Client_Secret_8b833bc6ef75e461292b1fbed5e8205c6f9f091d';
$base_url      = 'https://cobrancas.api.efipay.com.br';

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
$raw    = curl_exec($ch);
$err    = curl_error($ch);
$http   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo json_encode([
    'http_code'     => $http,
    'curl_error'    => $err,
    'response'      => json_decode($raw, true) ?? $raw,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
