<?php
// api/items/update.php?id=1  (PUT)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'])) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed. Use PUT."]);
    exit;
}

require_once __DIR__ . '/../database.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) {
    $data = [];
}

// The id can come from the URL (?id=1) or from the JSON body
$id = (int)($_GET['id'] ?? $data['id'] ?? $data['id_'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "A valid 'id' is required."]);
    exit;
}

// Only the fields you send are changed
$fields = [];
$params = [':id' => $id];

if (array_key_exists('picture', $data)) {
    $picture = trim((string)$data['picture']);
    if ($picture === '') {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "'picture' cannot be empty."]);
        exit;
    }
    $fields[] = "picture = :picture";
    $params[':picture'] = $picture;
}

if (array_key_exists('category_id', $data)) {
    $categoryId = (int)$data['category_id'];
    if ($categoryId <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "'category_id' must be a valid number."]);
        exit;
    }
    $fields[] = "category_id = :category_id";
    $params[':category_id'] = $categoryId;
}

if (empty($fields)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No fields to update. Send 'picture' and/or 'category_id'."]);
    exit;
}

try {
    $check = $databaseConnection->prepare("SELECT id_ FROM items WHERE id_ = :id");
    $check->execute([':id' => $id]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Item not found."]);
        exit;
    }

    $sql = "UPDATE items SET " . implode(', ', $fields) . " WHERE id_ = :id";
    $stmt = $databaseConnection->prepare($sql);
    $stmt->execute($params);

    $get = $databaseConnection->prepare("SELECT * FROM items WHERE id_ = :id");
    $get->execute([':id' => $id]);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Item updated successfully.",
        "item"    => $get->fetch()
    ]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Constraint error: check that category_id exists. " . $e->getMessage()]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
}