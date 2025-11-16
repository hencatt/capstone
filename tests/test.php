<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <title>Document</title>
    <link rel="stylesheet" href="../Users/css/toast.css">
</head>

<body>

    <div class="row">
        <div class="col-6">

            <div class="row mb-3 align-items-center">
                <label class="col-3 col-form-label fw-semibold">Name:</label>
                <div class="col">
                    <input type="text" id="inputName" class="form-control" placeholder="Enter name..." required>
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-3 col-form-label fw-semibold">Item Name:</label>
                <div class="col">
                    <input type="text" id="inputItemName" class="form-control" placeholder="Enter item..." required>
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-3 col-form-label fw-semibold">Received Item/s:</label>
                <div class="col">
                    <input type="number" id="inputReceived" class="form-control" placeholder="e.g. 10" required>
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-3 col-form-label fw-semibold">Distributed Item/s:</label>
                <div class="col">
                    <input type="number" id="inputDistributed" class="form-control" placeholder="e.g. 2" required>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="button" id="btnConvert" class="btn btn-primary px-4 fw-semibold">
                    Convert to Table
                </button>
            </div>
        </div>

        <hr class="mt-4">

        <div id="receivedTable"></div>


</body>

</html>