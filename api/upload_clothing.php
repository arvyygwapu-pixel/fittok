<?php
include "helpers.php";
$userId = requireUser($conn);

$category = $_POST["category"] ?? "";
if (!in_array($category, ["top","bottom","shoes","accessory"]))
  respond(["success" => false, "message" => "Bad category"], 400);

if (!isset($_FILES["image"]) || $_FILES["image"]["error"] !== 0)
  respond(["success" => false, "message" => "No image"], 400);

// Only allow real images
$info = getimagesize($_FILES["image"]["tmp_name"]);
$allowed = ["image/jpeg" => "jpg", "image/png" => "png"];
if (!$info || !isset($allowed[$info["mime"]]))
  respond(["success" => false, "message" => "Images only"], 400);

$filename = bin2hex(random_bytes(8)) . "." . $allowed[$info["mime"]];
move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/$filename");

$path  = "uploads/$filename";
$color = $_POST["color"] ?? "";
$style = $_POST["style"] ?? "";
$occ   = $_POST["occasion"] ?? "";

$stmt = $conn->prepare("INSERT INTO clothes (user_id,image_path,category,color,style,occasion) VALUES (?,?,?,?,?,?)");
$stmt->bind_param("isssss", $userId, $path, $category, $color, $style, $occ);
$stmt->execute();

respond(["success" => true, "image_path" => $path]);