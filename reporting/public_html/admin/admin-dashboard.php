<?php
$page_title = "Admin Dashboard";
require_once __DIR__ . '/../../includes/header.php';

require_role('super_admin');

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'analyst'");
$total_analysts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'viewer'");
$total_viewers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM reports");
$total_reports = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM reports WHERE status = 'published'");
$published_reports = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<h1>Admin Dashboard</h1>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
    <div class="card" style="text-align: center;">
        <h2 style="color: #667eea; margin: 10px 0;">👥</h2>
        <h3><?= $total_users ?></h3>
        <p>Total Users</p>
    </div>
    <div class="card" style="text-align: center;">
        <h2 style="color: #f39c12; margin: 10px 0;">📈</h2>
        <h3><?= $total_analysts ?></h3>
        <p>Analysts</p>
    </div>
    <div class="card" style="text-align: center;">
        <h2 style="color: #9b59b6; margin: 10px 0;">👁️</h2>
        <h3><?= $total_viewers ?></h3>
        <p>Viewers</p>
    </div>
    <div class="card" style="text-align: center;">
        <h2 style="color: #2ecc71; margin: 10px 0;">📊</h2>
        <h3><?= $total_reports ?></h3>
        <p>Total Reports</p>
    </div>
</div>

<h2>System Overview</h2>
<div class="card">
    <p><strong>Published Reports:</strong> <?= $published_reports ?> out of <?= $total_reports ?></p>
    <p><strong>System Status:</strong> <span style="color: #2ecc71; font-weight: bold;">✓ Operational</span></p>
    <p><strong>Database:</strong> Connected</p>
</div>

<h2>Quick Actions</h2>
<div class="card">
    <a href="manage-users.php" class="button">Manage Users</a>
    <a href="#" class="button" style="background: #9b59b6;">View System Logs</a>
    <a href="#" class="button" style="background: #e74c3c;">System Settings</a>
</div>

<h2>Recent Activity</h2>
<div class="card">
    <p>Latest reports published and user activities will appear here.</p>
    <?php
    $stmt = $pdo->query("
        SELECT r.id, r.title, u.username, r.created_at
        FROM reports r
        INNER JOIN users u ON r.analyst_id = u.id
        ORDER BY r.created_at DESC
        LIMIT 10
    ");
    $recent_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($recent_reports):
    ?>
    <table>
        <thead>
            <tr>
                <th>Report Title</th>
                <th>Analyst</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_reports as $report): ?>
            <tr>
                <td><?= htmlspecialchars($report['title']) ?></td>
                <td><?= htmlspecialchars($report['username']) ?></td>
                <td><?= date('M d, Y H:i', strtotime($report['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p style="color: #999;">No reports created yet.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
