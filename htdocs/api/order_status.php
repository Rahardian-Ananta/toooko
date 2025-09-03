<?php
require __DIR__ . '/../config.php';

header('Content-Type: application/json');
$orderId = $_GET['orderId'] ?? '';
if ($orderId === '') { echo json_encode(['ok'=>false, 'message'=>'orderId required']); exit; }

$orders = read_json('orders.json', []);
$found = null;
foreach ($orders as $o) {
    if ($o['id'] === $orderId) { $found = $o; break; }
}

if (!$found) { echo json_encode(['ok'=>false, 'message'=>'not found']); exit; }

$tokens = read_json('tokens.json', []);
$token = null;
if (isset($tokens[$orderId])) { $token = $tokens[$orderId]['token']; }

echo json_encode([
  'ok'=>true,
  'status'=>$found['status'],
  'downloadToken'=>$token
]);
