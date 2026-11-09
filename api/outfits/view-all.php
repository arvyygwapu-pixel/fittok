<?php
// api/outfits/view-all.php  (GET)   optional filter: ?user_id=1
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

$sql = "SELECT * FROM outfits";
$params = [];
if (isset($_GET['user_id']) && (int)$_GET['user_id'] > 0) {
    $sql .= " WHERE user_id = :user_id";
    $params[':user_id'] = (int)$_GET['user_id'];
}
$sql .= " ORDER BY data DESC, id DESC";

try {
    $stmt = $databaseConnection->prepare($sql);
    $stmt->execute($params);
    $outfits = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode(["success" => true, "count" => count($outfits), "outfits" => $outfits]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}