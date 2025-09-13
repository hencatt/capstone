<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="css/customModal.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <style>
        .notifications {
            background-color: red;
            width: 350px;
            height: 350px;
            border-radius: 10px;
            padding: 1rem;
            position: absolute;
            left: 50%;
            z-index: 10;
            display: none;
        }
    </style>

    <div class="modal" id="modal">

        <div class="innerModal">
            <div class="row">
                <div class="col">
                    <h1>Edit</h1>
                </div>
                <div class="col d-flex justify-content-end align-items-center">
                    <button class="btn btn-outline-secondary" id="closeModal">X</button>
                </div>
            </div>
            <form method="POST">
                <div class="row mt-4">
                    <div class="col">
                        <label for="oldDate" class="form-label">Old Date:</label>
                        <input type="date" name="oldDate" id="oldDate" class="form-control">
                    </div>
                    <div class="col-1 d-flex align-items-end justify-content-center">
                        <h3>-></h3>
                    </div>
                    <div class="col">
                        <label for="inputNewDate" class="form-label">New Date:</label>
                        <input type="date" name="inputNewDate" id="inputNewDate" placeholder="Type Something" class="form-control">
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col">
                        <label for="oldTitle" class="form-label">Old Title:</label>
                        <input type="text" name="oldTitle" id="oldTitle" class="form-control">
                    </div>
                    <div class="col-1 d-flex align-items-end justify-content-center">
                        <h3>-></h3>
                    </div>
                    <div class="col">
                        <label for="inputNewTitle" class="form-label">New Title:</label>
                        <input type="text" name="inputNewTitle" class="form-control" placeholder="Enter Title Here.">
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col">
                        <label for="oldDescription">Old Description:</label>
                        <textarea name="oldDescription" id="oldDescription" placeholder="Enter Text Here..." class="form-control" style="height: 150px;"></textarea>
                    </div>
                    <div class="col-1 d-flex align-items-center justify-content-center">
                        <h3>-></h3>
                    </div>

                    <div class="col">
                        <label for="inputNewDescription" class="form-label">New Description:</label>
                        <textarea name="inputNewDescription" id="inputNewDescription" placeholder="Enter Text Here..." class="form-control" style="height: 150px;"></textarea>
                    </div>
                </div>
                <div class="row mt-5">
                    <div class="col d-flex justify-content-end align-items-center gap-2">
                        <button class="btn btn-outline-secondary" id="closeModal">Close</button>
                        <button class="btn btn-outline-success">Save Changes</button>
                    </div>
                </div>
        </div>
        </form>

    </div>

    <div class="deleteModal" id="deleteModal1">
        <div class="deleteInnerModal">
            <div class="row">
                <div class="col">
                    <h1>Delete Item</h1>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h6>Are you sure you want to delete this item?</h6>
                </div>
            </div>
            <form method="POST">
                <div class="row">
                    <div class="col d-flex align-items-center justify-content-end mt-5 gap-2">
                        <button class="btn btn-outline-secondary cancelBtn">No</button>
                        <button class="btn btn-danger destroyBtn">Delete</button>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <div style="display: flex; justify-content:center; align-items:center; height:500px;">
        <button style="width: 100px; height: 100px; text-align:center; font-size:large" class="btn btn-primary deleteBtn" data-target="deleteModal1">click me</button>
    </div>

    <div style="display:flex; justify-content: center; align-items: center;">
        <button id="notifyBtn">
            <label for="">this is a notif</label>
        </button>
    </div>
    <div class="notifications">
        <p>Holiday on this and that</p><br>
        <p>Holiday on this and that</p><br>
    </div>
    Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nesciunt quis facilis aliquid est, laudantium dolores atque molestias consequuntur distinctio corporis, doloremque enim deserunt id recusandae laboriosam esse nihil omnis dolor?

    <script>
        $(document).ready(function() {
            const notifButton = $("#notifyBtn");
            const notifDiv = $(".notifications");

            notifButton.click(function() {
                notifDiv.toggle();
            });
        });


        const deleteButton = document.querySelectorAll(".deleteBtn");
        const deleteModal = document.querySelectorAll(".deleteModal");
        const cancelButton = document.querySelectorAll(".cancelBtn");
        const destroyButton = document.querySelectorAll(".destroyBtn");

        deleteButton.forEach(btn => {
            btn.addEventListener("click", function() {
                const targetId = btn.getAttribute("data-target");
                const modal = document.getElementById(targetId);
                modal.classList.add("open");
            });
        });
    </script>
</body>

</html>