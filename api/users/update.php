<?php
// api/users/update.php?id=1  (PUT)
// Send any of: name, email, username, password  (only what you send is changed)
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

foreach (['name', 'username'] as $col) {
    if (array_key_exists($col, $data)) {
        $value = trim((string)$data[$col]);
        if ($value === '') {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "'$col' cannot be empty."]);
            exit;
        }
        $fields[] = "$col = :$col";
        $params[":$col"] = $value;
    }
}

if (array_key_exists('email', $data)) {
    $email = trim((string)$data['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "'email' must be a valid email address."]);
        exit;
    }
    $fields[] = "email = :email";
    $params[':email'] = $email;
}

if (array_key_exists('password', $data)) {
    $password = (string)$data['password'];
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "'password' must be at least 6 characters."]);
        exit;
    }
    $fields[] = "password = :password";
    $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
}

if (empty($fields)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No fields to update. Send name, email, username and/or password."]);
    exit;
}

try {
    $check = $databaseConnection->prepare("SELECT id FROM users WHERE id = :id");
    $check->execute([':id' => $id]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "User not found."]);
        exit;
    }

    $stmt = $databaseConnection->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id");
    $stmt->execute($params);

    $get = $databaseConnection->prepare("SELECT id, name, email, username FROM users WHERE id = :id");
    $get->execute([':id' => $id]);

    http_response_code(200);
    echo json_encode(["success" => true, "message" => "User updated successfully.", "user" => $get->fetch()]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Email or username is already used."]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
}