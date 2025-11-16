<?php
require_once './gad_portal.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$con = newCon();

if ($data['action'] === "grant") {

    $sql = "UPDATE research_tbl 
            SET research_grant = ?, research_grant_times = ? 
            WHERE id = ?";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("sii", $data['research_grant'], $data['research_grant_times'], $data['research_id']);
    $stmt->execute();

    echo json_encode(["message" => "Grant status updated successfully"]);
}

if ($data['action'] === "resubmit") {

    $sql = "UPDATE research_tbl 
            SET research_resubmission_status = ? 
            WHERE id = ?";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("si", $data['research_resubmission_status'], $data['research_id']);
    $stmt->execute();

    echo json_encode(["message" => "Resubmission status updated"]);
}
