<?php
// api/outfits/update.php?id=1  (PUT)   send "name" and/or "data" (YYYY-MM-DD)
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

$fields = [];
$params = [':id' => $id];

if (array_key_exists('name', $data)) {
    $name = trim((string)$data['name']);
    if ($name === '' || mb_strlen($name) > 100) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "'name' must be 1 to 100 characters."]);
        exit;
    }
    $fields[] = "name = :name";
    $params[':name'] = $name;
}

if (array_key_exists('data', $data)) {
    $date = trim((string)$data['data']);
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "'data' must be a valid date in YYYY-MM-DD format."]);
        exit;
    }
    $fields[] = "data = :data";
    $params[':data'] = $date;
}

if (empty($fields)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No fields to update. Send 'name' and/or 'data'."]);
    exit;
}

try {
    $check = $databaseConnection->prepare("SELECT id FROM outfits WHERE id = :id");
    $check->execute([':id' => $id]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Outfit not found."]);
        exit;
    }

    $stmt = $databaseConnection->prepare("UPDATE outfits SET " . implode(', ', $fields) . " WHERE id = :id");
    $stmt->execute($params);

    $get = $databaseConnection->prepare("SELECT * FROM outfits WHERE id = :id");
    $get->execute([':id' => $id]);

    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Outfit updated successfully.", "outfit" => $get->fetch()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}