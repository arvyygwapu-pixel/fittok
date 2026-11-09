<?php
// api/outfits/create.php  (POST)
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

$userId = (int)($data['user_id'] ?? 0);
$name   = trim($data['name'] ?? '');
$date   = trim($data['data'] ?? '');   // the column is called "data" (type DATE)

// If no date is sent, use today
if ($date === '') { $date = date('Y-m-d'); }

$errors = [];
if ($userId <= 0)  $errors[] = "'user_id' is required.";
if ($name === '')  $errors[] = "'name' is required.";
if (mb_strlen($name) > 100) $errors[] = "'name' must be 100 characters or less.";
$d = DateTime::createFromFormat('Y-m-d', $date);
if (!$d || $d->format('Y-m-d') !== $date) $errors[] = "'data' must be a valid date in YYYY-MM-DD format.";

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => implode(' ', $errors)]);
    exit;
}

try {
    $stmt = $databaseConnection->prepare(
        "INSERT INTO outfits (user_id, name, data) VALUES (:user_id, :name, :data)"
    );
    $stmt->execute([':user_id' => $userId, ':name' => $name, ':data' => $date]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Outfit created successfully.",
        "outfit" => [
            "id"      => (int)$databaseConnection->lastInsertId(),
            "user_id" => $userId,
            "name"    => $name,
            "data"    => $date
        ]
    ]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Constraint error: check that user_id exists. " . $e->getMessage()]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
}   