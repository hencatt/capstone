<?php
$con = mysqli_connect("localhost", "root", "", "gad_portal");
$con->query("SET GLOBAL max_allowed_packet=16777216"); //sets max file upload to 16mb
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>