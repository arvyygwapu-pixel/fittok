<?php
// api/authentication/login.php (POST)
include "../helpers.php";

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
if (!is_array($data) || empty($data)) {
    $data = $_POST;
}

$loginInput = trim($data["username"] ?? $data["email"] ?? "");
$password   = $data["password"] ?? "";

if ($loginInput === "" || $password === "") {
    respond([
        "success" => false,
        "message" => "Username and password are required."
    ], 400);
}

try {
    $sql = "SELECT id, name, username, email, password 
            FROM users 
            WHERE username = :username OR email = :email 
            LIMIT 1";
    $stmt = $databaseConnection->prepare($sql);
    $stmt->execute([
        ':username' => $loginInput,
        ':email'    => $loginInput
    ]);

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = (int)$user['id'];

        respond([
            "success" => true,
            "message" => "Authentication successful!",
            "user"    => [
                "id"       => (int)$user['id'],
                "name"     => $user['name'],
                "username" => $user['username'],
                "email"    => $user['email']
            ]
        ]);
    } else {
        respond([
            "success" => false,
            "message" => "Invalid username or password."
        ], 401);
    }
} catch (PDOException $e) {
    respond([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ], 500);
}