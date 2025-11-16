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
            <div class="row mt-4">
                <div class="col">
                    <h1>Research Gallery</h1>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col">
                    Explore all researches categorized based on their current voting results 
                    (<b>Approved</b>, <b>Rejected</b>, <b>Pending</b>).
                </div>
            </div>

            <!-- TOGGLE START -->
    <div class="row mt-4">
        <div class="col">
            <div class="btn-group btn-group-toggle" data-toggle="buttons" id="toggleGroup">
                <label class="btn btn-secondary active" data-value="Approved">
                    <input type="radio" name="toggleOptions" value="Approved" autocomplete="off" checked> Approved
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
function renderResearchCardsToString($data) {
    if (empty($data)) {
        return '<p>No research records found in this category.</p>';
    }

    $html = '';
    foreach ($data as $row) {
        $title = htmlspecialchars($row['research_title']);
        $date = htmlspecialchars($row['date_submitted']);
        $author = htmlspecialchars($row['author']);
        $co = $row['co_author'] ? ', ' . htmlspecialchars($row['co_author']) : '';
        $agenda = htmlspecialchars($row['research_agenda']);
        $sdg = htmlspecialchars($row['research_sdg']);
        $desc = htmlspecialchars($row['description']);

        $card = <<<HTML
<div class="col d-flex flex-column gap-3 research-card"
     style="background-color:white; padding:20px; border-radius:10px; 
            max-width:400px; min-height:500px; max-height:500px;">
    <div style="max-height:400px;">
        <div>
            <h5>{$title}</h5>
            <i class="blockquote-footer">Date Submitted: {$date}</i>
        </div>
        <hr>
        <div>
            <i><b>Author/s:</b> {$author}{$co}</i><br>
            <i><b>NEUST Agenda:</b> {$agenda}</i><br>
            <i><b>SDG:</b> {$sdg}</i>
        </div>
        <hr>
        <div style="max-height:200px; overflow-y:auto;">
            {$desc}
        </div>
    </div>
    <button class="btn btn-outline-primary mt-auto align-self-end">View PDF</button>
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

    // default
    showCategory('Approved');
});
</script>
