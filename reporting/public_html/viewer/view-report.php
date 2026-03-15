<?php
$page_title = "View Report";
require_once __DIR__ . '/../../includes/header.php';

require_role('viewer');

$report_id = $_GET['id'] ?? '';

if (!$report_id || !can_access_report($report_id)) {
    die("Report not found or access denied.");
}

// Get report - viewers can only see published reports
$stmt = $pdo->prepare("
    SELECT r.*, rc.name as category_name, u.username as analyst_name
    FROM reports r
    INNER JOIN report_categories rc ON r.category_id = rc.id
    INNER JOIN users u ON r.analyst_id = u.id
    WHERE r.id = ? AND r.status = 'published'
");
$stmt->execute([$report_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    die("Report not found.");
}

// Check access
$can_access = false;
if ($report['is_public']) {
    $can_access = true;
} else {
    $stmt = $pdo->prepare("SELECT 1 FROM report_viewers WHERE report_id = ? AND viewer_id = ? LIMIT 1");
    $stmt->execute([$report_id, $_SESSION['user_id']]);
    $can_access = $stmt->fetch() !== false;
}

if (!$can_access) {
    die("You don't have access to this report.");
}
?>

<div id="report-content">
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1><?= htmlspecialchars($report['title']) ?></h1>
    <div>
        <button onclick="exportPDF()" class="button" style="background: #e74c3c;">📥 Export PDF</button>
    </div>
</div>

<div class="card" style="background: #f8f9fa; border-bottom: 3px solid #667eea;">
    <p><strong>Category:</strong> <?= htmlspecialchars($report['category_name']) ?></p>
    <p><strong>Author:</strong> <?= htmlspecialchars($report['analyst_name']) ?></p>
    <p><strong>Published:</strong> <?= date('M d, Y H:i', strtotime($report['created_at'])) ?></p>
</div>

<?php if ($report['content']): ?>
<div class="card">
    <h2>📊 Report Content</h2>
    <div style="line-height: 1.6; color: #333;">
        <?= $report['content'] ?>
    </div>
</div>
<?php endif; ?>

<?php if ($report['analyst_comments']): ?>
<div class="card" style="background: #f0f8ff; border-left: 4px solid #667eea;">
    <h2>✍️ Analyst Insights</h2>
    <div style="line-height: 1.6; color: #333; white-space: pre-wrap;">
        <?= htmlspecialchars($report['analyst_comments']) ?>
    </div>
</div>
<?php endif; ?>
</div>

<div style="margin-top: 20px;">
    <a href="view-reports.php" class="button button-secondary">← Back to Reports</a>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function exportPDF() {
    const element = document.getElementById('report-content');
    const title = <?= json_encode($report['title']) ?>;
    html2pdf().set({
        filename: title.replace(/[^a-zA-Z0-9]/g, '_') + '.pdf',
        margin: 10,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    }).from(element).save();
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
