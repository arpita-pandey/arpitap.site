<?php
$pdo = new PDO(
    "mysql:host=localhost;dbname=analytics;charset=utf8mb4",
    "analytics_user",
    "NewStrongPass123!",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
