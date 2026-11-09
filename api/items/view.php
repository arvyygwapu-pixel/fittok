<?php
// api/items/view.php?id=1  (GET)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed. Use GET."]);
    exit;
}

require_once __DIR__ . '/../database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "A valid 'id' query parameter is required."]);
    exit;
}

try {
    $stmt = $databaseConnection->prepare("SELECT * FROM items WHERE id_ = :id");
    $stmt->execute([':id' => $id]);
    $item = $stmt->fetch();

    if (!$item) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Item not found."]);
        exit;
    }

    http_response_code(200);
    echo json_encode(["success" => true, "item" => $item]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}