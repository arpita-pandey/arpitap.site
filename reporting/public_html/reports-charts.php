<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';

$stmt1 = $pdo->query("
    SELECT page, COUNT(*) AS total
    FROM events
    GROUP BY page
    ORDER BY total DESC
    LIMIT 10
");
$pageData = $stmt1->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $pdo->query("
    SELECT event_type, COUNT(*) AS total
    FROM events
    GROUP BY event_type
    ORDER BY total DESC
    LIMIT 10
");
$typeData = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$pageLabels = array_column($pageData, 'page');
$pageCounts = array_column($pageData, 'total');

$typeLabels = array_column($typeData, 'event_type');
$typeCounts = array_column($typeData, 'total');

include __DIR__ . '/../includes/header.php';
?>
<h1>Reports Charts</h1>

<canvas id="pageChart" width="600" height="300"></canvas>
<br><br>
<canvas id="typeChart" width="600" height="300"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('pageChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($pageLabels) ?>,
        datasets: [{
            label: 'Events by Page',
            data: <?= json_encode($pageCounts) ?>
        }]
    }
});

new Chart(document.getElementById('typeChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($typeLabels) ?>,
        datasets: [{
            label: 'Events by Type',
            data: <?= json_encode($typeCounts) ?>
        }]
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
