<?php
define('JWT_SECRET', 'cse135-reporting-secret-2026-change-in-prod');

function jwt_b64_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function jwt_b64_decode($data) {
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
}
function jwt_create($payload) {
    $header  = jwt_b64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = jwt_b64_encode(json_encode($payload));
    $sig     = jwt_b64_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    return "$header.$payload.$sig";
}
function jwt_verify($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    [$header, $payload, $sig] = $parts;
    $expected = jwt_b64_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    if (!hash_equals($expected, $sig)) return false;
    $data = json_decode(jwt_b64_decode($payload), true);
    if (isset($data['exp']) && $data['exp'] < time()) return false;
    return $data;
}
function jwt_require() {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!$header && function_exists('getallheaders')) {
        $all = getallheaders();
        $header = $all['Authorization'] ?? $all['authorization'] ?? '';
    }
    $token = null;
    if (preg_match('/Bearer\s+(.+)/i', $header, $m)) $token = $m[1];
    if (!$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
    $payload = jwt_verify($token);
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired token']);
        exit();
    }
    return $payload;
}
