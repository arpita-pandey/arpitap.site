<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit();
}
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/jwt.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if (!$username || !$password) {
    http_response_code(400); echo json_encode(['error' => 'Username and password required']); exit();
}

$stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ? AND is_active = 1");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401); echo json_encode(['error' => 'Invalid credentials']); exit();
}

$pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

// Fetch sections based on role
$sections = [];
if ($user['role'] === 'super_admin') {
    $sections = ['overview','performance','errors','reports','decisions','admin'];
} elseif ($user['role'] === 'viewer') {
    // Viewers can only see published reports
    $sections = ['reports'];
} else {
    $stmt2 = $pdo->prepare("SELECT LOWER(s.name) FROM analyst_sections a JOIN sections s ON a.section_id = s.id WHERE a.analyst_id = ?");
    $stmt2->execute([$user['id']]);
    $sections = $stmt2->fetchAll(PDO::FETCH_COLUMN);
}

$token = jwt_create([
    'sub'      => $user['id'],
    'username' => $user['username'],
    'role'     => $user['role'],
    'sections' => $sections,
    'exp'      => time() + 86400,
]);

echo json_encode([
    'token' => $token,
    'user'  => ['id' => $user['id'], 'username' => $user['username'], 'role' => $user['role'], 'sections' => $sections],
]);