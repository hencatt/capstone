<?php
require_once 'includes.php';
session_start();

$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];


checkUser($_SESSION['user_id']);

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
        echo "Event no found";
    }
} else {
    echo "Please refresh page";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("$eventTitle") ?>
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
                                <h6 style="background-color: #ef7dd5ff; padding:10px; border-radius: 10px;"><b>Presentation on:</b> <i>$presentationMonth - $presentationDay - $presentationYear<i></h6>
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
            </div>

            <div class="row mt-5">
                <div class="col">
                    <button class="btn btn-outline-primary" id="coAuthorBtn">Assign Panel</button>
                </div>
            </div>


            <!-- CO AUTHORS MODAL -->
            <div class="modal fade" id="coauthorModal" tabindex="-1" aria-labelledby="coauthorModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="coauthorModalLabel">Panel</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="coauthor_lastname" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="coauthor_lastname" name="coauthor_lastname"
                                    placeholder="Last Name">
                            </div>
                            <div class="mb-3">
                                <label for="coauthor_firstname" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="coauthor_firstname"
                                    name="coauthor_firstname" placeholder="First Name">
                            </div>
                            <div class="mb-3">
                                <label for="coauthor_middlename" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="coauthor_middlename"
                                    name="coauthor_middlename" placeholder="Middle Name">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="addCoauthorsBtn">Add</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ---------------- -->
        </div>
    </div>



    <script>
        let coauthors = []; // global array

        $(document).ready(function () {
            const coAuthButton = $('#coAuthorBtn');
            $('#coAuthorsModal').hide();

            // Make updateCoauthorsList globally accessible
            window.updateCoauthorsList = function () {
                const tbody = document.querySelector('#coauthorsTable tbody');
                tbody.innerHTML = '';
                coauthors.forEach((c, idx) => {
                    tbody.innerHTML += `
                    <tr>
                        <td><input type="hidden" name="coauthors[${idx}][lname]" value="${c.lname}">${c.lname}</td>
                        <td><input type="hidden" name="coauthors[${idx}][fname]" value="${c.fname}">${c.fname}</td>
                        <td><input type="hidden" name="coauthors[${idx}][mname]" value="${c.mname}">${c.mname}</td>
                        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeCoauthor(${idx})">Remove</button></td>
                    </tr>`;
                });
            };

            window.removeCoauthor = function (idx) {
                coauthors.splice(idx, 1);
                updateCoauthorsList();
            }

            // Load modal content
            $('#coAuthorsModal').load("../phpFunctions/addCoAuthor.php", function () {
                // Delegate click event to dynamically added employee rows
                $('#coAuthorsModal').on('click', '.employeeRow', function () {
                    const fname = $(this).data('fname');
                    const mname = $(this).data('mname');
                    const lname = $(this).data('lname');
                    const email = $(this).data('email');

                    // Prevent duplicate co-authors in the table
                    if (!coauthors.some(c => c.lname === lname && c.fname === fname)) {
                        coauthors.push({ lname, fname, mname, email });
                        updateCoauthorsList();
                    }

                    // Add email to dropdown (NO DUPLICATE OPTIONS)
                    if ($("#inputEmail option[value='" + email + "']").length === 0) {
                        $("#inputEmail").append(`<option value="${email}">${email}</option>`);
                    }
                });

                // Close modal button inside loaded content
                $('#closeCoAuthorModal').on('click', function () {
                    $('#coAuthorsModal').hide();
                    coAuthButton.text("Assign Panel");
                });
            });

            // Toggle modal
            coAuthButton.on("click", function () {
                $('#coAuthorsModal').toggle();
                const isVisible = $('#coAuthorsModal').is(":visible");
                coAuthButton.text(isVisible ? ">>>" : "Assign Panel");
            });

            // Click outside to close
            $('body').on("click", function (e) {
                if (!$(e.target).closest('#coAuthorsModal, #coAuthorBtn').length && $('#coAuthorsModal').is(':visible')) {
                    $('#coAuthorsModal').hide();
                    coAuthButton.text("Add Co-Authors");
                }
            });
        });
    </script>
</body>

</html>