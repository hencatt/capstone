console.log('script.js is loaded');
document.addEventListener('DOMContentLoaded', () => {
    // Add Account Modal
    const addAccountButton = document.querySelector('#add_account');
    const addAccountModal = document.querySelector('#add_account_modal');
    const closeAddAccountButton = document.querySelector('#close_add_account');

    if (addAccountButton) {
        addAccountButton.addEventListener('click', () => {
            addAccountModal.style.display = 'flex';
        });
    }

    if (closeAddAccountButton) {
        closeAddAccountButton.addEventListener('click', () => {
            addAccountModal.style.display = 'none';
        });
    }

    // View All Modal
    const viewAllButton = document.querySelector('#view_all');
    const viewAllModal = document.querySelector('#view_all_modal');
    const closeViewAllButton = document.querySelector('#close_view_all');

    if (viewAllButton) {
        viewAllButton.addEventListener('click', () => {
            viewAllModal.style.display = 'flex';
        });
    }

    if (closeViewAllButton) {
        closeViewAllButton.addEventListener('click', () => {
            viewAllModal.style.display = 'none';
        });
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const deactivateIcons = document.querySelectorAll('.deactivate-icon');

    deactivateIcons.forEach(icon => {
        icon.addEventListener('click', () => {
            const userId = icon.getAttribute('data-id');
            const userRow = icon.closest('tr'); // Get the table row containing the icon

            if (confirm('Are you sure you want to deactivate this user?')) {
                fetch('../deactivate_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${userId}`,
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);

                    // Remove the row from the table if the deactivation is successful
                    if (data.includes('User deactivated successfully')) {
                        userRow.remove();
                    } else {
                        alert('Failed to deactivate the user.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deactivating the user.');
                });
            }
        });
    });
});


document.addEventListener('DOMContentLoaded', () => {
    const editIcons = document.querySelectorAll('.edit-icon');
    const editModal = document.querySelector('#edit_account_modal');
    const closeEditModalButton = document.querySelector('#close_edit_account');

    const editIdInput = document.querySelector('#edit_id');
    const editFnameInput = document.querySelector('#edit_fname');
    const editLnameInput = document.querySelector('#edit_lname');
    const editUsernameInput = document.querySelector('#edit_username');
    const editEmailInput = document.querySelector('#edit_email');
    const editPasswordInput = document.querySelector('#edit_password');
    const editPositionSelect = document.querySelector('#edit_position');
    const editDepartmentSelect = document.querySelector('#edit_department');
    const editCampusSelect = document.querySelector('#edit_campus');

    // Open the modal and populate fields
    editIcons.forEach(icon => {
        icon.addEventListener('click', () => {
            const userId = icon.getAttribute('data-id');

            // Fetch user details via AJAX
            fetch(`../get_user_details.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    // Populate the modal fields
                    editIdInput.value = data.id;
                    editFnameInput.value = data.fname;
                    editLnameInput.value = data.lname;
                    editUsernameInput.value = data.username;
                    editEmailInput.value = data.email;
                    editPasswordInput.value = ''; // Leave password empty for security
                    editPositionSelect.value = data.position;
                    editDepartmentSelect.value = data.department;
                    editCampusSelect.value = data.campus;

                    // Show the modal
                    editModal.style.display = 'flex';
                })
                .catch(error => console.error('Error fetching user details:', error));
        });
    });

    // Close the modal
    if (closeEditModalButton) {
        closeEditModalButton.addEventListener('click', () => {
            editModal.style.display = 'none';
        });
    }
});