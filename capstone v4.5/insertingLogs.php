<?php
// audit logs
function insertLog($fullname, $activity, $date){
    $con = mysqli_connect("localhost","root","","gad_portal");
    $sql = "INSERT INTO logs (username, activity, log_date) VALUES (?,?,?)";
    $stmt = $con->prepare($sql);
    if($stmt){
        $stmt->bind_param("sss", $fullname, $activity, $date);
        $stmt->execute();
        $stmt->close();
    }else{
        error_log("SQL Prepare Error: " . $con->error);
    }
}

?>
