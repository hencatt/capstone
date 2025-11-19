<?php
header('Content-Type: application/json');

// Include your database connection
require_once './gad_portal.php'; // Adjust path as needed

// Get research ID from query parameter
$researchId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($researchId <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid research ID'
    ]);
    exit;
}

try {
    $con = con(); // Your database connection function

    // Get the file path from database - column name is 'file' based on researchDetails.php
    $stmt = $con->prepare("SELECT file FROM research_tbl WHERE id = ?");
    $stmt->bind_param("i", $researchId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $filePath = $row['file'];

        if (empty($filePath)) {
            echo json_encode([
                'success' => false,
                'error' => 'File path is empty in database'
            ]);
            exit;
        }

        // File path from DB is relative to /Users/ folder
        // Since getPdfData.php is in /phpFunctions/, we need to go ../Users/

        // Normalize path separators for Windows
        $filePath = str_replace('\\', '/', $filePath);

        // Build the actual file path
        $actualFilePath = realpath(__DIR__ . '/../Users/' . $filePath);

        // If realpath fails, try without it
        if ($actualFilePath === false) {
            $actualFilePath = __DIR__ . '/../Users/' . $filePath;
        }

        if (!file_exists($actualFilePath)) {
            echo json_encode([
                'success' => false,
                'error' => 'PDF file not found',
                'tried_path' => $actualFilePath,
                'db_path' => $filePath,
                'current_dir' => __DIR__,
                'constructed_path' => __DIR__ . '/../Users/' . $filePath
            ]);
            exit;
        }

        // Read the actual PDF file content
        $fileContent = file_get_contents($actualFilePath);

        if ($fileContent === false) {
            echo json_encode([
                'success' => false,
                'error' => 'Could not read PDF file'
            ]);
            exit;
        }

        // Encode the actual PDF content to base64
        $pdfBase64 = base64_encode($fileContent);

        echo json_encode([
            'success' => true,
            'pdfBase64' => $pdfBase64
        ]);

    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Research not found with ID: ' . $researchId
        ]);
    }

    $stmt->close();
    $con->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>