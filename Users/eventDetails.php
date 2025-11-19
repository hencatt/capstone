<?php
require_once 'includes.php';
session_start();

$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];

checkUser($_SESSION['user_id']);

// ==========================================
// HANDLE PANEL ASSIGNMENT FORM SUBMISSION
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assignPanel'])) {
    $eventId = intval($_POST['eventId']);
    $panelMembers = $_POST['panelMembers'] ?? [];

    // Validate that at least 3 panel members are selected
    if (count($panelMembers) < 3) {
        echo "<script>alert('⚠️ Please select at least 3 panel members!');</script>";
    } else {
        $con = con();

        // Start transaction for data integrity
        $con->begin_transaction();

        try {
            // First, delete existing panel assignments for this event (if any)
            $deleteStmt = $con->prepare("DELETE FROM event_panel_tbl WHERE eventId = ?");
            $deleteStmt->bind_param("i", $eventId);
            $deleteStmt->execute();
            $deleteStmt->close();

            // Insert new panel assignments
            $insertStmt = $con->prepare("INSERT INTO event_panel_tbl (eventId, panelId, assignedBy, assignedDate) VALUES (?, ?, ?, NOW())");

            foreach ($panelMembers as $panelId) {
                $panelId = intval($panelId);
                $insertStmt->bind_param("iis", $eventId, $panelId, $currentUser);

                if (!$insertStmt->execute()) {
                    throw new Exception("Failed to assign panel member: " . $insertStmt->error);
                }
            }

            $insertStmt->close();

            // Commit transaction
            $con->commit();

            echo "<script>
                alert('✅ Panel members assigned successfully!');
                window.location.href = window.location.href;
            </script>";

        } catch (Exception $e) {
            // Rollback on error
            $con->rollback();
            echo "<script>alert('❌ Error: " . addslashes($e->getMessage()) . "');</script>";
            error_log("Panel Assignment Error: " . $e->getMessage());
        }

        $con->close();
    }
}

$previousPage = $_GET['prev'];
$eventId = $_GET['id'];
$con = con();
$sql = "SELECT id, 
        announceTitle, 
        announceDesc, 
        DAY(announceDate) AS day,
        MONTH(announceDate) AS month,
        YEAR(announceDate) AS year, 
        category, 
        DAY(proposalDate) AS proposalDay, 
        MONTH(proposalDate) AS proposalMonth, 
        YEAR(proposalDate) AS proposalYear, 
        DAY(acceptanceDate) AS acceptanceDay, 
        MONTH(acceptanceDate) AS acceptanceMonth, 
        YEAR(acceptanceDate) AS acceptanceYear, 
        DAY(presentationDate) AS presentationDay, 
        MONTH(presentationDate) AS presentationMonth, 
        YEAR(presentationDate) AS presentationYear
        FROM announcement_tbl
        WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $eventId);
if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $eventTitle = htmlspecialchars($row['announceTitle']);
        $eventDesc = htmlspecialchars($row['announceDesc']);
        $eventDay = htmlspecialchars($row['day']);
        $eventMonth = htmlspecialchars($row['month']);
        $eventYear = htmlspecialchars($row['year']);
        $eventCategory = htmlspecialchars($row['category']);
        $eventProposalDay = htmlspecialchars($row['proposalDay']);
        $eventProposalMonth = htmlspecialchars($row['proposalMonth']);
        $eventProposalYear = htmlspecialchars($row['proposalYear']);
        $acceptanceDay = htmlspecialchars($row['acceptanceDay']);
        $acceptanceMonth = htmlspecialchars($row['acceptanceMonth']);
        $acceptanceYear = htmlspecialchars($row['acceptanceYear']);
        $presentationDay = htmlspecialchars($row['presentationDay']);
        $presentationMonth = htmlspecialchars($row['presentationMonth']);
        $presentationYear = htmlspecialchars($row['presentationYear']);
    } else {
        echo "Event not found";
        exit;
    }
} else {
    echo "Please refresh page";
    exit;
}
$stmt->close();

