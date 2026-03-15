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

// Previous period for comparison (same length window before start)
$days = max(1, (strtotime($end) - strtotime($start)) / 86400);
$prev_start = date('Y-m-d', strtotime($start) - $days * 86400);
$prev_end = date('Y-m-d', strtotime($start) - 86400);

// ── Performance Budgets ────────────────────────────────────────────────────
// Current period p75 load time per page
$stmt = $pdo->prepare("
    SELECT page,
        COUNT(*) as samples,
        ROUND(AVG(JSON_EXTRACT(data, '$.totalLoadTime'))) as avg_load,
        ROUND(JSON_EXTRACT(
            JSON_ARRAYAGG(JSON_EXTRACT(data, '$.totalLoadTime') ORDER BY JSON_EXTRACT(data, '$.totalLoadTime')),
            CONCAT('$[', FLOOR(COUNT(*) * 0.75), ']')
        )) as p75_load
    FROM events
    WHERE event_type = 'performance' AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY page ORDER BY avg_load DESC
");
// p75 via JSON_ARRAYAGG may not work on all MySQL versions, fallback approach:
$stmt = $pdo->prepare("
    SELECT page,
        COUNT(*) as samples,
        ROUND(AVG(JSON_EXTRACT(data, '$.totalLoadTime'))) as avg_load
    FROM events
    WHERE event_type = 'performance' AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY page ORDER BY avg_load DESC
");
$stmt->execute([$start, $end]);
$perf_current = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Previous period avg load per page
$stmt = $pdo->prepare("
    SELECT page,
        ROUND(AVG(JSON_EXTRACT(data, '$.totalLoadTime'))) as avg_load
    FROM events
    WHERE event_type = 'performance' AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY page
");
$stmt->execute([$prev_start, $prev_end]);
$perf_prev_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
$perf_prev = [];
foreach ($perf_prev_raw as $r) $perf_prev[$r['page']] = (int)$r['avg_load'];

// Build budgets with comparison
$budgets = [];
foreach ($perf_current as $p) {
    $prev = $perf_prev[$p['page']] ?? null;
    $budget = 3000; // default budget: 3000ms
    $budgets[] = [
        'page' => $p['page'],
        'samples' => (int)$p['samples'],
        'avg_load' => (int)$p['avg_load'],
        'prev_avg_load' => $prev,
        'budget' => $budget,
        'status' => (int)$p['avg_load'] <= $budget ? 'under' : 'over',
        'change' => $prev !== null ? (int)$p['avg_load'] - $prev : null,
    ];
}

// ── Error Triage ───────────────────────────────────────────────────────────
// Get total pageviews for rate calculation
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total FROM events
    WHERE event_type = 'page_enter' AND DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$start, $end]);
$total_pageviews = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Errors grouped by message with triage info
$stmt = $pdo->prepare("
    SELECT
        MAX(JSON_UNQUOTE(JSON_EXTRACT(e.data, '$.message'))) as message,
        COUNT(*) as count,
        e.page,
        MIN(e.created_at) as first_seen,
        MAX(e.created_at) as last_seen,
        COUNT(DISTINCT e.session_id) as affected_sessions,
        (SELECT JSON_UNQUOTE(JSON_EXTRACT(s.data, '$.userAgent'))
         FROM events s WHERE s.session_id = MAX(e.session_id)
         AND s.event_type = 'static' LIMIT 1) as browser
    FROM events e
    WHERE e.event_type = 'error' AND DATE(e.created_at) BETWEEN ? AND ?
    GROUP BY e.page
    ORDER BY count DESC LIMIT 20
");
$stmt->execute([$start, $end]);
$errors_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

$triage = [];
foreach ($errors_raw as $e) {
    // Determine priority based on frequency, page importance, and blocking potential
    $is_critical_page = in_array($e['page'], ['/', '/index.html', '/checkout.html', '/products.html']);
    $freq_score = min($e['count'], 100); // cap at 100
    $impact_score = $is_critical_page ? 2 : 1;
    $score = $freq_score * $impact_score;

    if ($score >= 50) $priority = 'Critical';
    elseif ($score >= 20) $priority = 'High';
    elseif ($score >= 5) $priority = 'Medium';
    else $priority = 'Low';

    $per_1k = $total_pageviews > 0
        ? round($e['count'] / $total_pageviews * 1000, 1)
        : 0;

    $triage[] = [
        'message' => $e['message'],
        'count' => (int)$e['count'],
        'page' => $e['page'],
        'affected_sessions' => (int)$e['affected_sessions'],
        'per_1k_pageviews' => $per_1k,
        'first_seen' => $e['first_seen'],
        'last_seen' => $e['last_seen'],
        'browser' => $e['browser'],
        'priority' => $priority,
        'is_critical_page' => $is_critical_page,
    ];
}

// ── Actionable Metrics (week over week comparison) ─────────────────────────
// Current period stats
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT session_id) as sessions FROM events WHERE DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$start, $end]);
$curr_sessions = (int)$stmt->fetch(PDO::FETCH_ASSOC)['sessions'];

$stmt = $pdo->prepare("SELECT COUNT(*) as pv FROM events WHERE event_type='page_enter' AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$start, $end]);
$curr_pv = (int)$stmt->fetch(PDO::FETCH_ASSOC)['pv'];

$stmt = $pdo->prepare("SELECT COUNT(*) as errs FROM events WHERE event_type='error' AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$start, $end]);
$curr_errors = (int)$stmt->fetch(PDO::FETCH_ASSOC)['errs'];

// Previous period stats
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT session_id) as sessions FROM events WHERE DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$prev_start, $prev_end]);
$prev_sessions = (int)$stmt->fetch(PDO::FETCH_ASSOC)['sessions'];

$stmt = $pdo->prepare("SELECT COUNT(*) as pv FROM events WHERE event_type='page_enter' AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$prev_start, $prev_end]);
$prev_pv = (int)$stmt->fetch(PDO::FETCH_ASSOC)['pv'];

$stmt = $pdo->prepare("SELECT COUNT(*) as errs FROM events WHERE event_type='error' AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$prev_start, $prev_end]);
$prev_errors = (int)$stmt->fetch(PDO::FETCH_ASSOC)['errs'];

$pv_per_session = $curr_sessions > 0 ? round($curr_pv / $curr_sessions, 2) : 0;
$prev_pv_per_session = $prev_sessions > 0 ? round($prev_pv / $prev_sessions, 2) : 0;

$error_rate = $curr_pv > 0 ? round($curr_errors / $curr_pv * 1000, 1) : 0;
$prev_error_rate = $prev_pv > 0 ? round($prev_errors / $prev_pv * 1000, 1) : 0;

$actionable = [
    'current_period' => ['start' => $start, 'end' => $end],
    'previous_period' => ['start' => $prev_start, 'end' => $prev_end],
    'sessions' => ['current' => $curr_sessions, 'previous' => $prev_sessions],
    'pageviews' => ['current' => $curr_pv, 'previous' => $prev_pv],
    'pv_per_session' => ['current' => $pv_per_session, 'previous' => $prev_pv_per_session],
    'error_rate_per_1k' => ['current' => $error_rate, 'previous' => $prev_error_rate],
    'total_errors' => ['current' => $curr_errors, 'previous' => $prev_errors],
];

echo json_encode([
    'budgets' => $budgets,
    'triage' => $triage,
    'actionable' => $actionable,
    'total_pageviews' => $total_pageviews,
]);
