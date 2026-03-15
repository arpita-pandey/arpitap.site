<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role('super_admin');

$page_title = "Manage Users";

$message = '';
$error = '';

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'viewer';

    if (!$username || !$password) {
        $error = "Username and password are required";
    } else {
        try {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $password_hash, $role]);
            $message = "User '$username' created successfully!";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error = "Username already exists";
            } else {
                $error = "Error creating user: " . $e->getMessage();
            }
        }
    }
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $user_id = $_POST['user_id'] ?? '';
    if ($user_id && $user_id !== '1') {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $message = "User deleted successfully!";
        } catch (PDOException $e) {
            $error = "Error deleting user: " . $e->getMessage();
        }
    } else {
        $error = "Cannot delete that user";
    }
}

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    $user_id = $_POST['user_id'] ?? '';
    $new_role = $_POST['role'] ?? '';

    if ($user_id && $user_id !== '1' && $new_role) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$new_role, $user_id]);
            $message = "User role updated successfully!";
        } catch (PDOException $e) {
            $error = "Error updating role: " . $e->getMessage();
        }
    }
}

$stmt = $pdo->query("SELECT id, username, role FROM users ORDER BY id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Manage Users</h1>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<h2>Create User</h2>
<form method="POST">
    <input type="hidden" name="action" value="create">

    <label>Username</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password</label><br>
    <input type="password" name="password" required><br><br>

    <label>Role</label><br>
    <select name="role">
        <option value="viewer">Viewer</option>
        <option value="analyst">Analyst</option>
        <option value="super_admin">Super Admin</option>
    </select><br><br>

    <button type="submit">Create User</button>
</form>

<h2>Existing Users</h2>
<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Role</th>
        <th>Update Role</th>
        <th>Delete</th>
    </tr>

    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td>
                <?php if ($user['id'] != 1): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="update_role">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <select name="role">
                            <option value="viewer" <?= $user['role'] === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                            <option value="analyst" <?= $user['role'] === 'analyst' ? 'selected' : '' ?>>Analyst</option>
                            <option value="super_admin" <?= $user['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                        </select>
                        <button type="submit">Update</button>
                    </form>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($user['id'] != 1): ?>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <button type="submit">Delete</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
