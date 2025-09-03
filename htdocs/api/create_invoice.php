<?php
require __DIR__ . '/../config.php';

header('Content-Type: application/json');

// basic input
$productId = $_POST['productId'] ?? '';
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if ($productId === '' || $name === '' || $email === '') {
    http_response_code(422);
    echo json_encode(['ok'=>false, 'message'=>'Data tidak lengkap']);
    exit;
}

// load product
$products = json_decode(file_get_contents(__DIR__ . '/../products.json'), true) ?? [];
$product = null;
foreach ($products as $p) {
    if ($p['id'] === $productId) { $product = $p; break; }
}
if (!$product) {
    http_response_code(404);
    echo json_encode(['ok'=>false, 'message'=>'Produk tidak ditemukan']);
    exit;
}

$merchantCode = DUITKU_MERCHANT_CODE;
$apiKey = DUITKU_API_KEY;
$timestamp = now_ms();

$merchantOrderId = 'ORD' . $timestamp; // unique
$paymentAmount = (int) $product['price'];
$productDetails = $product['name'];

$customerDetail = [
    'firstName' => $name,
    'lastName'  => '',
    'email'     => $email,
    'phoneNumber'=> $phone,
    'billingAddress' => [
        'firstName' => $name, 'lastName' => '',
        'address' => 'N/A', 'city' => 'N/A', 'postalCode' => '00000',
        'phone' => $phone, 'countryCode' => 'ID'
    ],
    'shippingAddress' => [
        'firstName' => $name, 'lastName' => '',
        'address' => 'N/A', 'city' => 'N/A', 'postalCode' => '00000',
        'phone' => $phone, 'countryCode' => 'ID'
    ]
];

$body = [
   'paymentAmount' => $paymentAmount,
   'merchantOrderId' => $merchantOrderId,
   'productDetails' => $productDetails,
   'email' => $email,
   'customerVaName' => $name,
   'phoneNumber' => $phone,
   'itemDetails' => [
       ['name' => $product['name'], 'price' => $paymentAmount, 'quantity' => 1]
   ],
   'customerDetail' => $customerDetail,
   'callbackUrl' => DUITKU_CALLBACK_URL,
   'returnUrl' => DUITKU_RETURN_URL,
   'expiryPeriod' => 60
];

$headers = [
    'Accept: application/json',
    'Content-Type: application/json',
    'x-duitku-signature: ' . duitku_request_signature($merchantCode, $timestamp, $apiKey),
    'x-duitku-timestamp: ' . $timestamp,
    'x-duitku-merchantcode: ' . $merchantCode
];

$url = API_BASE . '/api/merchant/createInvoice';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['ok'=>false, 'message'=>'Curl error: ' . curl_error($ch)]);
    exit;
}
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo $response;
    exit;
}

$res = json_decode($response, true);

// Save order
$orders = read_json('orders.json', []);
$orders[] = [
  'id' => $merchantOrderId,
  'productId' => $product['id'],
  'productName' => $product['name'],
  'email' => $email,
  'name' => $name,
  'phone' => $phone,
  'amount' => $paymentAmount,
  'status' => 'PENDING',
  'reference' => $res['reference'] ?? null,
  'paymentUrl' => $res['paymentUrl'] ?? null,
  'createdAt' => date('c')
];
write_json('orders.json', $orders);

echo json_encode([
  'ok'=> true,
  'paymentUrl' => $res['paymentUrl'] ?? null,
  'reference' => $res['reference'] ?? null,
  'orderId' => $merchantOrderId
]);
