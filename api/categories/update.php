<?php
// api/categories/update.php?id=1  (PUT)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'])) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed. Use PUT."]);
    exit;
}

require_once __DIR__ . '/../database.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) { $data = []; }

$id = (int)($_GET['id'] ?? $data['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "A valid 'id' is required."]);
    exit;
}

$name = trim($data['name'] ?? '');
if ($name === '' || mb_strlen($name) > 50) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "'name' is required and must be 50 characters or less."]);
    exit;
}

try {
    $check = $databaseConnection->prepare("SELECT id FROM categories WHERE id = :id");
    $check->execute([':id' => $id]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Category not found."]);
        exit;
    }

    $stmt = $databaseConnection->prepare("UPDATE categories SET name = :name WHERE id = :id");
    $stmt->execute([':name' => $name, ':id' => $id]);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Category updated successfully.",
        "category" => ["id" => $id, "name" => $name]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}