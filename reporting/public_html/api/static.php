<?php

header("Content-Type: application/json");

$method = $_SERVER["REQUEST_METHOD"];
$id = null;

if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);
}

$pdo = new PDO(
    "mysql:host=localhost;dbname=analytics;charset=utf8mb4",
    "analytics_user",
    "NewStrongPass123!",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

switch ($method) {

    case "GET":
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $pdo->query("SELECT * FROM events ORDER BY id DESC LIMIT 100");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = $pdo->prepare("
            INSERT INTO events (session_id, event_type, page, event_time, data)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data["session_id"] ?? null,
            $data["event_type"] ?? null,
            $data["page"] ?? null,
            $data["event_time"] ?? null,
            json_encode($data)
        ]);

        echo json_encode(["status" => "created"]);
        break;

    case "PUT":
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "ID required"]);
            exit();
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = $pdo->prepare("
            UPDATE events SET data = ? WHERE id = ?
        ");

        $stmt->execute([json_encode($data), $id]);

        echo json_encode(["status" => "updated"]);
        break;

    case "DELETE":
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "ID required"]);
            exit();
        }

        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(["status" => "deleted"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
}
