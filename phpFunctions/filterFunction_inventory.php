<?php
require_once 'gad_portal.php';

if (isset($_POST['item_category'])) {
    $category = $_POST['item_category'];

    $con = newCon();
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }

    // Base SQL
    $sql = "SELECT itemName, itemQuantity, itemSize, itemDesc, itemCategory, itemImage FROM inventory_tbl";

    // If "None" is selected, show all
    if ($category === "None") {
        $stmt = $con->prepare($sql);
    } else {
        $sql .= " WHERE itemCategory = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $category);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    echo '<table class="table table-bordered table-striped">';
    echo '
        <thead class="thead-dark">
            <tr>
                <th style="text-align:center;">Item Name</th>
                <th style="text-align:center;">Quantity</th>
                <th style="text-align:center;">Size</th>
                <th style="text-align:center;">Description</th>
                <th style="text-align:center;">Image</th>
                <th style="text-align:center;">Status</th>
                <th style="text-align:center;">Category</th>
            </tr>
        </thead>
        <tbody>
    ';

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $itemStatus = ($row['itemQuantity'] <= 0) ? 'Out of Stock' : 'In Stock';

            echo '<tr>';
            echo '<td style="text-align: center;">' . htmlspecialchars($row['itemName']) . '</td>';
            echo '<td style="text-align: center;">' . htmlspecialchars($row['itemQuantity']) . '</td>';
            echo '<td style="text-align: center;">' . htmlspecialchars($row['itemSize']) . '</td>';
            echo '<td>' . htmlspecialchars($row['itemDesc']) . '</td>';
            echo '<td style="text-align: center;"><img src="' . htmlspecialchars($row['itemImage']) . '" width="30" alt="Item image"></td>';
            echo '<td style="text-align: center;">' . $itemStatus . '</td>';
            echo '<td style="text-align: center;">' . htmlspecialchars($row['itemCategory']) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="7" style="text-align:center;">No items found.</td></tr>';
    }

    echo '</tbody></table>';

    $stmt->close();
    $con->close();
}
?>