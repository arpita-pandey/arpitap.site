<?php
require_once __DIR__ . '/../includes/auth.php';

session_unset();
session_destroy();

// If called via fetch (AJAX), just return OK
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
    isset($_SERVER['HTTP_AUTHORIZATION'])) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit();
}

header("Location: login.php");
exit();
