<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/jwt.php';
$auth = jwt_require();

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            // Single report
            $stmt = $pdo->prepare("
                SELECT r.*, rc.name as category_name, s.name as section_name,
                       u.username as analyst_name
                FROM reports r
                INNER JOIN report_categories rc ON r.category_id = rc.id
                INNER JOIN sections s ON rc.section_id = s.id
                INNER JOIN users u ON r.analyst_id = u.id
                WHERE r.id = ?
            ");
            $stmt->execute([$id]);
            $report = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$report) {
                http_response_code(404);
                echo json_encode(['error' => 'Report not found']);
                exit();
            }

            // Access control
            if ($auth['role'] === 'super_admin') {
                // full access
            } elseif ($auth['role'] === 'analyst') {
                if ((int)$report['analyst_id'] !== (int)$auth['sub']) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Access denied']);
                    exit();
                }
            } elseif ($auth['role'] === 'viewer') {
                if ($report['status'] !== 'published') {
                    http_response_code(403);
                    echo json_encode(['error' => 'Access denied']);
                    exit();
                }
                if (!$report['is_public']) {
                    $stmt2 = $pdo->prepare("SELECT 1 FROM report_viewers WHERE report_id = ? AND viewer_id = ? LIMIT 1");
                    $stmt2->execute([$id, $auth['sub']]);
                    if (!$stmt2->fetch()) {
                        http_response_code(403);
                        echo json_encode(['error' => 'Access denied']);
                        exit();
                    }
                }
            }

            echo json_encode($report);
        } else {
            // List reports based on role
            if ($auth['role'] === 'super_admin') {
                $stmt = $pdo->query("
                    SELECT r.id, r.title, r.status, r.is_public, r.created_at,
                           rc.name as category_name, s.name as section_name,
                           u.username as analyst_name
                    FROM reports r
                    INNER JOIN report_categories rc ON r.category_id = rc.id
                    INNER JOIN sections s ON rc.section_id = s.id
                    INNER JOIN users u ON r.analyst_id = u.id
                    ORDER BY r.created_at DESC
                ");
            } elseif ($auth['role'] === 'analyst') {
                $stmt = $pdo->prepare("
                    SELECT r.id, r.title, r.status, r.is_public, r.created_at,
                           rc.name as category_name, s.name as section_name,
                           u.username as analyst_name
                    FROM reports r
                    INNER JOIN report_categories rc ON r.category_id = rc.id
                    INNER JOIN sections s ON rc.section_id = s.id
                    INNER JOIN users u ON r.analyst_id = u.id
                    WHERE r.analyst_id = ?
                    ORDER BY r.created_at DESC
                ");
                $stmt->execute([$auth['sub']]);
            } elseif ($auth['role'] === 'viewer') {
                $stmt = $pdo->prepare("
                    SELECT r.id, r.title, r.status, r.is_public, r.created_at,
                           rc.name as category_name, s.name as section_name,
                           u.username as analyst_name
                    FROM reports r
                    INNER JOIN report_categories rc ON r.category_id = rc.id
                    INNER JOIN sections s ON rc.section_id = s.id
                    INNER JOIN users u ON r.analyst_id = u.id
                    LEFT JOIN report_viewers rv ON r.id = rv.report_id
                    WHERE r.status = 'published'
                      AND (r.is_public = 1 OR rv.viewer_id = ?)
                    ORDER BY r.created_at DESC
                ");
                $stmt->execute([$auth['sub']]);
            }
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        if (!in_array($auth['role'], ['analyst', 'super_admin'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Only analysts can create reports']);
            exit();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $title = trim($data['title'] ?? '');
        $category_id = intval($data['category_id'] ?? 0);
        $content = $data['content'] ?? '';
        $comments = $data['analyst_comments'] ?? '';
        $is_public = intval($data['is_public'] ?? 0);
        $status = $data['status'] ?? 'draft';

        if (!$title || !$category_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Title and category are required']);
            exit();
        }

        $chart_snapshots = isset($data['chart_snapshots']) ? json_encode($data['chart_snapshots']) : null;

        $stmt = $pdo->prepare("
            INSERT INTO reports (title, category_id, analyst_id, content, analyst_comments, is_public, status, chart_snapshots)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $category_id, $auth['sub'], $content, $comments, $is_public, $status, $chart_snapshots]);
        echo json_encode(['id' => (int)$pdo->lastInsertId(), 'message' => 'Report created']);
        break;

    case 'PUT':
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID required']); exit(); }
        if (!in_array($auth['role'], ['analyst', 'super_admin'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Only analysts can edit reports']);
            exit();
        }

        // Check ownership
        if ($auth['role'] === 'analyst') {
            $stmt = $pdo->prepare("SELECT analyst_id FROM reports WHERE id = ?");
            $stmt->execute([$id]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$r || (int)$r['analyst_id'] !== (int)$auth['sub']) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                exit();
            }
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $title = trim($data['title'] ?? '');
        $category_id = intval($data['category_id'] ?? 0);
        $content = $data['content'] ?? '';
        $comments = $data['analyst_comments'] ?? '';
        $is_public = intval($data['is_public'] ?? 0);
        $status = $data['status'] ?? 'draft';

        $chart_snapshots = isset($data['chart_snapshots']) ? json_encode($data['chart_snapshots']) : null;

        $stmt = $pdo->prepare("
            UPDATE reports SET title=?, category_id=?, content=?, analyst_comments=?, is_public=?, status=?, chart_snapshots=?
            WHERE id=?
        ");
        $stmt->execute([$title, $category_id, $content, $comments, $is_public, $status, $chart_snapshots, $id]);
        echo json_encode(['message' => 'Report updated']);
        break;

    case 'DELETE':
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID required']); exit(); }
        if (!in_array($auth['role'], ['analyst', 'super_admin'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit();
        }
        if ($auth['role'] === 'analyst') {
            $stmt = $pdo->prepare("SELECT analyst_id FROM reports WHERE id = ?");
            $stmt->execute([$id]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$r || (int)$r['analyst_id'] !== (int)$auth['sub']) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                exit();
            }
        }
        $pdo->prepare("DELETE FROM report_viewers WHERE report_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM exports WHERE report_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM reports WHERE id = ?")->execute([$id]);
        echo json_encode(['message' => 'Report deleted']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
