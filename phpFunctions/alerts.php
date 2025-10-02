<?php
function alertSuccess($title, $message)
{
    echo "
    <script>
        Swal.fire({
            title: '" . addslashes($title) . "',
            text: '" . addslashes($message) . "',
            icon: 'success',
            confirmButtonText: 'Ok'
        });
    </script>
    ";
}

function alertError($title, $message)
{
    echo "
    <script>
        Swal.fire({
            title: '" . addslashes($title) . "',
            text: '" . addslashes($message) . "',
            icon: 'error',
            confirmButtonText: 'Ok'
        });
    </script>
    ";
}

function alertJsonEncode($type, $title, $message){
    header('Content-Type: application/json');
    echo json_encode([
        "title" => $title,
        "message" => $message,
        "type" => $type
    ]);
    exit;
}
?>