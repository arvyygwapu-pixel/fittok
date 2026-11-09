<?php
// api/categories/create.php  (POST)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed. Use POST."]);
    exit;
}

require_once __DIR__ . '/../database.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) { $data = $_POST; }

$name = trim($data['name'] ?? '');
if ($name === '') {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "The 'name' field is required."]);
    exit;
}
if (mb_strlen($name) > 50) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "'name' must be 50 characters or less."]);
    exit;
}

try {
    $stmt = $databaseConnection->prepare("INSERT INTO categories (name) VALUES (:name)");
    $stmt->execute([':name' => $name]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Category created successfully.",
        "category" => ["id" => (int)$databaseConnection->lastInsertId(), "name" => $name]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}