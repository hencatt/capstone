<?php
require_once '../../phpFunctions/gad_portal.php';
session_start();

?>

<table class="table table-sm table-striped">
    <thead>
        <tr style="text-align: center;">
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Size</th>
            <th>Description</th>
            <th>Image</th>
            <th>Status</th>
            <th>Category</th>
            <?php if ($_SESSION['user_position'] === "Technical Assistant") {
                echo '<th>Edit</th>';
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $con = newCon();
        $sql = "SELECT id, itemName, itemDesc, itemImage, itemQuantity, itemSize, itemCategory FROM inventory_tbl";
        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $itemStatus = "";
                if (htmlspecialchars($row['itemQuantity']) <= 0) {
                    $itemStatus = 'Out of Stock';
                } else {
                    $itemStatus = 'In Stock';
                }
                echo '<tr>';
                echo '<td style="text-align: center;">' . htmlspecialchars($row['itemName']) . '</td>';
                echo '<td style="text-align: center;">' . htmlspecialchars($row['itemQuantity']) . '</td>';
                echo '<td style="text-align: center;">' . htmlspecialchars($row['itemSize']) . '</td>';
                echo '<td>' . htmlspecialchars($row['itemDesc']) . '</td>';
                echo '<td style="text-align: center;"><img src="' . $row['itemImage'] . '" width="30" alt="Item image"></td>';
                echo '<td style="text-align: center;">' . $itemStatus . '</td>';
                echo '<td style="text-align: center;">' . htmlspecialchars($row['itemCategory']) . '</td>';
                echo '<td colspan="2"
                        style="
                        text-align: center;
                        "
                        >' .

                    '
                    '; ?><?php
                            if ($_SESSION['user_position'] === "Technical Assistant") {
                                echo '
                    <div class="row" id="editItem">
                        <div class="col d-flex justify-content-center align-items-center gap-2">
                            <button data-bs-toggle="modal" class="btn btn-outline-success" data-bs-target="#editItem' . htmlspecialchars($row['id']) . '">
                                            <span class="material-symbols-outlined">edit</span>
                                            </button>
                                            ';
                                echo
                                '<button data-bs-toggle="modal" class="btn btn-outline-danger" data-bs-target="#deleteItem' . htmlspecialchars($row['id']) . '">
                                            <span class="material-symbols-outlined">delete</span>
                                            </button>
                                            </div>
                                            </div>
                                            ' .

                                    '</td>';
                                echo '</tr>';
                            }
                            ?>
<?php

                // EDIT MODAL
                echo '
                    <div class="modal fade" id="editItem' . htmlspecialchars($row['id']) . '" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5">Edit Item</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="itemID" value="' . htmlspecialchars($row['id']) . '">
                                        <input type="hidden" name="oldItemName" value="' . htmlspecialchars($row['itemName']) . '">
                                        <label>Name:</label><br>
                                        <input value="' . htmlspecialchars($row['itemName']) . '" disabled="true" class="form-control">
                                        <label>Change to:</label>
                                        <input type="text" name="updateName" id="updateItemName" placeholder="Enter Updated Name" class="form-control" value="' . htmlspecialchars($row['itemName']) . '">
                                        <br>

                                        <label>Quantity:</label><br>
                                        <input value="' . htmlspecialchars($row['itemQuantity']) . '" disabled="true" class="form-control">
                                        <label>Change to:</label>
                                        <input type="number" name="updateQuantity" id="updateItemQuantity" placeholder="Updated Quantity" class="form-control" value="' . htmlspecialchars($row['itemQuantity']) . '">
                                        <br>

                                        <label>Size:</label><br>
                                        <input value="' . htmlspecialchars($row['itemSize']) . '" disabled="true" class="form-control">
                                        <label>Change to:</label>
                                        <select name="updateSize" id="updateItemSize" class="form-control">
                                            <option value="-" disabled ' . ($row['itemSize'] === '-' ? 'selected' : '') . '>Select Size</option>
                                            <option value="S" ' . ($row['itemSize'] === 'S' ? 'selected' : '') . '>S</option>
                                            <option value="M" ' . ($row['itemSize'] === 'M' ? 'selected' : '') . '>M</option>
                                            <option value="L" ' . ($row['itemSize'] === 'L' ? 'selected' : '') . '>L</option>
                                            <option value="XL" ' . ($row['itemSize'] === 'XL' ? 'selected' : '') . '>XL</option>
                                            <option value="XXL" ' . ($row['itemSize'] === 'XXL' ? 'selected' : '') . '>XXL</option>
                                            <option value="XXXL" ' . ($row['itemSize'] === 'XXXL' ? 'selected' : '') . '>XXXL</option>
                                            <option value="4XL" ' . ($row['itemSize'] === '4XL' ? 'selected' : '') . '>4XL</option>
                                            <option value="5XL" ' . ($row['itemSize'] === '5XL' ? 'selected' : '') . '>5XL</option>
                                            <option value="-" ' . ($row['itemSize'] === '-' ? 'selected' : '') . '>N/A</option>
                                        </select>
                                        <br>

                                        <label>Category</label><br>
                                        <input type="text" value="' . htmlspecialchars($row['itemCategory']) . '" disabled class="form-control">
                                        <label>Change to:</label>
                                        <select name="updateCategory" id="updateItemCategory" class="form-control">
                                            <option value="-" disabled ' . ($row['itemCategory'] === '-' ? 'selected' : '') . '>Select Category</option>
                                            <option value="Women" ' . ($row['itemCategory'] === 'Women' ? 'selected' : '') . '>Women</option>
                                            <option value="Men" ' . ($row['itemCategory'] === 'Men' ? 'selected' : '') . '>Men</option>
                                            <option value="LGBTQIA+" ' . ($row['itemCategory'] === 'LGBTQIA+' ? 'selected' : '') . '>LGBTQIA+</option>
                                            <option value="Education" ' . ($row['itemCategory'] === 'Education' ? 'selected' : '') . '>Education</option>
                                            <option value="PWD" ' . ($row['itemCategory'] === 'PWD' ? 'selected' : '') . '>PWD</option>
                                            <option value="Everyone" ' . ($row['itemCategory'] === 'Everyone' ? 'selected' : '') . '>Everyone</option>
                                            <option value="-" ' . ($row['itemCategory'] === '-' ? 'selected' : '') . '>N/A</option>
                                        </select>
                                        <br>

                                        <label>Image:</label><br>
                                        <img src="' . htmlspecialchars($row['itemImage']) . '" alt="Item Image" width="100"><br>
                                        <label>Change Image</label>
                                        <input type="file" name="UpdateFileToUpload" class="form-control">
                                        <input type="hidden" name="itemID" value="' . htmlspecialchars($row['id']) . '">
                                        <br>

                                        <label>Description:</label><br>
                                        <textarea class="form-control" disabled="true">' . htmlspecialchars($row['itemDesc']) . '</textarea>
                                        <label>Change to:</label>
                                        <textarea class="form-control" name="updateDesc" id="updateItemDesc" placeholder"Enter updated text here...">' . htmlspecialchars($row['itemDesc']) . '</textarea>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" name="updateItem">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>';

                // delete modal
                echo '
                    <div class="modal fade" id="deleteItem' . htmlspecialchars($row['id']) . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5">Delete ' . htmlspecialchars($row['itemName']) . '</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                            <form method="POST">
                                <input type="hidden" name="itemID" value="' . htmlspecialchars($row['id']) . '">
                                <input type="hidden" name="itemName" value="' . htmlspecialchars($row['itemName']) . '">
                                Are you sure you want to delete ' . htmlspecialchars($row['itemName']) . '?
                            </div>
                            <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" name="deleteItem">Yes</button>
                            </form>
                            </div>
                            </div>
                        </div>
                    </div>
                ';
            }
        } else {
            echo "0 results";
        }
        $con->close();
?>

    </tbody>
</table>