<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: /login.php");
        exit();
    }
}

function require_role($required_roles) {
    require_login();

    $required_roles = is_array($required_roles) ? $required_roles : [$required_roles];

    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $required_roles)) {
        http_response_code(403);
        die("Access Denied: You do not have permission to access this page.");
    }
}

function is_super_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
}

function is_analyst() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'analyst';
}

function is_viewer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'viewer';
}

function can_view_section($section_id) {
    if (is_super_admin()) {
        return true;
    }

    if (!is_analyst()) {
        return false;
    }

    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 1
        FROM analyst_sections
        WHERE analyst_id = ? AND section_id = ?
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id'], $section_id]);
    return $stmt->fetch() !== false;
}

function get_analyst_sections() {
    global $pdo;

    if (is_super_admin()) {
        $stmt = $pdo->query("SELECT id, name FROM sections ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if (is_analyst()) {
        $stmt = $pdo->prepare("
            SELECT s.id, s.name
            FROM sections s
            INNER JOIN analyst_sections ass ON s.id = ass.section_id
            WHERE ass.analyst_id = ?
            ORDER BY s.name
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return [];
}

function can_access_report($report_id) {
    global $pdo;

    if (is_super_admin()) {
        return true;
    }

    if (is_analyst()) {
        $stmt = $pdo->prepare("
            SELECT 1
            FROM reports
            WHERE id = ? AND analyst_id = ?
            LIMIT 1
        ");
        $stmt->execute([$report_id, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            return true;
        }
    }

    if (is_viewer()) {
        $stmt = $pdo->prepare("
            SELECT 1
            FROM reports r
            LEFT JOIN report_viewers rv ON r.id = rv.report_id
            WHERE r.id = ? AND (r.is_public = 1 OR rv.viewer_id = ?)
            LIMIT 1
        ");
        $stmt->execute([$report_id, $_SESSION['user_id']]);
        return $stmt->fetch() !== false;
    }

    return false;
}
?>
