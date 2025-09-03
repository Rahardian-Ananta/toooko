<?php
require __DIR__ . '/../config.php';

// Buat log biar tahu callback masuk atau tidak
$logFile = __DIR__ . '/../storage/callback_log.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - RAW: " . json_encode($_POST) . PHP_EOL, FILE_APPEND);

// Ambil data dari callback Duitku
$merchantCode     = $_POST['merchantCode']     ?? null;
$amount           = $_POST['amount']           ?? null;
$merchantOrderId  = $_POST['merchantOrderId']  ?? null;
$productDetail    = $_POST['productDetail']    ?? null;
$additionalParam  = $_POST['additionalParam']  ?? null;
$paymentCode      = $_POST['paymentCode']      ?? null;
$resultCode       = $_POST['resultCode']       ?? null;
$merchantUserId   = $_POST['merchantUserId']   ?? null;
$reference        = $_POST['reference']        ?? null;
$signature        = $_POST['signature']        ?? null;
$publisherOrderId = $_POST['publisherOrderId'] ?? null;
$spUserHash       = $_POST['spUserHash']       ?? null;
$settlementDate   = $_POST['settlementDate']   ?? null;
$issuerCode       = $_POST['issuerCode']       ?? null;

// Basic validation
if (!$merchantCode || !$amount || !$merchantOrderId || !$signature) {
    http_response_code(400);
    exit('Invalid callback');
}

// Verifikasi signature
// Format: SHA256(merchantCode + amount + merchantOrderId + apiKey)
$expectedSignature = hash('sha256', $merchantCode . $amount . $merchantOrderId . $DUITKU_API_KEY);

if ($signature !== $expectedSignature) {
    file_put_contents($logFile, "Signature mismatch: expected $expectedSignature got $signature" . PHP_EOL, FILE_APPEND);
    http_response_code(403);
    exit('Invalid signature');
}

// Jika resultCode = 00 maka pembayaran sukses
if ($resultCode === "00") {
    // Update order di storage/orders.json
    $ordersFile = __DIR__ . '/../storage/orders.json';
    $orders = [];
    if (file_exists($ordersFile)) {
        $orders = json_decode(file_get_contents($ordersFile), true) ?: [];
    }

    if (isset($orders[$merchantOrderId])) {
        $orders[$merchantOrderId]['status'] = 'SUCCESS';
        $orders[$merchantOrderId]['reference'] = $reference;
        $orders[$merchantOrderId]['settlementDate'] = $settlementDate;
        file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT));
    }

    file_put_contents($logFile, "Order $merchantOrderId updated to SUCCESS" . PHP_EOL, FILE_APPEND);
} else {
    file_put_contents($logFile, "Order $merchantOrderId resultCode=$resultCode" . PHP_EOL, FILE_APPEND);
}

// Wajib return "SUCCESS" supaya Duitku anggap callback berhasil diterima
echo "SUCCESS";
