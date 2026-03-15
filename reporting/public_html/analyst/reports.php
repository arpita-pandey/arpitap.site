<?php
$page_title = "My Reports";
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/db.php';  // ← add this

require_role(['analyst', 'super_admin']);


// Get categories analyst can access
$analyst_sections = get_analyst_sections();
$section_ids = array_column($analyst_sections, 'id');

if (empty($section_ids)) {
    $section_ids = [0]; // Prevent SQL error if no sections
}

// Get reports
$placeholders = implode(',', array_fill(0, count($section_ids), '?'));
$query = "
    SELECT r.id, r.title, r.category_id, rc.name as category_name, r.status, r.created_at, r.is_public
    FROM reports r
    INNER JOIN report_categories rc ON r.category_id = rc.id
    INNER JOIN sections s ON rc.section_id = s.id
    WHERE r.analyst_id = ?
";

if (!is_super_admin()) {
    $query .= " AND s.id IN ($placeholders)";
}

$query .= " ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($query);

if (is_super_admin()) {
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt->execute(array_merge([$_SESSION['user_id']], $section_ids));
}

$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle publish/draft toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $report_id = $_POST['report_id'];
    $new_status = $_POST['status'];
    
    if (can_access_report($report_id)) {
        $stmt = $pdo->prepare("UPDATE reports SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $report_id]);
        header("Refresh:0");
        exit();
    }
}
?>

<h1>My Reports</h1>

<div style="margin-bottom: 20px;">
    <a href="create-report.php" class="button">📝 Create New Report</a>
</div>

<?php if (!empty($analyst_sections)): ?>
<div class="card" style="background: #eff; color: #339; border-left: 4px solid #339;">
    <strong>Your Accessible Sections:</strong> 
    <?= htmlspecialchars(implode(', ', array_column($analyst_sections, 'name'))) ?>
</div>
<?php endif; ?>

<?php if (empty($reports)): ?>
<div class="card">
    <p style="text-align: center; color: #999; padding: 40px 0;">
        No reports created yet. <a href="create-report.php">Create your first report</a>
    </p>
</div>
<?php else: ?>
<div class="card">
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Visibility</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reports as $report): ?>
            <tr>
                <td><strong><?= htmlspecialchars($report['title']) ?></strong></td>
                <td><?= htmlspecialchars($report['category_name']) ?></td>
                <td>
                    <span style="background: <?= $report['status'] === 'published' ? '#2ecc71' : '#f39c12' ?>; color: white; padding: 4px 8px; border-radius: 3px; font-size: 12px;">
                        <?= ucfirst($report['status']) ?>
                    </span>
                </td>
                <td>
                    <?= $report['is_public'] ? '🌍 Public' : '🔒 Private' ?>
                </td>
                <td><?= date('M d, Y', strtotime($report['created_at'])) ?></td>
                <td>
                    <a href="view-report.php?id=<?= $report['id'] ?>" class="button" style="display: inline-block; padding: 5px 10px; font-size: 12px;">View</a>
                    <a href="edit-report.php?id=<?= $report['id'] ?>" class="button" style="display: inline-block; padding: 5px 10px; font-size: 12px; background: #9b59b6;">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
