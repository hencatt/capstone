<?php
require_once 'includes.php';
session_start();
checkUser($_SESSION['user_id']);

$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];
$currentFname = $user['fname'];
$currentLname = $user['lname'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php headerLinks("Research Gallery") ?>
    <style>
        .research-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .research-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important;
        }

        .research-card {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .pdf-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .pdf-modal-box {
            width: 80%;
            height: 85%;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
        }

        .pdf-close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            border: none;
            background: transparent;
            font-size: 28px;
            cursor: pointer;
            z-index: 10;
            color: #333;
            font-weight: bold;
        }

        .pdf-close-btn:hover {
            color: #ff0000;
        }

        .pdf-viewer-frame {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>

<body>
    <div class="row everything">
        <div class="col sidebar">
            <?php sidebar("researchGallery", $currentPosition) ?>
        </div>

        <!-- Main Contents -->
        <div class="col-10 mt-3 mainContent">
            <?php topbar($currentUser, $currentPosition, "researchGallery") ?>

            <div id="contents">
                <div class="row">
                    <div class="col">
                        <h1>Research Gallery</h1>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col">
                        Explore all researches categorized based on their current voting results
                        (<b style="color: green">Approved</b>, <b style="color: red">Rejected</b>, <b style="color: #ff9d09ff">Pending</b>).
                    </div>
                </div>

                <!-- TOGGLE START -->
                <div class="row mt-4">
                    <div class="col">
                        <div class="btn-group btn-group-toggle" data-toggle="buttons" id="toggleGroup">
                            <label class="btn btn-secondary active" data-value="Approved">
                                <input type="radio" name="toggleOptions" value="Approved" autocomplete="off" checked>
                                Approved
                            </label>
                            <label class="btn btn-secondary" data-value="Rejected">
                                <input type="radio" name="toggleOptions" value="Rejected" autocomplete="off"> Rejected
                            </label>
                            <label class="btn btn-secondary" data-value="Pending">
                                <input type="radio" name="toggleOptions" value="Pending" autocomplete="off"> Pending
                            </label>
                        </div>
                    </div>
                </div>
                <!-- TOGGLE END -->

                <?php
                $con = con();

                $sql = "
    SELECT r.*, 
           SUM(v.vote = 'Approve') AS approve_count,
           SUM(v.vote = 'Reject') AS reject_count
    FROM research_tbl r
    LEFT JOIN votes_tbl v ON r.id = v.research_id
    GROUP BY r.id
    ORDER BY r.date_submitted DESC
";

                $result = $con->query($sql);
                $approvedResearches = [];
                $rejectedResearches = [];
                $pendingResearches = [];

                while ($row = $result->fetch_assoc()) {
                    $approve = (int) $row['approve_count'];
                    $reject = (int) $row['reject_count'];
                    $totalVotes = $approve + $reject;

                    if ($totalVotes > 1) {
                        if ($approve > $reject) {
                            $approvedResearches[] = $row;
                        } elseif ($reject > $approve) {
                            $rejectedResearches[] = $row;
                        } else {
                            $pendingResearches[] = $row;
                        }
                    } else {
                        $pendingResearches[] = $row;
                    }
                }

                // Helper to render cards to string
                function renderResearchCardsToString($data)
                {
                    if (empty($data)) {
                        return '<div class="col"><div class="alert alert-info"><i class="fas fa-info-circle"></i> No research records found in this category.</div></div>';
                    }

                    $html = '';
                    foreach ($data as $row) {
                        $researchId = htmlspecialchars($row['id']);
                        $title = htmlspecialchars($row['research_title']);
                        $date = htmlspecialchars($row['date_submitted']);
                        $author = htmlspecialchars($row['author']);
                        $co = $row['co_author'] ? ', ' . htmlspecialchars($row['co_author']) : '';
                        $agenda = htmlspecialchars($row['research_agenda']);
                        $sdg = htmlspecialchars($row['research_sdg']);
                        $desc = htmlspecialchars($row['description']);

                        // Truncate description if too long
                        $descShort = strlen($desc) > 200 ? substr($desc, 0, 200) . '...' : $desc;

                        $card = <<<HTML
<div class="col d-flex flex-column gap-3 research-card"
     style="background-color:white; padding:20px; border-radius:10px; 
            max-width:400px; min-height:500px; max-height:500px;">
    <div style="max-height:400px;">
        <div>
            <h5>{$title}</h5>
            <i class="blockquote-footer"><i class="fas fa-calendar"></i> Date Submitted: {$date}</i>
        </div>
        <hr>
        <div>
            <i><b><i class="fas fa-user"></i> Author/s:</b> {$author}{$co}</i><br>
            <i><b><i class="fas fa-lightbulb"></i> NEUST Agenda:</b> {$agenda}</i><br>
            <i><b><i class="fas fa-leaf"></i> SDG:</b> {$sdg}</i>
        </div>
        <hr>
        <div style="max-height:200px; overflow-y:auto;">
            {$descShort}
        </div>
    </div>
    <div class="d-flex gap-2 mt-auto">
        <button class="btn btn-outline-primary flex-grow-1 view-pdf-btn" data-research-id="{$researchId}">
            <i class="fas fa-file-pdf"></i> View PDF
        </button>
        <a href="researchDetails.php?id={$researchId}&prev=Gallery" class="btn btn-outline-info">
            <i class="fas fa-eye"></i> Details
        </a>
    </div>
</div>
HTML;
                        $html .= $card;
                    }
                    return $html;
                }

                $approvedHtml = renderResearchCardsToString($approvedResearches);
                $rejectedHtml = renderResearchCardsToString($rejectedResearches);
                $pendingHtml = renderResearchCardsToString($pendingResearches);
                ?>

                <!-- Hidden templates -->
                <div id="template_approved" style="display:none;">
                    <div class="row mt-2 d-flex flex-row gap-3" style="overflow-x:auto;">
                        <?= $approvedHtml ?>
                    </div>
                </div>

                <div id="template_rejected" style="display:none;">
                    <div class="row mt-2 d-flex flex-row gap-3" style="overflow-x:auto;">
                        <?= $rejectedHtml ?>
                    </div>
                </div>

                <div id="template_pending" style="display:none;">
                    <div class="row mt-2 d-flex flex-row gap-3" style="overflow-x:auto;">
                        <?= $pendingHtml ?>
                    </div>
                </div>

                <!-- Visible single container where cards will appear -->
                <div id="cards_container" class="mt-3">
                    <!-- Default content: Approved -->
                    <div class="row mt-2 d-flex flex-row gap-3" style="overflow-x:auto;">
                        <?= $approvedHtml ?>
                    </div>
                </div>

                <!-- PDF Modal Overlay -->
                <div id="pdfModalOverlay" class="pdf-modal-overlay">
                    <div class="pdf-modal-box">
                        <button class="pdf-close-btn" id="closePdfModal">&times;</button>
                        <div id="pdfViewerContainer" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Toggle script: copies template into #cards_container and handles active button -->
    <script defer>
        document.addEventListener("DOMContentLoaded", () => {
            const group = document.getElementById('toggleGroup');
            const radios = Array.from(document.querySelectorAll('input[name="toggleOptions"]'));
            const cardsContainer = document.getElementById('cards_container');
            const templates = {
                "Approved": document.getElementById('template_approved'),
                "Rejected": document.getElementById('template_rejected'),
                "Pending": document.getElementById('template_pending')
            };

            function setActiveButton(value) {
                const labels = group.querySelectorAll('label');
                labels.forEach(lbl => {
                    if (lbl.dataset.value === value) {
                        lbl.classList.add('active');
                    } else {
                        lbl.classList.remove('active');
                    }
                });
            }

            function showCategory(value) {
                const t = templates[value];
                if (!t) return;
                cardsContainer.innerHTML = t.innerHTML;
                setActiveButton(value);
                attachPdfViewers();
            }

            radios.forEach(radio => {
                radio.addEventListener('change', () => {
                    if (radio.checked) showCategory(radio.value);
                });
                const label = radio.closest('label');
                if (label) {
                    if (!label.dataset.value) label.dataset.value = radio.value;
                    label.addEventListener('click', () => {
                        setTimeout(() => {
                            if (radio.checked) showCategory(radio.value);
                        }, 10);
                    });
                }
            });

            // PDF Viewer Functionality
            const pdfModalOverlay = document.getElementById('pdfModalOverlay');
            const closePdfModal = document.getElementById('closePdfModal');
            const pdfViewerContainer = document.getElementById('pdfViewerContainer');

            function attachPdfViewers() {
                const viewPdfBtns = document.querySelectorAll('.view-pdf-btn');
                viewPdfBtns.forEach(btn => {
                    btn.addEventListener('click', function () {
                        const researchId = this.getAttribute('data-research-id');
                        openPdfModal(researchId);
                    });
                });
            }

            function openPdfModal(researchId) {
                // Show loading state
                pdfViewerContainer.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
                pdfModalOverlay.style.display = 'flex';
                document.body.style.overflow = 'hidden';

                // Fetch PDF data via AJAX with better error handling
                fetch('../phpFunctions/getPdfData.php?id=' + encodeURIComponent(researchId))
                    .then(response => {
                        // Check if response is ok
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.text(); // Get as text first to debug
                    })
                    .then(text => {
                        console.log('Response received:', text.substring(0, 200)); // Debug log
                        try {
                            const data = JSON.parse(text);
                            if (data.success && data.pdfBase64) {
                                displayPdf(data.pdfBase64);
                            } else {
                                const errorMsg = data.error || 'PDF file not found';
                                console.error('PDF Error:', errorMsg);
                                pdfViewerContainer.innerHTML = `<div style="padding: 20px; text-align: center;"><p class="text-danger">${errorMsg}</p></div>`;
                            }
                        } catch (parseError) {
                            console.error('JSON Parse Error:', parseError);
                            console.error('Response text:', text);
                            pdfViewerContainer.innerHTML = '<div style="padding: 20px; text-align: center;"><p class="text-danger">Invalid response from server. Check console for details.</p></div>';
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        pdfViewerContainer.innerHTML = `<div style="padding: 20px; text-align: center;"><p class="text-danger">Error loading PDF: ${error.message}</p></div>`;
                    });
            }

            function displayPdf(base64Data) {
                try {
                    // Remove any whitespace or data URI prefix if present
                    base64Data = base64Data.replace(/\s/g, '');
                    if (base64Data.includes('base64,')) {
                        base64Data = base64Data.split('base64,')[1];
                    }

                    // Validate base64 string
                    if (!base64Data || base64Data.length === 0) {
                        throw new Error('Empty base64 data');
                    }

                    // Check if it's valid base64
                    const base64Regex = /^[A-Za-z0-9+/]+={0,2}$/;
                    if (!base64Regex.test(base64Data)) {
                        console.error('Invalid base64 characters detected');
                        throw new Error('Invalid PDF data format');
                    }

                    console.log('Base64 length:', base64Data.length);
                    console.log('First 50 chars:', base64Data.substring(0, 50));

                    // Decode base64 → binary
                    const byteCharacters = atob(base64Data);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);

                    // Verify it's actually a PDF (check magic number)
                    if (byteArray[0] !== 0x25 || byteArray[1] !== 0x50 ||
                        byteArray[2] !== 0x44 || byteArray[3] !== 0x46) {
                        console.error('Not a valid PDF file (magic number check failed)');
                        throw new Error('File is not a valid PDF');
                    }

                    const blob = new Blob([byteArray], { type: "application/pdf" });
                    const pdfURL = URL.createObjectURL(blob);

                    console.log('PDF Blob created successfully, size:', blob.size);

                    // Create iframe instead of object for better compatibility
                    const pdfViewer = document.createElement("iframe");
                    pdfViewer.src = pdfURL;
                    pdfViewer.width = "100%";
                    pdfViewer.height = "100%";
                    pdfViewer.style.border = "none";
                    pdfViewer.className = "pdf-viewer-frame";

                    pdfViewerContainer.innerHTML = '';
                    pdfViewerContainer.appendChild(pdfViewer);

                    // Store URL for cleanup
                    pdfModalOverlay.dataset.pdfUrl = pdfURL;
                } catch (error) {
                    console.error('PDF Display Error:', error);
                    console.error('Error stack:', error.stack);
                    pdfViewerContainer.innerHTML = `<div style="padding: 20px; text-align: center;">
                <p class="text-danger">Error displaying PDF: ${error.message}</p>
                <p class="text-muted">Please check the browser console for more details.</p>
            </div>`;
                }
            }

            function closePdfModalHandler() {
                pdfModalOverlay.style.display = 'none';
                document.body.style.overflow = 'auto';

                // Cleanup PDF URL
                if (pdfModalOverlay.dataset.pdfUrl) {
                    URL.revokeObjectURL(pdfModalOverlay.dataset.pdfUrl);
                    delete pdfModalOverlay.dataset.pdfUrl;
                }

                pdfViewerContainer.innerHTML = '';
            }

            closePdfModal.addEventListener('click', closePdfModalHandler);

            // Close on overlay click
            pdfModalOverlay.addEventListener('click', (e) => {
                if (e.target === pdfModalOverlay) {
                    closePdfModalHandler();
                }
            });

            // Close on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && pdfModalOverlay.style.display === 'flex') {
                    closePdfModalHandler();
                }
            });

            // Initial attachment
            attachPdfViewers();

            // Default view
            showCategory('Approved');
        });
    </script>
</body>

</html>