const addAccountButton = document.querySelector('#add_account'); // Button to open the modal
const modalAddAccount = document.querySelector('.modals'); // Modal container
const closeModalButton = document.querySelector('.add_btn_close'); // Button to close the modal

// Show the modal when the "Add Account" button is clicked
if (addAccountButton) {
    addAccountButton.addEventListener('click', () => {
        modalAddAccount.style.display = 'flex'; // Show the modal
    });
}

// Hide the modal when the "Close" button is clicked
if (closeModalButton) {
    closeModalButton.addEventListener('click', () => {
        modalAddAccount.style.display = 'none'; // Hide the modal
    });
}
