<?php
// api/items/view-all.php  (GET)
// Optional filters: ?user_id=1  and/or  ?category_id=2
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed. Use GET."]);
    exit;
}

require_once __DIR__ . '/../database.php';

$where = [];
$params = [];

if (isset($_GET['user_id']) && (int)$_GET['user_id'] > 0) {
    $where[] = "user_id = :user_id";
    $params[':user_id'] = (int)$_GET['user_id'];
}
if (isset($_GET['category_id']) && (int)$_GET['category_id'] > 0) {
    $where[] = "category_id = :category_id";
    $params[':category_id'] = (int)$_GET['category_id'];
}

$sql = "SELECT * FROM items";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY id_ DESC";

try {
    $stmt = $databaseConnection->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "count"   => count($items),
        "items"   => $items
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}