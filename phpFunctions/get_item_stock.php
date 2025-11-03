<?php
require "./gad_portal.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $itemId = intval($_POST["itemId"]);

    $stmt = $con->prepare("SELECT itemQuantity FROM inventory_tbl WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $stmt->bind_result($itemQuantity);
    $stmt->fetch();
    $stmt->close();

    echo json_encode([
        "itemQuantity" => $itemQuantity
    ]);
}
