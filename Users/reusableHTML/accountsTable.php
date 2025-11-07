<?php
require_once '../../phpFunctions/gad_portal.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die("Unauthorized access");
}

$con = newCon();
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// ✅ Fetch fullname, username, position, and hashed password
$sql = "
    SELECT 
        id,
        CONCAT(fname, ' ', lname) AS fullname,
        username,
        position,
        pass AS password
    FROM accounts_tbl
";
$result = $con->query($sql);
?>

<div class="table-responsive">
    <table class="table table-striped table-sm" id="accountsTable">
        <thead class="thead-dark">
            <tr>
                <th>Full Name</th>
                <th>Username</th>
                <th>Position</th>
                <th>Password</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['position']) ?></td>
                        <td><?= htmlspecialchars($row['password']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center text-muted">No accounts found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $con->close(); ?>