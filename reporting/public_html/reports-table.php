<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';

$stmt = $pdo->query("
    SELECT id, session_id, event_type, page, event_time, created_at
    FROM events
    ORDER BY created_at DESC
    LIMIT 50
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="mb-4">Reports Table</h1>

<div class="table-responsive">
<table class="table table-striped table-hover table-bordered align-middle">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Session ID</th>
            <th>Event Type</th>
            <th>Page</th>
            <th>Event Time</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['session_id']) ?></td>
            <td><?= htmlspecialchars($row['event_type']) ?></td>
            <td><?= htmlspecialchars($row['page']) ?></td>
            <td><?= htmlspecialchars($row['event_time']) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
