<?php
$page_title = "View Report";
require_once __DIR__ . '/../../includes/header.php';

require_login();

$report_id = $_GET['id'] ?? '';

if (!$report_id || !can_access_report($report_id)) {
    die("Report not found or access denied.");
}

$stmt = $pdo->prepare("
    SELECT r.*, rc.name as category_name, u.username as analyst_name
    FROM reports r
    INNER JOIN report_categories rc ON r.category_id = rc.id
    INNER JOIN users u ON r.analyst_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$report_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    die("Report not found.");
}

?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1><?= htmlspecialchars($report['title']) ?></h1>
    <div>
        <?php if (is_analyst() && $report['analyst_id'] == $_SESSION['user_id']): ?>
            <a href="edit-report.php?id=<?= $report['id'] ?>" class="button" style="background: #9b59b6;">Edit</a>
        <?php endif; ?>
        <button onclick="exportPDF()" class="button" style="background: #e74c3c;">📥 Export PDF</button>
    </div>
</div>

<div id="report-content">
<div class="card" style="background: #f8f9fa; border-bottom: 3px solid #667eea;">
    <p><strong>Category:</strong> <?= htmlspecialchars($report['category_name']) ?></p>
    <p><strong>Author:</strong> <?= htmlspecialchars($report['analyst_name']) ?></p>
    <p><strong>Status:</strong> <span style="background: <?= $report['status'] === 'published' ? '#2ecc71' : '#f39c12' ?>; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;"><?= ucfirst($report['status']) ?></span></p>
    <p><strong>Created:</strong> <?= date('M d, Y H:i', strtotime($report['created_at'])) ?></p>
    <p><strong>Visibility:</strong> <?= $report['is_public'] ? '🌍 Public' : '🔒 Private' ?></p>
</div>

<?php if ($report['content']): ?>
<div class="card">
    <h2>Report Content</h2>
    <div style="line-height: 1.6; color: #333;">
        <?= $report['content'] ?>
    </div>
</div>
<?php endif; ?>

<?php if ($report['analyst_comments']): ?>
<div class="card" style="background: #f0f8ff; border-left: 4px solid #667eea;">
    <h2>✍️ Analyst Comments</h2>
    <div style="line-height: 1.6; color: #333; white-space: pre-wrap;">
        <?= htmlspecialchars($report['analyst_comments']) ?>
    </div>
</div>
<?php endif; ?>

</div><!-- /report-content -->

<div style="margin-top: 20px;">
    <a href="<?php echo is_analyst() ? 'reports.php' : '../../viewer/view-reports.php'; ?>" class="button button-secondary">← Back</a>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function exportPDF() {
    const filename = <?= json_encode('report_' . $report['id'] . '_' . date('Y-m-d') . '.pdf') ?>;
    html2pdf()
        .set({ filename: filename, margin: 10, image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 }, jsPDF: { unit: 'mm', format: 'a4' } })
        .from(document.getElementById('report-content'))
        .save();
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
