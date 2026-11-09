<?php
// api/categories/delete.php?id=1  (DELETE)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed. Use DELETE."]);
    exit;
}

require_once __DIR__ . '/../database.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = (int)($_GET['id'] ?? ($data['id'] ?? 0));
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "A valid 'id' is required."]);
    exit;
}

try {
    // Don't delete a category that items still use
    $used = $databaseConnection->prepare("SELECT COUNT(*) FROM items WHERE category_id = :id");
    $used->execute([':id' => $id]);
    $count = (int)$used->fetchColumn();
    if ($count > 0) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Cannot delete: $count item(s) still use this category."]);
        exit;
    }

    $stmt = $databaseConnection->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Category not found."]);
        exit;
    }

    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Category deleted successfully."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}