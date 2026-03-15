<?php
$page_title = "View Reports";
require_once __DIR__ . '/../../includes/header.php';

require_role('viewer');

// Get available reports for this viewer
$stmt = $pdo->prepare("
    SELECT r.id, r.title, r.category_id, rc.name as category_name, r.analyst_id, u.username as analyst_name, r.created_at, r.is_public
    FROM reports r
    INNER JOIN report_categories rc ON r.category_id = rc.id
    INNER JOIN users u ON r.analyst_id = u.id
    LEFT JOIN report_viewers rv ON r.id = rv.report_id
    WHERE r.status = 'published' AND (r.is_public = 1 OR rv.viewer_id = ?)
    GROUP BY r.id
    ORDER BY r.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by category
$grouped_reports = [];
foreach ($reports as $report) {
    $cat = $report['category_name'];
    if (!isset($grouped_reports[$cat])) {
        $grouped_reports[$cat] = [];
    }
    $grouped_reports[$cat][] = $report;
}
?>

<h1>📊 Available Reports</h1>

<?php if (empty($reports)): ?>
<div class="card">
    <p style="text-align: center; color: #999; padding: 40px 0;">
        No reports available to you at this time. <br>
        Check back later for new published reports.
    </p>
</div>
<?php else: ?>

<?php foreach ($grouped_reports as $category => $category_reports): ?>
<div style="margin-bottom: 30px;">
    <h2 style="color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px;">📁 <?= htmlspecialchars($category) ?></h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        <?php foreach ($category_reports as $report): ?>
        <div class="card" style="display: flex; flex-direction: column;">
            <h3><?= htmlspecialchars($report['title']) ?></h3>
            <p style="color: #666; font-size: 13px; margin: 10px 0;">
                <strong>Author:</strong> <?= htmlspecialchars($report['analyst_name']) ?>
            </p>
            <p style="color: #666; font-size: 13px; margin-bottom: 10px;">
                <strong>Published:</strong> <?= date('M d, Y', strtotime($report['created_at'])) ?>
            </p>
            <p style="flex-grow: 1; color: #999; font-size: 12px;">
                <?= $report['is_public'] ? '🌍 Public Report' : '🔒 Assigned to You' ?>
            </p>
            <div style="margin-top: auto;">
                <a href="view-report.php?id=<?= $report['id'] ?>" class="button" style="width: 100%; text-align: center;">View Report →</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
