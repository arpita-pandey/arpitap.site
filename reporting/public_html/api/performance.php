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
    SELECT page,
        COUNT(*) as samples,
        ROUND(AVG(JSON_EXTRACT(data, '$.totalLoadTime'))) as avg_load,
        ROUND(MIN(JSON_EXTRACT(data, '$.totalLoadTime'))) as min_load,
        ROUND(MAX(JSON_EXTRACT(data, '$.totalLoadTime'))) as max_load
    FROM events
    WHERE event_type = 'performance'
        AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY page ORDER BY avg_load DESC
");
$stmt->execute([$start, $end]);
$by_page = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT DATE(created_at) as date,
        ROUND(AVG(JSON_EXTRACT(data, '$.totalLoadTime'))) as avg_load
    FROM events
    WHERE event_type = 'performance'
        AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at) ORDER BY date ASC
");
$stmt->execute([$start, $end]);
$daily = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['by_page' => $by_page, 'daily' => $daily]);
