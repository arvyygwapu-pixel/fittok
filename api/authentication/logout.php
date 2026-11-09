<?php
// api/authentication/logout.php  (GET or POST)
include "../helpers.php";

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    respond([
        "success" => false,
        "message" => "Method not allowed. Use GET or POST."
    ], 405);
}

// login.php stores the user in the session, so we read the same session here
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    respond([
        "success" => false,
        "message" => "You are not logged in."
    ], 401);
}

// 1. Clear all session data
$_SESSION = [];

// 2. Delete the session cookie in the browser / Postman
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 3. Destroy the session on the server
session_destroy();

respond([
    "success" => true,
    "message" => "Logged out successfully."
]);