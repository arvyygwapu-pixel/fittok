<?php
// api/outfits/delete.php?id=1  (DELETE)
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
    $stmt = $databaseConnection->prepare("DELETE FROM outfits WHERE id = :id");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Outfit not found."]);
        exit;
    }

    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Outfit deleted successfully."]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Cannot delete: this outfit still has linked items. " . $e->getMessage()]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
}