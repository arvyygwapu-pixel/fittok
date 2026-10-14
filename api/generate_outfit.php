<?php
include "helpers.php";
$userId = requireUser($conn);

$occasion = $_POST["occasion"] ?? "";
$style    = $_POST["style"] ?? "";

function pick($conn, $userId, $category, $lockedId, $occasion, $style) {
  // If the user locked this item, keep it
  if ($lockedId) {
    $s = $conn->prepare("SELECT * FROM clothes WHERE id=? AND user_id=?");
    $s->bind_param("ii", $lockedId, $userId);
  } else {
    // Prefer matching items; fall back to any item in that category
    $s = $conn->prepare("SELECT * FROM clothes WHERE user_id=? AND category=?
      ORDER BY (occasion=?) + (style=?) DESC, RAND() LIMIT 1");
    $s->bind_param("isss", $userId, $category, $occasion, $style);
  }
  $s->execute();
  return $s->get_result()->fetch_assoc();
}

$top    = pick($conn, $userId, "top",    (int)($_POST["locked_top"] ?? 0),    $occasion, $style);
$bottom = pick($conn, $userId, "bottom", (int)($_POST["locked_bottom"] ?? 0), $occasion, $style);
$shoes  = pick($conn, $userId, "shoes",  (int)($_POST["locked_shoes"] ?? 0),  $occasion, $style);

if (!$top || !$bottom || !$shoes)
  respond(["success" => false, "message" => "Add at least one top, bottom, and shoes first"], 400);

respond(["success" => true, "top" => $top, "bottom" => $bottom, "shoes" => $shoes]);