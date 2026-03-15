<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/jwt.php';
$auth = jwt_require();

if (!in_array($auth['role'], ['super_admin'])) {
    http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        $stmt = $pdo->query("SELECT id, username, email, role, is_active, created_at, last_login FROM users ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $username = trim($data['username'] ?? '');
        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $role     = $data['role'] ?? 'viewer';
        if (!$username || !$password) {
            http_response_code(400); echo json_encode(['error' => 'Username and password required']); exit();
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hash, $role]);
        echo json_encode(['id' => (int)$pdo->lastInsertId(), 'message' => 'User created']);
        break;

    case 'PUT':
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID required']); exit(); }
        $data      = json_decode(file_get_contents('php://input'), true);
        $role      = $data['role'] ?? 'viewer';
        $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        $stmt = $pdo->prepare("UPDATE users SET role = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$role, $is_active, $id]);
        echo json_encode(['message' => 'User updated']);
        break;

    case 'DELETE':
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID required']); exit(); }
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $target = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$target) { http_response_code(404); echo json_encode(['error' => 'User not found']); exit(); }
        if ($target['role'] === 'super_admin') {
            http_response_code(403); echo json_encode(['error' => 'Cannot delete super admin']); exit();
        }
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['message' => 'User deleted']);
        break;

    default:
        http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
}
