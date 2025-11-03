<?php

require "./gad_portal.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $itemId = intval($_POST['itemId']);
    $received = intval($_POST['received']);
    $distributed = intval($_POST['distributed']);

    $remaining = $received - $distributed;

    // Get item name
    $stmt = $con->prepare("SELECT itemName FROM inventory_tbl WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $stmt->bind_result($itemName);
    $stmt->fetch();

    echo "
    <table class='table table-bordered table-striped mt-3'>
        <thead class='thead-dark'>
            <tr>
                <th style='text-align:center;'>Name</th>
                <th style='text-align:center;'>Item Name</th>
                <th style='text-align:center;'>Received</th>
                <th style='text-align:center;'>Distributed</th>
                <th style='text-align:center;'>Remaining</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style='text-align:center;'>$name</td>
                <td style='text-align:center;'>$itemName</td>
                <td style='text-align:center;'>$received</td>
                <td style='text-align:center;'>$distributed</td>
                <td style='text-align:center;'>$remaining</td>
            </tr>
        </tbody>
    </table>";
}
