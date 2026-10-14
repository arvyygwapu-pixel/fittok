<?php

header("Content-Type: application/json");

include_once "database.php";

function respond($data, $code = 200) {
  http_response_code($code);
  echo json_encode($data);
  exit;
}

// Returns the logged-in user's id, or stops with an error
function requireUser($conn) {
  $token = $_SERVER["HTTP_AUTHORIZATION"] ?? "";
  $token = str_replace("Bearer ", "", $token);
  $stmt = $conn->prepare("SELECT id FROM users WHERE api_token=?");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  if (!$token || !$row) respond(["success" => false, "message" => "Not logged in"], 401);
  return $row["id"];
}