const editButton = document.querySelectorAll(".editBtn");
const closeEditModalBtn = document.querySelectorAll(".closeEditModal");
const editModal = document.getElementById("editModal");

const deleteButton = document.querySelectorAll(".deleteBtn");
const cancelButton = document.querySelectorAll(".cancelBtn");

// Open modal
document.querySelectorAll(".editBtn").forEach(btn => {
  btn.addEventListener("click", () => {
    const targetId = btn.getAttribute("data-target");
    const modal = document.getElementById(targetId);
    modal.classList.add("open");
  });
});

// Close modal
document.querySelectorAll(".closeEditModal").forEach(btn => {
  btn.addEventListener("click", () => {
    const modal = btn.closest(".editModal");
    modal.classList.remove("open");
  });
});

// Open Delete Modal
deleteButton.forEach(btn => {
  btn.addEventListener("click", () => {
    const targetId = btn.getAttribute("data-target");
    const modal = document.getElementById(targetId);
    modal.classList.add("open");
  });
});

// Cancel Delete Modal
cancelButton.forEach(btn => {
  btn.addEventListener("click", () => {
    const modal = btn.closest(".deleteModal");
    modal.classList.remove("open");
  })
});