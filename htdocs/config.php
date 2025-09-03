<?php
// Simple .env parser
function env($key, $default = null) {
    static $vars = null;
    if ($vars === null) {
        $vars = [];
        $envPath = __DIR__ . '/.env';
        if (file_exists($envPath)) {
            foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (!str_contains($line, '=')) continue;
                [$k, $v] = array_map('trim', explode('=', $line, 2));
                $v = preg_replace('/^["\']|["\']$/', '', $v);
                // expand ${VAR}
                $v = preg_replace_callback('/\$\{([A-Z0-9_]+)\}/', function($m) use (&$vars) {
                    $key = $m[1];
                    return $vars[$key] ?? getenv($key) ?? '';
                }, $v);
                $vars[$k] = $v;
            }
        }
    }
    return $vars[$key] ?? getenv($key) ?? $default;
}

date_default_timezone_set('Asia/Jakarta');

define('DUITKU_MERCHANT_CODE', env('DUITKU_MERCHANT_CODE', 'DXXXX'));
define('DUITKU_API_KEY', env('DUITKU_API_KEY', 'YOUR_API_KEY'));
define('DUITKU_ENV', env('DUITKU_ENV', 'sandbox')); // sandbox | production
define('APP_URL', rtrim(env('APP_URL', 'http://localhost:8000'), '/'));
define('DUITKU_RETURN_URL', env('DUITKU_RETURN_URL', APP_URL . '/api/return.php'));
define('DUITKU_CALLBACK_URL', env('DUITKU_CALLBACK_URL', APP_URL . '/api/callback.php'));

define('API_BASE', DUITKU_ENV === 'production' ? 'https://api-prod.duitku.com' : 'https://api-sandbox.duitku.com');

function storage_path($file) {
    return __DIR__ . '/storage/' . $file;
}

function read_json($file, $default) {
    $path = storage_path($file);
    if (!file_exists($path)) return $default;
    $data = file_get_contents($path);
    if ($data === false || $data === '') return $default;
    $json = json_decode($data, true);
    return is_array($json) ? $json : $default;
}

function write_json($file, $data) {
    $path = storage_path($file);
    $tmp = $path . '.tmp';
    file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    rename($tmp, $path);
}

function now_ms() {
    return (int) round(microtime(true) * 1000);
}

// Signature for Create Invoice request header
function duitku_request_signature($merchantCode, $timestampMs, $apiKey) {
    return hash('sha256', $merchantCode . $timestampMs . $apiKey);
}

// Verify callback body signature (MD5 per docs)
function verify_callback_signature($merchantCode, $amount, $merchantOrderId, $apiKey, $signature) {
    $calc = md5($merchantCode . $amount . $merchantOrderId . $apiKey);
    return strtolower($calc) === strtolower($signature);
}

// Simple helper: generate random token
function make_token($length = 40) {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $out = '';
    for ($i=0;$i<$length;$i++) {
        $out .= $alphabet[random_int(0, strlen($alphabet)-1)];
    }
    return $out;
}

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
// CORS optional for API endpoints (if needed):
// header('Access-Control-Allow-Origin: *');

