<?php

include "helpers.php";

header('Content-Type: application/json');

$jsonPayload = file_get_contents('php://input');

$data = json_decode($jsonPayload, true);

$username = trim($data["username"] ?? "");
$password  = $data["password"] ?? "";

try {

    $sql = "SELECT id, username, password FROM users WHERE username = :username LIMIT 1";
    $stmt = $databaseConnection->prepare($sql);

    $stmt->execute([':username' => $username]);

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        echo "Authentication successful! Welcome, " . htmlspecialchars($user['username']);
        session_start();
        $_SESSION['user_id'] = $user['id'];
        
    } else {
        echo "Invalid username or password.";
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
