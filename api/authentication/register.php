<?php
// api/authentication/register.php  (POST)
include "../helpers.php";

header('Content-Type: application/json');

// 1. Get the raw JSON string from the request body
$jsonPayload = file_get_contents('php://input');

// 2. Decode the JSON string into a PHP associative array
$data = json_decode($jsonPayload, true);

$name     = trim($data["name"] ?? "");
$email    = trim($data["email"] ?? "");
$username = trim($data["username"] ?? "");
$password = $data["password"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
    respond([
        "success" => false,
        "message" => "Invalid email or short password"
    ], 400);
}

if ($name === "" || $username === "") {
    respond([
        "success" => false,
        "message" => "Name and username are required"
    ], 400);
}

try {
    // 3. Check if the email or username is already registered
    $check = $databaseConnection->prepare(
        "SELECT email, username FROM users WHERE email = :email OR username = :username LIMIT 1"
    );
    $check->execute([
        ':email'    => $email,
        ':username' => $username
    ]);
    $existing = $check->fetch();

    if ($existing) {
        $message = ($existing['email'] === $email) ? "Email already used" : "Username already used";
        respond([
            "success" => false,
            "message" => $message
        ], 409);
    }

    // 4. Not registered yet, so save the new user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, username, password) VALUES (:name, :email, :username, :password)";
    $stmt = $databaseConnection->prepare($sql);

    $stmt->execute([
        ':name'     => $name,
        ':email'    => $email,
        ':username' => $username,
        ':password' => $hashedPassword
    ]);

    respond(["success" => true]);
} catch (PDOException $error) {
    // 23000 = duplicate / constraint error from the database itself
    if ($error->getCode() === '23000') {
        respond(["success" => false, "message" => "Email or username already used"], 409);
    }
    respond(["success" => false, "message" => "Database error: " . $error->getMessage()], 500);
}