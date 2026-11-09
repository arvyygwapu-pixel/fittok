<?php
// api/users/view-all-user.php  (GET)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed. Use GET."]);
    exit;
}

require_once __DIR__ . '/../database.php';

try {
    // The password hash is never returned
    $stmt = $databaseConnection->query("SELECT id, name, email, username FROM users ORDER BY id ASC");
    $users = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode(["success" => true, "count" => count($users), "users" => $users]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}