<?php
require __DIR__ . '/../config.php';

$token = $_GET['token'] ?? '';

if ($token === '') { http_response_code(400); echo 'Token is required'; exit; }

$tokens = read_json('tokens.json', []);
$targetOrderId = null;
foreach ($tokens as $orderId => $info) {
    if (hash_equals($info['token'], $token)) {
        $targetOrderId = $orderId;
        $productId = $info['file'];
        break;
    }
}
if (!$targetOrderId) { http_response_code(404); echo 'Invalid token'; exit; }

// map productId to file path
$products = json_decode(file_get_contents(__DIR__ . '/../products.json'), true) ?? [];
$filePath = null;
foreach ($products as $p) {
    if ($p['id'] === $productId) {
        $filePath = __DIR__ . '/../' . $p['file'];
        break;
    }
}
if (!$filePath || !file_exists($filePath)) { http_response_code(404); echo 'File not found'; exit; }

// serve file
$filename = basename($filePath);
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
