<?php
include_once "./gad_portal.php";

$sql = "SELECT id, itemName FROM inventory_tbl WHERE itemQuantity > 0 ORDER BY itemName ASC";
$result = $con->query($sql);

$items = [];

while($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);
?>
