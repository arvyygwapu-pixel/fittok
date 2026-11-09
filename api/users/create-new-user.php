<?php
// api/users/create-new-user.php  (POST)
// Body (JSON): name, email, username, password
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

$name     = trim($data['name'] ?? '');
$email    = trim($data['email'] ?? '');
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

$errors = [];
if ($name === '')     $errors[] = "'name' is required.";
if ($username === '') $errors[] = "'username' is required.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "'email' must be a valid email address.";
if (strlen($password) < 6) $errors[] = "'password' must be at least 6 characters.";

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => implode(' ', $errors)]);
    exit;
}

try {
    // Give a clear message if the email or username is already taken
    $check = $databaseConnection->prepare("SELECT email, username FROM users WHERE email = :email OR username = :username LIMIT 1");
    $check->execute([':email' => $email, ':username' => $username]);
    $existing = $check->fetch();
    if ($existing) {
        $which = ($existing['email'] === $email) ? "Email" : "Username";
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "$which is already used."]);
        exit;
    }

    $stmt = $databaseConnection->prepare(
        "INSERT INTO users (name, email, username, password) VALUES (:name, :email, :username, :password)"
    );
    $stmt->execute([
        ':name'     => $name,
        ':email'    => $email,
        ':username' => $username,
        ':password' => password_hash($password, PASSWORD_DEFAULT)
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "User created successfully.",
        "user" => [
            "id"       => (int)$databaseConnection->lastInsertId(),
            "name"     => $name,
            "email"    => $email,
            "username" => $username
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}