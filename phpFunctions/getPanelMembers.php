<?php
/**
 * Get Panel Members for a Research Event
 * Returns HTML list of panel members assigned to an event
 */

require_once './gad_portal.php';

if (!isset($_GET['eventId']) || empty($_GET['eventId'])) {
    echo '<p class="text-muted">No event selected</p>';
    exit;
}

$eventId = intval($_GET['eventId']);

$con = con();

// Fetch panel members assigned to this event
$sql = "SELECT 
            a.id,
            CONCAT(a.fname, ' ', a.lname) as fullname,
            a.department,
            a.campus,
            ep.assignedDate
        FROM event_panel_tbl ep
        INNER JOIN accounts_tbl a ON ep.panelId = a.id
        WHERE ep.eventId = ?
        ORDER BY a.lname, a.fname";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<div class="list-group">';
    
    while ($row = $result->fetch_assoc()) {
        echo '
        <div class="list-group-item">
            <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1">' . htmlspecialchars($row['fullname']) . '</h6>
                <small class="text-muted">Panel Member</small>
            </div>
            <p class="mb-1">
                <small class="text-muted">
                    <i class="fas fa-building"></i> ' . htmlspecialchars($row['department']) . ' - 
                    <i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($row['campus']) . '
                </small>
            </p>
        </div>';
    }
    
    echo '</div>';
    echo '<small class="text-muted mt-2 d-block">Total: ' . $result->num_rows . ' panel member(s)</small>';
} else {
    echo '<div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-triangle"></i> No panel members assigned to this event yet.
          </div>';
}

$stmt->close();
$con->close();
?>