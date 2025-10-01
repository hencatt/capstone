<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <div id="modalConfirmation" style="padding:20px; border: gray 1px solid; border-radius:10px; width: max-content;">
        <div class="modalConfirmation-content">
            <div class="row">
                <div class="col">
                    <h3>Are you sure you want to approve?</h3>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col gap-3 d-flex justify-content-end">
                    <button class="btn btn-outline-danger" name="cancel">Cancel</button>
                    <button class="btn btn-outline-success" name="confirm">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>