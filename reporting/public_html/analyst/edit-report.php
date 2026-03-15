<?php
$page_title = "Edit Report";
require_once __DIR__ . '/../../includes/header.php';

require_role(['analyst', 'super_admin']);

$report_id = $_GET['id'] ?? '';
$message = '';
$error = '';

if (!$report_id) {
    die("Report ID not provided.");
}

// Get the report
$stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
$stmt->execute([$report_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report || (!is_super_admin() && $report['analyst_id'] != $_SESSION['user_id'])) {
    die("Report not found or access denied.");
}

// Get categories
$analyst_sections = get_analyst_sections();
$section_ids = array_column($analyst_sections, 'id');
$placeholders = implode(',', array_fill(0, count($section_ids), '?'));

$stmt = $pdo->prepare("
    SELECT id, name FROM report_categories 
    WHERE section_id IN ($placeholders)
    ORDER BY name
");
$stmt->execute($section_ids);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $content = $_POST['content'] ?? '';
    $analyst_comments = $_POST['analyst_comments'] ?? '';
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    $status = $_POST['status'] ?? 'draft';

    if (!$title || !$category_id) {
        $error = "Title and category are required";
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE reports 
                SET title = ?, category_id = ?, content = ?, analyst_comments = ?, is_public = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $category_id, $content, $analyst_comments, $is_public, $status, $report_id]);
            $message = "Report updated successfully!";
            
            // Refresh the report data
            $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
            $stmt->execute([$report_id]);
            $report = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = "Error updating report: " . $e->getMessage();
        }
    }
}
?>

<h1>Edit Report</h1>

<?php if ($message): ?>
<div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <form method="POST">
        <div style="margin-bottom: 20px;">
            <label><strong>Report Title *</strong></label>
            <input type="text" name="title" value="<?= htmlspecialchars($report['title']) ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label><strong>Category *</strong></label>
            <select name="category_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $report['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 20px;">
            <label><strong>Report Content</strong></label>
            <textarea name="content" style="width: 100%; height: 250px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; font-size: 13px;"><?= htmlspecialchars($report['content']) ?></textarea>
        </div>

        <div style="margin-bottom: 20px;">
            <label><strong>Analyst Comments</strong></label>
            <textarea name="analyst_comments" style="width: 100%; height: 150px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;"><?= htmlspecialchars($report['analyst_comments']) ?></textarea>
        </div>

        <div style="margin-bottom: 20px;">
            <label><strong>Status</strong></label>
            <select name="status" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                <option value="draft" <?= $report['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= $report['status'] === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
        </div>

        <div style="margin-bottom: 20px;">
            <label>
                <input type="checkbox" name="is_public" <?= $report['is_public'] ? 'checked' : '' ?>> 
                <strong>Make this report public</strong>
            </label>
        </div>

        <button type="submit" class="button">💾 Save Changes</button>
        <a href="reports.php" class="button button-secondary" style="display: inline-block;">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
