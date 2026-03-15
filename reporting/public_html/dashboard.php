<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

if (!isset($_SESSION['role'])) {
    die('No role found in session');
}

if ($_SESSION['role'] === 'super_admin') {
    header('Location: admin/admin-dashboard.php');
    exit();
}

if ($_SESSION['role'] === 'analyst') {
    header('Location: analyst/reports.php');
    exit();
}

if ($_SESSION['role'] === 'viewer') {
    header('Location: /viewer/view-reports.php');
    exit();
}

die('Invalid user role: ' . htmlspecialchars($_SESSION['role']));
?>