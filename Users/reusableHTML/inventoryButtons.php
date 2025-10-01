<a href="./employees.php"><button id="viewMoreBtn" class="btn btn-outline-primary">View
        More</button></a>
<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addItem">Add
    Item</button>


<!-- addItemModal -->
<div class="modal fade" id="addItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Add Item</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <label for="itemName">Item Name:</label><br>
                    <input type="text" name="name" id="itemName" placeholder="(e.g. T-shirt)" class="form-control"
                        required>
                    <br><br>
                    <label for="itemName">Item Quantity:</label><br>
                    <input type="number" name="quantity" id="itemQuantity" placeholder="(e.g. 100)"
                        class="form-control">
                    <br><br>
                    <label for="itemSize">Item Size:</label><br>
                    <select name="size" id="itemSize" class="form-control">
                        <option value="-" disabled selected>Select Size</option>
                        <option value="S">S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                        <option value="XXL">XXL</option>
                        <option value="XXXL">XXXL</option>
                        <option value="4XL">4XL</option>
                        <option value="5XL">5XL</option>
                        <option value="-">N/A</option>
                    </select>
                    <br><br>
                    <label for="itemDesc">Description:</label><br>
                    <textarea name="desc" id="itemDesc" placeholder="Enter item description here..."
                        class="form-control"></textarea>
                    <br><br>

                    <label for="itemCategory">Category:</label><br>
                    <select name="category" id="itemCategory" class="form-control">
                        <option value="-" disabled selected>Select Category</option>
                        <option value="Women">Women</option>
                        <option value="Men">Men</option>
                        <option value="LGBTQIA+">LGBTQIA+</option>
                        <option value="Education">Education</option>
                        <option value="PWD">PWD</option>
                        <option value="Everyone">Everyone</option>
                        <option value="-">N/A</option>

                    </select>
                    <br><br>

                    <!-- <label for="itemStatus">Status:</label><br> 
                                <select name="status" id="itemStatus" class="form-control">
                                    <option value="" disabled selected>Select Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                </select>
                                <br><br>
                                -->
                    Image:<br>
                    <input type="file" name="fileToUpload" id="fileToUpload" class="form-control">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" name="addItem">Add Item</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- editItemModal -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq"
    crossorigin="anonymous"></script>