// ==========================================
// FETCH ASSIGNED PANEL MEMBERS (IF ANY)
// ==========================================
$assignedPanelIds = [];
$assignedPanels = [];
$panelQuery = "SELECT 
                ep.panelId,
                CONCAT(a.fname, ' ', a.lname) as fullname,
                a.department,
                a.campus,
                ep.assignedBy,
                ep.assignedDate
               FROM event_panel_tbl ep
               INNER JOIN accounts_tbl a ON ep.panelId = a.id
               WHERE ep.eventId = ?
               ORDER BY a.lname, a.fname";
$panelStmt = $con->prepare($panelQuery);
$panelStmt->bind_param("i", $eventId);
$panelStmt->execute();
$panelResult = $panelStmt->get_result();
while ($panelRow = $panelResult->fetch_assoc()) {
    $assignedPanelIds[] = $panelRow['panelId'];
    $assignedPanels[] = $panelRow;
}
$panelStmt->close();
$con->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("$eventTitle") ?>
    <style>
        .panel-member-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .panel-member-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .panel-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .no-panels-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            color: white;
        }

        .panel-list-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
    </style>
</head>

<body>

    <div class="row everything">
        <div class="col sidebar">
            <?php echo sidebar("eventDetails", $currentPosition, "eventDetails", $eventTitle) ?>
        </div>

        <!-- MAIN CONTENTS -->
        <div class="col-10 mt-lg-3 mainContent">
            <?php echo topbar("$currentUser", $currentPosition, "eventDetails", $eventTitle, $previousPage) ?>
            <div id="contents">
                <div class="row mt-5">
                    <div class="col">
                        <h1><?= $eventTitle ?></h1>
                    </div>
                    <?php if ($eventCategory === "Research Event") {
                        echo <<<EOD
                            <div class="col d-flex align-items-center justify-content-start" style="color: white;">
                                <h6 style="background-color: #ef7dd5ff; padding:10px; border-radius: 10px;"><b>Presentation on:</b> <i>$presentationMonth - $presentationDay - $presentationYear</i></h6>
                            </div>
                        EOD;
                    } else {
                        echo <<<EOD
                        <div class="col">
                        </div>
                        EOD;
                    }
                    ?>
                </div>
                <div class="row mt-3">
                    <div class="col" style="background-color: white;
                    padding: 10px 15px;
                    border-radius: 10px;
                    ">
                        <p><?= $eventDesc ?></p>
                    </div>
                </div>
                <div class="row mt-3 gap-5">
                    <?php if ($eventCategory === "Research Event") {
                        echo <<<EOD
                        <div class="col flex flex-col text-center" style= "
                        background-color: white;
                        width: max-content;
                        height: max-content;
                        border-radius: 10px;
                        padding: 10px;
                        "
                        >
                            <h6>Proposal Date</h6>
                            <label>$eventProposalMonth - $eventProposalDay - $eventProposalYear</label>
                        </div>
                        <div class="col flex flex-col text-center" style= "
                        background-color: white;
                        width: max-content;
                        height: max-content;
                        border-radius: 10px;
                        padding: 10px;
                        "
                        >
                            <h6>Acceptance Date</h6>
                            <label>$acceptanceMonth - $acceptanceDay - $acceptanceYear</label>
                        </div>
                        <div class="col flex flex-col text-center" style= "
                        background-color: white;
                        width: max-content;
                        height: max-content;
                        border-radius: 10px;
                        padding: 10px;
                        "
                        >
                            <h6>Presentation Date</h6>
                            <label>$presentationMonth - $presentationDay - $presentationYear</label>
                        </div>
                        EOD;
                    }
                    ?>
                </div>

                <!-- DISPLAY ASSIGNED PANEL MEMBERS -->
                <?php if ($eventCategory === "Research Event"): ?>
                    <div class="row mt-4">
                        <div class="col">
                            <?php if (count($assignedPanels) > 0): ?>
                                <div class="panel-list-container">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4><i class="fas fa-users"></i> Assigned Panel Members (<?= count($assignedPanels) ?>)
                                        </h4>
                                        <span class="text-muted">
                                            <small>
                                                <i class="fas fa-user-tie"></i> Assigned by:
                                                <?= htmlspecialchars($assignedPanels[0]['assignedBy']) ?>
                                                <br>
                                                <i class="fas fa-calendar"></i>
                                                <?= date('M d, Y', strtotime($assignedPanels[0]['assignedDate'])) ?>
                                            </small>
                                        </span>
                                    </div>
                                    <div class="row g-3">
                                        <?php foreach ($assignedPanels as $panel): ?>
                                            <div class="col-md-4">
                                                <div class="panel-member-card">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-shrink-0">
                                                            <div
                                                                style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.2rem;">
                                                                <?= strtoupper(substr($panel['fullname'], 0, 1)) ?>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1"><?= htmlspecialchars($panel['fullname']) ?></h6>
                                                            <small class="text-muted d-block">
                                                                <i class="fas fa-building"></i>
                                                                <?= htmlspecialchars($panel['department']) ?>
                                                            </small>
                                                            <small class="text-muted d-block">
                                                                <i class="fas fa-map-marker-alt"></i>
                                                                <?= htmlspecialchars($panel['campus']) ?>
                                                            </small>
                                                            <span class="panel-badge mt-2">Panel Member</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="no-panels-card">
                                    <i class="fas fa-user-slash fa-3x mb-3"></i>
                                    <h4>No Panel Members Assigned Yet</h4>
                                    <p class="mb-0">Click the "Assign Panel" button below to assign panel members to this event.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($eventCategory === "Research Event"): ?>
                <div class="row mt-4">
                    <div class="col">
                        <button class="btn btn-outline-primary" id="addPanelBtn">
                            <i class="fas fa-user-plus"></i>
                            <?= count($assignedPanels) > 0 ? 'Reassign Panel Members' : 'Assign Panel Members' ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- PANEL MODAL -->
            <div id="addPanelModal" style="display: none;">
                <div class="custom-modal-backdrop"></div>
                <div class="custom-modal-dialog">
                    <div class="custom-modal-content">
                        <div class="custom-modal-header">
                            <h1 class="custom-modal-title">
                                <i class="fas fa-users"></i>
                                <?= count($assignedPanels) > 0 ? 'Reassign Panel Members' : 'Assign Panel Members' ?>
                            </h1>
                            <button type="button" class="custom-close-btn" id="closeModalBtn">&times;</button>
                        </div>
                        <form method="POST" action="" id="panelAssignForm">
                            <div class="custom-modal-body">
                                <input type="hidden" name="eventId" value="<?= $eventId ?>">

                                <?php if (count($assignedPanels) > 0): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Note:</strong> This event already has <?= count($assignedPanels) ?> panel
                                        member(s) assigned. Submitting this form will replace the current assignment.
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="form-label"><strong>Select Panel Members (Minimum 3
                                            required)</strong></label>
                                    <div id="panelMembersList" class="border rounded p-3"
                                        style="max-height: 400px; overflow-y: auto; background-color: white;">
                                        <?php
                                        $con = con();
                                        $sql = "SELECT 
                                            a.id, 
                                            CONCAT(a.fname, ' ', a.lname) as fullname, 
                                            a.department, 
                                            a.campus 
                                        FROM accounts_tbl a
                                        INNER JOIN employee_tbl e ON a.id = e.id
                                        WHERE a.position = 'Panel' 
                                        AND a.is_active = 1
                                        AND e.status = 'Active'
                                        ORDER BY a.lname, a.fname ASC";
                                        $result = $con->query($sql);

                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                $isChecked = in_array($row['id'], $assignedPanelIds) ? 'checked' : '';
                                                echo '
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input panel-checkbox" type="checkbox" name="panelMembers[]" value="' . htmlspecialchars($row['id']) . '" id="panel' . htmlspecialchars($row['id']) . '" ' . $isChecked . '>
                                                    <label class="form-check-label" for="panel' . htmlspecialchars($row['id']) . '">
                                                        <strong>' . htmlspecialchars($row['fullname']) . '</strong><br>
                                                        <small class="text-muted">' . htmlspecialchars($row['department']) . ' - ' . htmlspecialchars($row['campus']) . '</small>
                                                    </label>
                                                </div>';
                                            }
                                        } else {
                                            echo '<p class="text-muted">No panel members available.</p>';
                                        }
                                        $con->close();
                                        ?>
                                    </div>
                                    <small class="text-muted">Selected: <span id="selectedCount">0</span> panel
                                        member(s)</small>
                                </div>

                                <div class="alert alert-warning" role="alert" id="panelWarning" style="display: none;">
                                    <i class="fas fa-exclamation-triangle"></i> Please select at least 3 panel members.
                                </div>
                            </div>
                            <div class="custom-modal-footer">
                                <button type="button" class="btn btn-secondary" id="cancelModalBtn">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-primary" name="assignPanel" id="assignPanelBtn">
                                    <i class="fas fa-check"></i>
                                    <?= count($assignedPanels) > 0 ? 'Update Assignment' : 'Assign Panel' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <style>
                /* Custom Modal Styles */
                #addPanelModal {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 9999;
                }

                .custom-modal-backdrop {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0, 0, 0, 0.5);
                    z-index: 9998;
                }

                .custom-modal-dialog {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 90%;
                    max-width: 800px;
                    z-index: 9999;
                }

                .custom-modal-content {
                    background-color: white;
                    border-radius: 8px;
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                    max-height: 90vh;
                    overflow-y: auto;
                }

                .custom-modal-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 1rem 1.5rem;
                    border-bottom: 1px solid #dee2e6;
                }

                .custom-modal-title {
                    margin: 0;
                    font-size: 1.25rem;
                    font-weight: 500;
                }

                .custom-close-btn {
                    background: none;
                    border: none;
                    font-size: 2rem;
                    line-height: 1;
                    cursor: pointer;
                    padding: 0;
                    width: 30px;
                    height: 30px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .custom-close-btn:hover {
                    opacity: 0.7;
                }

                .custom-modal-body {
                    padding: 1.5rem;
                }

                .custom-modal-footer {
                    display: flex;
                    justify-content: flex-end;
                    gap: 10px;
                    padding: 1rem 1.5rem;
                    border-top: 1px solid #dee2e6;
                }
            </style>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            const addPanelBtn = $("#addPanelBtn");
            const panelModal = $("#addPanelModal");
            const closeModalBtn = $("#closeModalBtn");
            const cancelModalBtn = $("#cancelModalBtn");
            const panelCheckboxes = $(".panel-checkbox");
            const selectedCount = $("#selectedCount");
            const assignPanelBtn = $("#assignPanelBtn");
            const panelWarning = $("#panelWarning");

            // Update count on page load
            updateCount();

            // Open modal
            addPanelBtn.on("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                panelModal.fadeIn(200);
                $("body").css("overflow", "hidden");
            });

            // Close modal functions
            function closeModal() {
                panelModal.fadeOut(200);
                $("body").css("overflow", "auto");
            }

            closeModalBtn.on("click", closeModal);
            cancelModalBtn.on("click", closeModal);

            // Close on backdrop click
            $(".custom-modal-backdrop").on("click", closeModal);

            // Update count and validate selection
            function updateCount() {
                const checkedCount = $(".panel-checkbox:checked").length;
                selectedCount.text(checkedCount);

                if (checkedCount >= 3) {
                    assignPanelBtn.prop("disabled", false);
                    panelWarning.hide();
                } else {
                    assignPanelBtn.prop("disabled", true);
                    if (checkedCount > 0) {
                        panelWarning.show();
                    } else {
                        panelWarning.hide();
                    }
                }
            }

            panelCheckboxes.on("change", updateCount);

            // Validate before form submission
            $("#panelAssignForm").on("submit", function (e) {
                const checkedCount = $(".panel-checkbox:checked").length;
                if (checkedCount < 3) {
                    e.preventDefault();
                    panelWarning.show();
                    return false;
                }
                return true;
            });
        });
    </script>
</body>

</html>