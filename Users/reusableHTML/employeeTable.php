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
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="employeeTableBody">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="empName"><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['department']) ?></td>
                        <td><?= htmlspecialchars($row['campus']) ?></td>
                        <td class="empEmail"><?= htmlspecialchars($row['email']) ?></td>
                        <td>
                            <?php if ($row['account_id']): ?>
                                <span class="badge bg-success">Has Account</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">No Account</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" 
                                        class="btn btn-outline-primary view-btn"
                                        data-id="<?= htmlspecialchars($row['emp_id']) ?>"
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <button type="button"
                                        class="btn btn-outline-success editEmployeeBtn"
                                        data-id="<?= htmlspecialchars($row['emp_id']) ?>"
                                        title="Edit Employee">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <?php if (!$row['account_id'] && ($currentPosition === 'Director' || $currentPosition === 'Technical Assistant')): ?>
                                    <button type="button"
                                            class="btn btn-outline-info assignBtn"
                                            data-id="<?= htmlspecialchars($row['emp_id']) ?>"
                                            title="Assign Account">
                                        <i class="fas fa-user-plus"></i> Assign
                                    </button>
                                <?php endif; ?>

                                <button type="button"
                                        class="btn btn-outline-danger delete-btn"
                                        data-id="<?= htmlspecialchars($row['emp_id']) ?>"
                                        title="Delete Employee">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
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



<script>
$(document).ready(function() {
    // 🔹 View Employee Info
    $(document).on('click', '.view-btn', function() {
        const id = $(this).data('id');
        if (!id) return alert('Employee ID missing.');

        $.post('../../phpFunction/getEmployeeDetails.php', { id: id }, function(resp) {
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
        }, 'json').fail(() => alert('Request failed 1.'));
    });

    // 🔹 Edit Employee Info
    $(document).on('click', '.editEmployeeBtn', function() {
    const id = $(this).data('id');
    $('#id').val(id); // this part is fine if #id exists

    $.post('../phpFunctions/getEmployeeDetails.php', { id: id }, function(resp) {
        console.log(resp);
            if (!resp || resp.error) {
                alert(resp?.error || 'Unable to fetch employee info.');
                return;
            }

            $(function () {
            const genderSelect = $("#inputGender");
            const otherGender = $("#otherGender");
            otherGender.hide();

            genderSelect.on("change", function () {
                if ($(this).val() === "LGBTQIA+") {
                    otherGender.show();
                } else {
                    otherGender.val("");
                    otherGender.hide();
                }
            });

            function toggleChildOptions() {
                const checkedChild = $('input[name="inputChildren"]:checked').val();
                if (checkedChild === "No") {
                    $("#childrenNum").val("");
                    $("#childrenNumCol").hide();
                    $("#childConcern").val("");
                    $("#childConcernCol").hide();
                } else {
                    $("#childrenNumCol").show();
                    $("#childConcernCol").show();
                }
            }

            toggleChildOptions();
            $('input[name="inputChildren"]').on('change', function () {
                toggleChildOptions();
            });
        });

            // Fill modal fields
            $('#inputFname').val(resp.fname);
            $('#inputMname').val(resp.m_initial);
            $('#inputLname').val(resp.lname);
            $('#inputEmail').val(resp.email);
            $('#inputContact').val(resp.contact_no);
            $('#inputDepartment').val(resp.department);
            $('#inputCampus').val(resp.campus);
            $('#inputBirthdate').val(resp.birthday);
            $('#inputPriority').val(resp.priority_status);
            $('#inputStAddress').val(resp.address);
            $('#inputCity').val(resp.city);
            $('#inputProvince').val(resp.province);
            $('#inputMaritalStatus').val(resp.marital_status);
            $('#inputSize').val(resp.size);
            $('#inputSex').val(resp.sex);
            $('#inputGender').val(resp.gender);
            $('#inputIncome').val(resp.income);
            $('#inputChildrenNum').val(resp.children_num);
            $('#inputConcern').val(resp.concern);

            // 🔸 Show modal (same as Add Employee)
            const modal = document.getElementById('modal');
            if (modal) {
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        }, 'json').fail(() => alert('Request failed 2.'));
    });

    // 🔹 Close modal
    $(document).on('click', '.close-btn, #cancelInfo', function() {
        const modal = document.getElementById('modal');
        if (modal) {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }
    });

    // 🔹 Delete Employee
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        if (!id) return;
        if (!confirm('Are you sure you want to delete this record?')) return;

        $.post('../phpFunctions/deleteEmployee.php', { id }, function(resp) {
            if (resp && resp.success) {
                alert(resp.message);
                $(`button.delete-btn[data-id='${id}']`).closest('tr').fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                alert(resp?.error || 'Failed to delete record.');
            }
        }, 'json').fail(() => alert('Delete request  3.'));
    });

    window.openEditModal = function(employee) {
    $('#modal').addClass('open');
    document.body.style.overflow = 'hidden';

    // Fill fields dynamically
    $('#emp_id').val(employee.id);
    $('#inputFname').val(employee.fname);
    $('#inputMname').val(employee.mname);
    $('#inputLname').val(employee.lname);
    $('#inputEmail').val(employee.email);
    $('#inputContact').val(employee.contact);
    $('#inputDepartment').val(employee.department);
    $('#inputCampus').val(employee.campus);
    $('#inputBirthdate').val(employee.birthdate);
    $('#inputPriority').val(employee.priority);
    $('#inputStAddress').val(employee.stAddress);
    $('#inputCity').val(employee.city);
    $('#inputProvince').val(employee.province);
    $('#inputSex').val(employee.sex);
    $('#inputGender').val(employee.gender);
    $('#inputIncome').val(employee.income);

    // Handle other gender field
    if(employee.genderOther) {
      $('#inputGender').val('LGBTQIA+');
      $('#otherGender').val(employee.genderOther).show();
    } else {
      $('#otherGender').hide();
    }
  };

  // ------------------------------
  // Close modal
  // ------------------------------
  $('#closeModal, #cancelInfo').click(function() {
    $('#modal').removeClass('open');
    document.body.style.overflow = '';
  });

  // ------------------------------
  // Show/hide "other gender"
  // ------------------------------
  $('#inputGender').change(function() {
    if($(this).val() === 'LGBTQIA+') {
      $('#otherGender').show();
    } else {
      $('#otherGender').hide().val('');
    }
  });

  // ------------------------------
  // AJAX Save
  // ------------------------------
  $('#employeeForm').submit(function(e) {
    e.preventDefault();
    const formData = $(this).serialize();

    $.post('../phpFunctions/updateEmployee.php', formData, function(resp) {
      if(resp.success) {
        alert(resp.message);
        $('#modal').removeClass('open');
        document.body.style.overflow = '';
        location.reload(); // refresh to show updated info
      } else {
        alert(resp.error || 'Failed to update employee.');
      }
    }, 'json').fail(() => alert('Request failed'));
  });

   $(document).on('click', '#saveInfo', function(e) {
    e.preventDefault();

    const formData = $('#modal form').serialize(); // gets all form inputs including hidden emp_id

    $.post('../phpFunctions/updateEmployee.php', formData, function(resp) {
        if (resp.success) {
            alert(resp.message);
            $('#modal').removeClass('open'); // or use Bootstrap: $('#modal').modal('hide');
            document.body.style.overflow = '';
            location.reload(); // refresh to see changes
        } else {
            alert(resp.error || 'Failed to update employee.');
        }
    }, 'json').fail(() => alert('Request failed'));
});

});
</script>

