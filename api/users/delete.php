<?php
// api/users/delete.php?id=1  (DELETE)
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
    // Don't delete a user who still owns items or outfits
    $itemCount = $databaseConnection->prepare("SELECT COUNT(*) FROM items WHERE user_id = :id");
    $itemCount->execute([':id' => $id]);
    $items = (int)$itemCount->fetchColumn();

    $outfitCount = $databaseConnection->prepare("SELECT COUNT(*) FROM outfits WHERE user_id = :id");
    $outfitCount->execute([':id' => $id]);
    $outfits = (int)$outfitCount->fetchColumn();

    if ($items > 0 || $outfits > 0) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Cannot delete: this user still has $items item(s) and $outfits outfit(s). Delete those first."
        ]);
        exit;
    }

    $stmt = $databaseConnection->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "User not found."]);
        exit;
    }

    http_response_code(200);
    echo json_encode(["success" => true, "message" => "User deleted successfully."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}