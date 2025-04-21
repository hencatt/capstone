<?php
$con = mysqli_connect("localhost", "root", "", "gad_portal");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>