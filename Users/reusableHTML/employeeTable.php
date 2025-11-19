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

// Get current user's position to determine what actions to show
$currentPosition = $_SESSION['user_position'] ?? '';

$sql = "
    SELECT 
        ei.employee_id AS emp_id,
        CONCAT(ei.fname, ' ', ei.lname) AS full_name,
        et.department,
        et.campus,
        et.email,
        at.id AS account_id
    FROM employee_info ei
    INNER JOIN employee_tbl et ON ei.employee_id = et.id
    LEFT JOIN accounts_tbl at ON et.id = at.id
    WHERE et.status = 'active'
";
$result = $con->query($sql);
?>

<div class="table-responsive">
    <table class="table table-striped table-sm" id="employeeTable">
        <thead class="thead-dark">
            <tr>
                <th>Full Name</th>
                <th>Department</th>
                <th>Campus</th>
            </tr>
        </thead>
        <tbody id="employeeTableBody">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="empName"><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['department']) ?></td>
                        <td><?= htmlspecialchars($row['campus']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center text-muted">No data available</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $con->close(); ?>

<script>
$(document).ready(function() {
    // View Employee Info
    $(document).on('click', '.view-btn', function() {
        const id = $(this).data('id');
        if (!id) return alert('Employee ID missing.');

        $.post('../../phpFunctions/getEmployeeDetails.php', { id: id }, function(resp) {
            if (!resp || resp.error) {
                alert(resp?.error || 'Unable to fetch details.');
                return;
            }

            $('#v_full_name').text(`${resp.fname || ''} ${resp.m_initial ? resp.m_initial + '. ' : ''}${resp.lname || ''}`);
            $('#v_email').text(resp.email || '');
            $('#v_contact').text(resp.contact_no || '');
            $('#v_department').text(resp.department || '');
            $('#v_campus').text(resp.campus || '');
            $('#v_address').text(resp.address || '');
            $('#v_birthday').text(resp.birthday || '');
            $('#v_marital_status').text(resp.marital_status || '');
            $('#v_sex').text(resp.sex || '');
            $('#v_gender').text(resp.gender || '');
            $('#v_priority_status').text(resp.priority_status || '');
            $('#v_size').text(resp.size || '');
            $('#v_income').text(resp.income || '');
            $('#v_children_num').text(resp.children_num || '');
            $('#v_concern').text(resp.concern || '');
        }, 'json').fail(() => alert('Request failed.'));
    });

    // Edit Employee Info
    $(document).on('click', '.editEmployeeBtn', function() {
        const id = $(this).data('id');
        $('#emp_id').val(id);

        $.post('../phpFunctions/getEmployeeDetails.php', { id: id }, function(resp) {
            if (!resp || resp.error) {
                alert(resp?.error || 'Unable to fetch employee info.');
                return;
            }

            // Fill modal fields
            $('#fname').val(resp.fname);
            $('#m_initial').val(resp.m_initial);
            $('#lname').val(resp.lname);
            $('#email').val(resp.email);
            $('#contact_no').val(resp.contact_no);
            $('#department').val(resp.department);
            $('#campus').val(resp.campus);
            $('#birthday').val(resp.birthday);
            $('#priority_status').val(resp.priority_status);
            $('#address').val(resp.address);
            $('#marital_status').val(resp.marital_status);
            $('#size').val(resp.size);
            $('#sex').val(resp.sex);
            $('#gender').val(resp.gender);
            $('#income').val(resp.income);
            $('#children_num').val(resp.children_num);
            $('#concern').val(resp.concern);

            // Show modal
            const modal = document.getElementById('modal');
            if (modal) {
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        }, 'json').fail(() => alert('Request failed.'));
    });

    // Delete Employee
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        if (!id) return;
        if (!confirm('Are you sure you want to delete this employee?')) return;

        $.post('../phpFunctions/deleteEmployee.php', { id }, function(resp) {
            if (resp && resp.success) {
                alert(resp.message);
                $(`button.delete-btn[data-id='${id}']`).closest('tr').fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                alert(resp?.error || 'Failed to delete employee.');
            }
        }, 'json').fail(() => alert('Delete request failed.'));
    });
});
</script>



