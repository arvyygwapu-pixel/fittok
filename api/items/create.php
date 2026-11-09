<?php
// api/items/create.php  (POST)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed. Use POST."]);
    exit;
}

require_once __DIR__ . '/../database.php';

// Read JSON body (falls back to form data if JSON is not sent)
$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) {
    $data = $_POST;
}

$userId     = (int)($data['user_id'] ?? 0);
$categoryId = (int)($data['category_id'] ?? 0);
$picture    = trim($data['picture'] ?? '');

// All three columns are NOT NULL in the table, so all are required
$errors = [];
if ($userId <= 0)     $errors[] = "'user_id' is required.";
if ($categoryId <= 0) $errors[] = "'category_id' is required.";
if ($picture === '')  $errors[] = "'picture' is required.";

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => implode(' ', $errors)]);
    exit;
}

try {
    $stmt = $databaseConnection->prepare(
        "INSERT INTO items (user_id, picture, category_id)
         VALUES (:user_id, :picture, :category_id)"
    );
    $stmt->execute([
        ':user_id'     => $userId,
        ':picture'     => $picture,
        ':category_id' => $categoryId
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Item created successfully.",
        "item" => [
            "id_"         => (int)$databaseConnection->lastInsertId(),
            "user_id"     => $userId,
            "picture"     => $picture,
            "category_id" => $categoryId
        ]
    ]);
} catch (PDOException $e) {
    // 23000 = constraint error (for example a user_id or category_id that doesn't exist)
    if ($e->getCode() === '23000') {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Constraint error: check that user_id and category_id exist. " . $e->getMessage()]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
}