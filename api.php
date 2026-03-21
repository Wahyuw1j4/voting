<?php
header('Content-Type: application/json');

$host = 'localhost';
$port = '5433';
$db   = 'voting';
$user = 'postgres';
$pass = 'hesoyam404';

$conn = pg_connect("host=$host port=$port dbname=$db user=$user password=$pass");

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$action = $_GET['action'] ?? 'list';

switch ($action) {

    case 'list':
        $result = pg_query($conn, "SELECT id, name, score, photo FROM kandidat ORDER BY id");
        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $row['id'] = (int)$row['id'];
            $row['score'] = (int)$row['score'];
            $data[] = $row;
        }
        echo json_encode($data);
        break;

    case 'vote':
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            pg_query_params($conn, "UPDATE kandidat SET score = score + 1 WHERE id = $1", [$id]);
        }
        echo json_encode(['ok' => true]);
        break;

    case 'reset':
        pg_query($conn, "UPDATE kandidat SET score = 0");
        echo json_encode(['ok' => true]);
        break;

    case 'edit':
        $id = (int)($_GET['id'] ?? 0);
        $input = json_decode(file_get_contents('php://input'), true);
        $name = trim($input['name'] ?? '');
        if ($id > 0 && $name !== '') {
            pg_query_params($conn, "UPDATE kandidat SET name = $1 WHERE id = $2", [$name, $id]);
        }
        echo json_encode(['ok' => true]);
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}

pg_close($conn);
