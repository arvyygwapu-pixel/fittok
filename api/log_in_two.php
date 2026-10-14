
<?php
header("Content-Type: application/json; charset=UTF-8");

include "helpers.php";

$email = trim($_POST["email"] ?? "");
$pass  = $_POST["password"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pass === "") {
    respond([
        "success" => false,
        "message" => "Please enter a valid email and password"
    ], 400);
}

$stmt = $conn->prepare(
    "SELECT id, name, password FROM users WHERE email = ? LIMIT 1"
);

if (!$stmt) {
    respond([
        "success" => false,
        "message" => "Database query failed"
    ], 500);
}

$stmt->bind_param("s", $email);

if (!$stmt->execute()) {
    respond([
        "success" => false,
        "message" => "Unable to process login"
    ], 500);
}

$stmt->bind_result($userId, $name, $passwordHash);

if ($stmt->fetch() && password_verify($pass, $passwordHash)) {
    $stmt->close();

    respond([
        "success" => true,
        "message" => "Login successful",
        "user" => [
            "id" => $userId,
            "name" => $name,
            "email" => $email
        ]
    ], 200);
}

$stmt->close();

respond([
    "success" => false,
    "message" => "Invalid email or password"
], 401);