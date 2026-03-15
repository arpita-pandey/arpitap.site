<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/jwt.php';
jwt_require();

$start = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$end   = $_GET['end']   ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM events WHERE event_type = 'error' AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at) ORDER BY date ASC
");
$stmt->execute([$start, $end]);
$daily = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT
        MAX(JSON_UNQUOTE(JSON_EXTRACT(e.data, '$.message'))) as message,
        COUNT(*) as count,
        e.page as top_page,
        MAX(e.created_at) as last_seen,
        MIN(e.created_at) as first_seen,
        MAX(JSON_UNQUOTE(JSON_EXTRACT(e.data, '$.stack'))) as stack,
        (SELECT JSON_UNQUOTE(JSON_EXTRACT(s.data, '$.userAgent'))
         FROM events s WHERE s.session_id = MAX(e.session_id)
         AND s.event_type = 'static' LIMIT 1) as browser
    FROM events e
    WHERE e.event_type = 'error' AND DATE(e.created_at) BETWEEN ? AND ?
    GROUP BY e.page
    ORDER BY count DESC LIMIT 50
");
$stmt->execute([$start, $end]);
$grouped = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['daily' => $daily, 'grouped' => $grouped]);
