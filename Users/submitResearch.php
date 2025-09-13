<?php
require_once 'includes.php';

session_start();

checkUser($_SESSION['user_id'], $_SESSION['user_username']);

$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];


if ($currentPosition === "Director") {
    $dashboard = "director.php";
} elseif ($currentPosition === "Technical Assistant") {
    $dashboard = "TA.php";
} elseif ($currentPosition === "Focal Person") {
    $dashboard = "focalPerson.php";
}

if (isset($_POST['submitResearch'])) {
    $title = trim($_POST['researchTitle']);
    $dateStarted = $_POST['dateStarted'];
    $dateComplete = $_POST['dateComplete'];
    $status = $_POST['researchStatus'];
    $description = trim($_POST['researchDescription']);

    // Handle file upload
    $fileName = $_FILES['researchUpload']['name'];
    $fileTmp = $_FILES['researchUpload']['tmp_name'];
    $targetDir = "researchfiles/";
    $targetFile = $targetDir . basename($fileName);

    // Get author (current user)
    $author = $currentFname . " " . $currentLname;

    // Get co-authors from JS array (combine names)
    $coauthors = [];
    if (!empty($_POST['coauthors'])) {
        foreach ($_POST['coauthors'] as $coauthor) {
            $coauthors[] = $coauthor['fname'] . " " . $coauthor['mname'] . (empty($coauthor['lname']) ? "" : " " . $coauthor['lname']);
        }
    }
    $coauthorStr = implode(", ", $coauthors);

    if (move_uploaded_file($fileTmp, $targetFile)) {
        // Insert research info with author and co-author(s)
        $stmt = $con->prepare("INSERT INTO research_tbl (research_title, date_started, date_completed, status, file, description, author, co_author) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $title, $dateStarted, $dateComplete, $status, $targetFile, $description, $author, $coauthorStr);
        $stmt->execute();
        $stmt->close();

        insertLog($currentUser, "Submitted research: $title", date('Y-m-d H:i:s'));
        header("Location: submitResearch.php?success=1");
        exit;
    } else {
        echo "<script>alert('File upload failed.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("Submit Research") ?>

</head>

<body>
    <style>
        .coAuthorsModalAni {
            animation: moveTranslate 0.3s ease-out;
        }

        @keyframes moveTranslate {
            from {
                transform: translate(30rem, 13rem);
            }

            to {
                transform: translate(34rem, 13rem);
                ;
            }

            from {
                opacity: 0%;
            }

            to {
                opacity: 100%;
            }
        }
    </style>
    <?= addDelay("researchSubmit", $currentUser, $currentPosition); ?>
    <div class="row">
        <div class="col sidebar">
            <?php echo sidebar("research", $currentPosition) ?>
        </div>

        <div class="col-lg-10 col-sm-8 col-xs-6 mt-lg-3 mainContent">
            <?php echo topbar("$currentUser", $currentPosition, "researchSubmit") ?>

            <div id="contents">
                <div class="row mt-5">
                    <h1>Research</h1>
                    <div class="col">
                        <p>Upload your New Research. After uploading the file, your research will be reviewed by the admin.</p>
                    </div>
                </div>
                <!-- Research Submission Form -->
                <form method="POST" enctype="multipart/form-data">
                    <div class="row gx-5 mb-5" style="border: solid black 1px;">
                        <div class="col">
                            <div class="row mt-3">
                                <div class="col">
                                    <label for="inputResearchTitle" class="form-label">Research Title</label>
                                    <input type="text" class="form-control" id="inputResearchTitle" name="researchTitle" placeholder="Enter title here..." required>
                                </div>
                            </div>
                            <div class="row mt-5">
                                <div class="col">
                                    <div class="d-flex justify-content-between align-items-center relative">
                                        <label for="">Co-Authors</label>
                                        <button type="button" class="btn btn-secondary" id="coAuthorBtn">
                                            Add Co-Author
                                        </button>
                                        <div class="coAuthorsModalAni" id="coAuthorsModal" style="
                                            background-color: white;
                                            position: absolute;
                                            max-height: 450px;
                                            width: 400px;
                                            border-radius: 20px;
                                            transform: translate(34rem, 13rem);
                                            filter: drop-shadow(gray 5px 4px 5px);
                                            overflow-y:auto;
                                            overflow-x:auto;
                                            ">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Co-Authors Modal -->
                            <div class="modal fade" id="coauthorModal" tabindex="-1" aria-labelledby="coauthorModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="coauthorModalLabel">Add Co-Author</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="coauthor_lastname" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" id="coauthor_lastname" name="coauthor_lastname" placeholder="Last Name">
                                            </div>
                                            <div class="mb-3">
                                                <label for="coauthor_firstname" class="form-label">First Name</label>
                                                <input type="text" class="form-control" id="coauthor_firstname" name="coauthor_firstname" placeholder="First Name">
                                            </div>
                                            <div class="mb-3">
                                                <label for="coauthor_middlename" class="form-label">Middle Name</label>
                                                <input type="text" class="form-control" id="coauthor_middlename" name="coauthor_middlename" placeholder="Middle Name">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary" id="addCoauthorsBtn">Add</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3" id="coauthorsList">
                                <div class="col">
                                    <table class="table table-bordered" id="coauthorsTable">
                                        <thead>
                                            <tr>
                                                <th>Last Name</th>
                                                <th>First Name</th>
                                                <th>Middle Name</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            <div class="row mt-4">
                                <div class="col">
                                    <label for="inputEmail" class="form-label">Email</label>
                                    <input type="text" name="inputEmail" id="inputEmail" class="form-control" required placeholder="Enter your email...">
                                    <!-- note -->
                                    <br>
                                    <figcaption class="blockquote-footer d-flex align-items-end">(Note: Only primary author required)</figcaption>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label for="dateStarted" class="form-label">Date Started</label>
                                    <input type="date" name="dateStarted" id="dateStarted" class="form-control" required>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <label for="dateComplete" class="form-label">Date Completed</label>
                                    <input type="date" name="dateComplete" id="dateComplete" class="form-control" required>
                                </div>
                            </div>
                            <div class="row mt-5 mb-5">
                                <div class="col">
                                    <label for="researchStatus">Research Status</label>
                                    <select name="researchStatus" id="researchStatus" class="form-select" required>
                                        <option value="completed">Completed</option>
                                        <option value="incomplete">Incomplete</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row mt-3">
                                <div class="col">
                                    <label for="researchUpload" class="form-label">Upload File</label>
                                    <input type="file" name="researchUpload" id="researchUpload" class="form-control" required>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <label for="researchDescription" class="form-label">Research Description/Abstract</label>
                                    <textarea name="researchDescription" id="researchDescription" class="form-control" style="height: 400px;" required></textarea>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <!-- EXTRA HERE -->
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <!-- ANOTHER EXTRA -->
                                </div>
                            </div>
                            <div class="row mt-5 mb-3">
                                <div class="col d-flex justify-content-end">
                                    <button type="submit" name="submitResearch" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let coauthors = []; // global array

        $(document).ready(function() {
            const coAuthButton = $('#coAuthorBtn');
            $('#coAuthorsModal').hide();

            // Make updateCoauthorsList globally accessible
            window.updateCoauthorsList = function() {
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

            window.removeCoauthor = function(idx) {
                coauthors.splice(idx, 1);
                updateCoauthorsList();
            }

            // Load modal content
            $('#coAuthorsModal').load("../phpFunctions/addCoAuthor.php", function() {
                // Delegate click event to dynamically added employee rows
                $('#coAuthorsModal').on('click', '.employeeRow', function() {
                    const fname = $(this).data('fname');
                    const mname = $(this).data('mname');
                    const lname = $(this).data('lname');

                    console.log("Clicked:", fname, mname, lname);

                    // prevent duplicates
                    if (!coauthors.some(c => c.lname === lname && c.fname === fname)) {
                        coauthors.push({
                            lname,
                            fname,
                            mname
                        });
                        updateCoauthorsList(); // now works globally
                    }
                });

                // Close modal button inside loaded content
                $('#closeCoAuthorModal').on('click', function() {
                    $('#coAuthorsModal').hide();
                    coAuthButton.text("Add Co-Authors");
                });
            });

            // Toggle modal
            coAuthButton.on("click", function() {
                $('#coAuthorsModal').toggle();
                const isVisible = $('#coAuthorsModal').is(":visible");
                coAuthButton.text(isVisible ? ">>>" : "Add Co-Authors");
            });

            // Click outside to close
            $('body').on("click", function(e) {
                if (!$(e.target).closest('#coAuthorsModal, #coAuthorBtn').length && $('#coAuthorsModal').is(':visible')) {
                    $('#coAuthorsModal').hide();
                    coAuthButton.text("Add Co-Authors");
                }
            });
        });
    </script>

</body>

</html>