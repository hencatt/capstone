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

$sql = "
    SELECT 
        id,
        fname,
        lname,
        username,
        position,
        department,
        campus,
        email,
        pass AS password
    FROM accounts_tbl
    WHERE is_active = 1
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
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['fname'] . ' ' . $row['lname']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['position']) ?></td>
                        <td><?= htmlspecialchars($row['password']) ?></td>
                        <td>
                            <button type="button"
                                    class="btn btn-outline-success btn-sm editAccountBtn"
                                    data-id="<?= htmlspecialchars($row['id']) ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>

                            <button type="button"
                                    class="btn btn-outline-danger btn-sm deleteAccountBtn"
                                    data-id="<?= htmlspecialchars($row['id']) ?>">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">No accounts found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $con->close(); ?>

<script>
$(document).on('click', '.editAccountBtn', function() {
    const id = $(this).data('id');
    $('#acc_id').val(id); // ✅ Correct hidden input ID

    $.post('../phpFunctions/getAccountDetails.php', { id }, function(resp) {
        if (!resp || resp.error) {
            alert(resp?.error || 'Unable to fetch account info.');
            return;
        }

        // ✅ Match your modal field IDs (with "acc_")
        $('#acc_username').val(resp.username);
        $('#acc_fname').val(resp.fname);
        $('#acc_lname').val(resp.lname);
        $('#acc_email').val(resp.email);
        $('#acc_position').val(resp.position);
        $('#acc_department').val(resp.department);
        $('#acc_campus').val(resp.campus);
        $('#acc_password').val(''); // leave blank

        // ✅ Show the correct modal
        $('#editAccountModal').modal('show');
    }, 'json').fail(() => alert('Failed to load account info.'));
});
</script>

<script>
    $(document).on('click', '.deleteAccountBtn', function() {
    const id = $(this).data('id');

    if (!confirm('Are you sure you want to deactivate this account?')) return;

    $.post('../phpFunctions/deleteAccount.php', { id }, function(resp) {
        if (resp.success) {
            alert(resp.message);
            // Reload or remove row without refresh
            $(`button[data-id="${id}"]`).closest('tr').fadeOut(400, function() {
                $(this).remove();
            });
        } else {
            alert(resp.error || 'Failed to deactivate account.');
        }
    }, 'json').fail(() => alert('Request failed while deactivating account.'));
});
</script>