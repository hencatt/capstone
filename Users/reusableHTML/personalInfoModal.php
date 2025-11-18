<div id="modal" class="modalInfo">
  <div class="modalInfo-content" style="width: 90%; height: 80vh; overflow-y:auto;">
    <div class="row">
      <div class="col">
        <h4 id="modalTitle">Add Employee</h4>
      </div>
      <div class="col close-btn d-flex justify-content-end">
        <span class="material-symbols-outlined">close</span>
      </div>
    </div>

    <form id="employeeForm">
      <hr>
      <div class="row d-flex">
        <!-- Hidden field to track if editing or adding -->
        <input type="hidden" name="emp_id" id="emp_id" value="">

        <!-- GROUP 1 -->
        <div class="col-8">
          <div class="row">
            <div class="col">
              <label for="fname" class="form-label">First Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="fname" name="fname" placeholder="Enter First Name" required>
            </div>
            <div class="col">
              <label for="m_initial" class="form-label">Middle Initial</label>
              <input type="text" class="form-control" id="m_initial" name="m_initial" placeholder="M." maxlength="2">
            </div>
            <div class="col">
              <label for="lname" class="form-label">Last Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="lname" name="lname" placeholder="Enter Last Name" required>
            </div>
          </div>
          
          <div class="row mt-3">
            <div class="col">
              <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" id="email" class="form-control" placeholder="email@example.com" required>
            </div>
            <div class="col">
              <label for="contact_no" class="form-label">Contact No. <span class="text-danger">*</span></label>
              <input type="text" name="contact_no" id="contact_no" class="form-control" placeholder="09XX XXX XXXX" required>
            </div>
          </div>
          
          <div class="row mt-4">
            <div class="col">
              <label for="department" class="form-label">Department</label>
              <input type="text" class="form-control" name="department" id="department" 
                     value="<?= htmlspecialchars($_SESSION['user_department'] ?? '') ?>" readonly>
            </div>
            <div class="col">
              <label for="campus" class="form-label">Campus</label>
              <input type="text" class="form-control" name="campus" id="campus" 
                     value="<?= htmlspecialchars($_SESSION['user_campus'] ?? '') ?>" readonly>
            </div>
          </div>
          
          <div class="row mt-4">
            <div class="col">
              <label for="birthday" class="form-label">Birthdate <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="birthday" id="birthday" required>
            </div>
            <div class="col">
              <label for="priority_status" class="form-label">Priority Status</label>
              <select name="priority_status" id="priority_status" class="form-select">
                <option value="None">None</option>
                <option value="PWD">PWD</option>
                <option value="Senior Citizen">Senior Citizen</option>
              </select>
            </div>
          </div>
          
          <div class="row mt-3">
            <div class="col">
              <label for="address" class="form-label">Complete Address <span class="text-danger">*</span></label>
              <input type="text" name="address" id="address" 
                     placeholder="Street, Barangay, City, Province" class="form-control" required>
            </div>
          </div>
          
          <div class="row mt-4">
            <div class="col">
              <label for="marital_status" class="form-label">Marital Status <span class="text-danger">*</span></label>
              <select name="marital_status" id="marital_status" class="form-select" required>
                <option value="" disabled selected>Select Marital Status</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Widowed">Widowed</option>
              </select>
            </div>
            <div class="col">
              <label for="size" class="form-label">Clothing Size</label>
              <select name="size" id="size" class="form-select">
                <option value="" disabled selected>Select Size</option>
                <option value="S">Small</option>
                <option value="M">Medium</option>
                <option value="L">Large</option>
                <option value="XL">Extra Large</option>
                <option value="2XL">2XL</option>
                <option value="3XL">3XL</option>
                <option value="4XL">4XL</option>
              </select>
            </div>
          </div>
        </div>

        <!-- GROUP 2 -->
        <div class="col-4">
          <div class="row">
            <div class="col">
              <label for="sex" class="form-label">Sex <span class="text-danger">*</span></label>
              <select name="sex" id="sex" class="form-select" required>
                <option value="" disabled selected>Select Sex</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
          </div>
          
          <div class="row mt-4">
            <div class="col">
              <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
              <select class="form-select" id="gender" name="gender" required>
                <option value="" disabled selected>Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="LGBTQIA+">LGBTQIA+</option>
              </select>
              <input type="text" class="form-control mt-2" id="otherGender" name="otherGender" 
                     placeholder="Please specify" style="display: none;">
            </div>
          </div>
          
          <div class="row mt-4">
            <div class="col">
              <label for="income" class="form-label">Monthly Income</label>
              <select name="income" id="income" class="form-select">
                <option value="" disabled selected>Select Income Range</option>
                <option value="Below 10000">Below ₱10,000</option>
                <option value="10000-30000">₱10,000 - ₱30,000</option>
                <option value="40000-50000">₱40,000 - ₱50,000</option>
                <option value="Above 65000">Above ₱65,000</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <hr>
      
      <!-- Children/Dependents Section -->
      <div class="row">
        <div class="col">
          <label class="form-label">Do you have any children or dependents?</label>
          <div class="mt-2">
            <div class="form-check form-check-inline">
              <input type="radio" class="form-check-input" name="hasChildren" id="hasChildrenYes" value="Yes">
              <label for="hasChildrenYes" class="form-check-label">Yes</label>
            </div>
            <div class="form-check form-check-inline">
              <input type="radio" class="form-check-input" name="hasChildren" id="hasChildrenNo" value="No" checked>
              <label for="hasChildrenNo" class="form-check-label">No</label>
            </div>
          </div>
        </div>
      </div>
      
      <div class="row mt-3" id="childrenNumCol" style="display: none;">
        <div class="col-md-4">
          <label for="children_num" class="form-label">Number of Dependents:</label>
          <input type="number" id="children_num" class="form-control" name="children_num" 
                 placeholder="0" min="0" max="20">
        </div>
      </div>
      
      <div class="row mt-3" id="childConcernCol" style="display: none;">
        <div class="col">
          <label for="concern" class="form-label">Special Needs or Concerns (Optional):</label>
          <textarea class="form-control" style="height: 100px; resize: none;" 
                    id="concern" name="concern" placeholder="Enter any special concerns here..."></textarea>
        </div>
      </div>

      <hr>
      
      <!-- Action Buttons -->
      <div class="row">
        <div class="col d-flex flex-row justify-content-end gap-3">
          <button type="submit" class="btn btn-outline-success" id="saveInfo">
            <i class="fas fa-save me-1"></i> Save
          </button>
          <button type="button" id="cancelInfo" class="btn btn-secondary close-btn">
            <i class="fas fa-times me-1"></i> Cancel
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>

  
$(document).ready(function() {
  // Show/hide LGBTQIA+ specify field
  $('#gender').on('change', function() {
    if ($(this).val() === 'LGBTQIA+') {
      $('#otherGender').slideDown(200);
    } else {
      $('#otherGender').val('').slideUp(200);
    }
  });

  // Show/hide children fields
  function toggleChildOptions() {
    const hasChildren = $('input[name="hasChildren"]:checked').val();
    if (hasChildren === 'Yes') {
      $('#childrenNumCol, #childConcernCol').slideDown(200);
    } else {
      $('#children_num').val('');
      $('#concern').val('');
      $('#childrenNumCol, #childConcernCol').slideUp(200);
    }
  }

  $('input[name="hasChildren"]').on('change', toggleChildOptions);
  
  // Initialize on load
  toggleChildOptions();
});
</script>