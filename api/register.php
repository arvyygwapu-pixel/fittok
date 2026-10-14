<?php

include "helpers.php";

header('Content-Type: application/json');

// 1. Get the raw JSON string from the request body
$jsonPayload = file_get_contents('php://input');

// 2. Decode the JSON string into a PHP associative array (or object)
$data = json_decode($jsonPayload, true);

$name  = trim($data["name"] ?? "");
$email = trim($data["email"] ?? "");
$username = trim($data["username"] ?? "");
$password  = $data["password"] ?? "";


if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6)  {
    respond([
        "success" => false, "message" => "Invalid email or short password"
    ], 400);
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $sql = "INSERT INTO users (name, email, username, password) VALUES (:name, :email, :username, :password)";
    $stmt = $databaseConnection->prepare($sql);


    $stmt->execute([
        ':name'     => $name,
        ':email'    => $email,
        ':username' => $username,
        ':password' => $hashedPassword
    ]);

    respond(["success" => true]);
} catch (PDOException  $error) {
    respond(["success" => false, "message" => "Email already used"], 409); 
}

