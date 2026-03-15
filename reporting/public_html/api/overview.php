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

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT session_id) as total FROM events WHERE DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$start, $end]);
$total_sessions = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE event_type = 'page_enter' AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$start, $end]);
$total_pageviews = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT AVG(JSON_EXTRACT(data, '$.totalLoadTime')) FROM events WHERE event_type = 'performance' AND JSON_EXTRACT(data, '$.totalLoadTime') > 0 AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$start, $end]);
$avg_load = (int)round($stmt->fetchColumn() ?? 0);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE event_type = 'error' AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$start, $end]);
$error_count = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT DATE(created_at) as date, COUNT(*) as count FROM events WHERE event_type = 'page_enter' AND DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY date ASC");
$stmt->execute([$start, $end]);
$daily_pageviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT page, COUNT(*) as views FROM events WHERE event_type = 'page_enter' AND DATE(created_at) BETWEEN ? AND ? GROUP BY page ORDER BY views DESC LIMIT 10");
$stmt->execute([$start, $end]);
$top_pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'summary'         => compact('total_sessions', 'total_pageviews', 'avg_load', 'error_count'),
    'daily_pageviews' => $daily_pageviews,
    'top_pages'       => $top_pages,
]);
