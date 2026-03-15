<?php
$page_title = "Create Report";
require_once __DIR__ . '/../../includes/header.php';

require_role(['analyst', 'super_admin']);

$message = '';
$error = '';

// Get allowed categories
$analyst_sections = get_analyst_sections();
$section_ids = array_column($analyst_sections, 'id');

if (empty($section_ids)) {
    die("You don't have access to any sections.");
}

$placeholders = implode(',', array_fill(0, count($section_ids), '?'));
$stmt = $pdo->prepare("
    SELECT id, name, section_id FROM report_categories 
    WHERE section_id IN ($placeholders)
    ORDER BY name
");
$stmt->execute($section_ids);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $content = $_POST['content'] ?? '';
    $analyst_comments = $_POST['analyst_comments'] ?? '';
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    if (!$title || !$category_id) {
        $error = "Title and category are required";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO reports (title, category_id, analyst_id, content, analyst_comments, is_public, status)
                VALUES (?, ?, ?, ?, ?, ?, 'draft')
            ");
            $stmt->execute([$title, $category_id, $_SESSION['user_id'], $content, $analyst_comments, $is_public]);
            $report_id = $pdo->lastInsertId();
            $message = "Report created successfully! <a href='view-report.php?id=$report_id'>View Report</a>";
        } catch (PDOException $e) {
            $error = "Error creating report: " . $e->getMessage();
        }
    }
}
?>

<h1>Create New Report</h1>

<?php if ($message): ?>
<div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <form method="POST">
        <div style="margin-bottom: 20px;">
            <label><strong>Report Title *</strong></label>
            <input type="text" name="title" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" placeholder="e.g., Q1 Sales Performance Analysis">
        </div>

        <div style="margin-bottom: 20px;">
            <label><strong>Category *</strong></label>
            <select name="category_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                <option value="">Select a category...</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 20px;">
            <label><strong>Report Content</strong></label>
            <p style="color: #666; font-size: 12px; margin-bottom: 10px;">Add charts, tables, and data visualizations. You can use HTML or paste formatted content.</p>
            <textarea name="content" style="width: 100%; height: 250px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; font-size: 13px;" placeholder="Enter your report content here..."></textarea>
        </div>

        <div style="margin-bottom: 20px;">
            <label><strong>Analyst Comments</strong></label>
            <p style="color: #666; font-size: 12px; margin-bottom: 10px;">Provide your interpretation and insights about the data.</p>
            <textarea name="analyst_comments" style="width: 100%; height: 150px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;" placeholder="Share your analysis, key findings, and recommendations..."></textarea>
        </div>

        <div style="margin-bottom: 20px;">
            <label>
                <input type="checkbox" name="is_public"> 
                <strong>Make this report public</strong> (viewable to all users)
            </label>
        </div>

        <button type="submit" class="button">📝 Create Report</button>
        <a href="reports.php" class="button button-secondary" style="display: inline-block;">Cancel</a>
    </form>
</div>

<div class="card" style="background: #f0f8ff; border-left: 4px solid #667eea;">
    <h3>Report Creation Tips</h3>
    <ul style="margin-left: 20px; margin-top: 10px; line-height: 1.6;">
        <li><strong>Charts:</strong> Include data visualizations like line charts, bar charts, or pie charts.</li>
        <li><strong>Tables:</strong> Provide detailed data in table format for reference.</li>
        <li><strong>Comments:</strong> Add your expert interpretation of the data and key insights.</li>
        <li><strong>Visibility:</strong> Keep reports private by default, make public when ready to share.</li>
        <li><strong>Status:</strong> Reports are created as drafts. Publish them when complete.</li>
    </ul>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
