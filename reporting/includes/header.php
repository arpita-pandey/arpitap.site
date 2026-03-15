<?php 
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Backend</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .button {
        display: inline-block;
        padding: 8px 16px;
        background: #667eea;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        border: none;
        cursor: pointer;
        font-size: 14px;
    }
    .button:hover { opacity: 0.85; color: white; }
    .button-secondary { background: #6c757d; }
    .card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #f8f9fa; font-weight: 600; }
    tr:hover td { background: #f8f9fa; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
    <a class="navbar-brand" href="/dashboard.php">📊 Analytics</a>
    <div class="navbar-nav ms-auto">
        <a class="nav-link" href="/dashboard.php">Dashboard</a>
        <a class="nav-link" href="/reports-table.php">Reports Table</a>
        <a class="nav-link" href="/reports-charts.php">Reports Charts</a>
        <a class="nav-link text-danger" href="/logout.php">Logout</a>
    </div>
</nav>
<div class="container-fluid py-4 px-4">
