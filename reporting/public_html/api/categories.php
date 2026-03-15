<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/jwt.php';
$auth = jwt_require();

if ($auth['role'] === 'super_admin') {
    $stmt = $pdo->query("
        SELECT rc.id, rc.name, s.name as section_name
        FROM report_categories rc
        INNER JOIN sections s ON rc.section_id = s.id
        ORDER BY s.name, rc.name
    ");
} elseif ($auth['role'] === 'analyst') {
    $stmt = $pdo->prepare("
        SELECT rc.id, rc.name, s.name as section_name
        FROM report_categories rc
        INNER JOIN sections s ON rc.section_id = s.id
        INNER JOIN analyst_sections asec ON s.id = asec.section_id
        WHERE asec.analyst_id = ?
        ORDER BY s.name, rc.name
    ");
    $stmt->execute([$auth['sub']]);
} else {
    echo json_encode([]);
    exit();
}

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
