<div id="modal" class="modalInfo">
  <div class="modalInfo-content" style="width: 90%; height: 80vh; overflow-y:auto;">
    <div class="row">
      <div class="col">
        <h4>Personal Info</h4>
      </div>
      <div class="col close-btn d-flex justify-content-end">
        <span class="material-symbols-outlined">close</span>
      </div>
    </div>

    <form method="POST" action="">
      <hr>
      <div class="row d-flex">
        <input type="hidden" name="emp_id" id="id">

        <!-- GROUP 1 -->
        <div class="col-8">
          <div class="row">
            <div class="col">
              <label for="inputFname" class="form-label">First Name</label>
              <input type="text" class="form-control" name="inputFname" id="inputFname" placeholder="Enter Text Here..." required>
            </div>
            <div class="col">
              <label for="inputMname" class="form-label">Middle Name</label>
              <input type="text" class="form-control" name="inputMname" id="inputMname" placeholder="Enter Text Here..." required>
            </div>
            <div class="col">
              <label for="inputLname" class="form-label">Last Name</label>
              <input type="text" class="form-control" name="inputLname" id="inputLname" placeholder="Enter Text Here..." required>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col">
              <label for="inputEmail" class="form-label">Email</label>
              <input type="email" name="inputEmail" id="inputEmail" class="form-control" required>
            </div>
            <div class="col">
              <label for="inputContact" class="form-label">Contact No.</label>
              <input type="text" name="inputContact" id="inputContact" class="form-control" required>
            </div>
          </div>
          <div class="row mt-4">
            <div class="col">
              <label for="inputDepartment" class="form-label">Department</label>
              <input type="text" class="form-control" name="inputDepartment" id="inputDepartment" value="<?= htmlspecialchars($_SESSION['user_department']) ?>" readonly>
            </div>
            <div class="col">
              <label for="inputCampus" class="form-label">Campus</label>
              <input type="text" class="form-control" name="inputCampus" id="inputCampus" value="<?= htmlspecialchars($_SESSION['user_campus']) ?>" readonly>
            </div>
          </div>
          <div class="row mt-4">
            <div class="col">
              <label for="inputBirthdate" class="form-label">Birthdate</label>
              <input type="date" class="form-control" name="inputBirthdate" id="inputBirthdate" required>
            </div>
            <div class="col">
              <label for="inputPriority" class="form-label">Priority Status</label>
              <select name="inputPriority" id="inputPriority" class="form-select" required>
                <option value="" disabled selected>Select Priority Status</option>
                <option value="None">None</option>
                <option value="PWD">PWD</option>
                <option value="Senior Citizen">Senior Citizen</option>
              </select>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-6">
              <label for="inputStAddress" class="form-label">Street Address</label>
              <input type="text" name="inputStAddress" id="inputStAddress" placeholder="123 Main Street" class="form-control" required>
            </div>
            <div class="col">
              <label for="inputCity" class="form-label">City</label>
              <input type="text" name="inputCity" id="inputCity" placeholder="Enter City" class="form-control" required>
            </div>
            <div class="col">
              <label for="inputProvince" class="form-label">Province</label>
              <input type="text" name="inputProvince" id="inputProvince" placeholder="Enter Province" class="form-control" required>
            </div>
          </div>
          <div class="row mt-4">
            <div class="col">
              <label for="inputMaritalStatus" class="form-label">Marital Status</label>
              <select name="inputMaritalStatus" id="inputMaritalStatus" class="form-select" required>
                <option value="" disabled selected>Select Marital Status</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Widowed">Widowed</option>
              </select>
            </div>
            <div class="col">
              <label for="inputSize" class="form-label">Preferred Clothing Size</label>
              <select name="inputSize" id="inputSize" class="form-select" required>
                <option value="" disabled selected>Select Size</option>
                <option value="S">Small</option>
                <option value="M">Medium</option>
                <option value="L">Large</option>
                <option value="XL">Extra Large</option>
                <option value="2XL">Double XL</option>
                <option value="3XL">Triple XL</option>
                <option value="4XL">4XL</option>
              </select>
            </div>
          </div>
        </div>

        <!-- GROUP 2 -->
        <div class="col-4">
          <div class="row d-flex">
            <div class="col">
              <div class="row">
                <div class="col">
                  <label for="inputSex" class="form-label">Select Sex</label>
                  <select name="inputSex" id="inputSex" class="form-select" required>
                    <option value="" disabled selected>Select Sex</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                  </select>
                </div>
              </div>
              <div class="row mt-4">
                <div class="col">
                  <label for="inputGender" class="form-label">Select Gender</label>
                  <select class="form-control" id="inputGender" name="inputGender" required>
                    <option value="">-- Select Gender --</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="LGBTQIA+">LGBTQIA+</option>
                  </select>
                  <input type="text" class="form-control mt-2" id="otherGender" name="otherGender" placeholder="Please specify" style="display: none;">
                </div>
              </div>

              <div class="row mt-4">
                <div class="col">
                  <label for="inputIncome">Select Income</label>
                  <select name="inputIncome" id="inputIncome" class="form-select" required>
                    <option value="" disabled selected>Select Income</option>
                    <option value="Below 10000">Below 10,000</option>
                    <option value="10000-30000">₱10,000 - ₱30,000</option>
                    <option value="40000-50000">₱40,000 - ₱50,000</option>
                    <option value="Above 65000">Above ₱65,000</option>
                  </select>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      <hr>
      <div class="row">
        <div class="col">
          <div class="col">
            <div class="row">
              <div class="col">
                <label>Do you have any children or dependents?</label>
                <div class="mt-2 form-check">
                  <input type="radio" class="form-check-input" name="inputChildren" id="inputChildrenYes" value="Yes">
                  <label for="inputChildrenYes" class="form-check">Yes</label>
                  <input type="radio" class="form-check-input" name="inputChildren" id="inputChildrenNo" checked value="No">
                  <label for="inputChildrenNo" class="form-check">No</label>
                </div>
              </div>
            </div>
            <div class="row mt-3">
              <div class="col" id="childrenNumCol">
                <label for="inputChildrenNum">Number of dependents:</label>
                <input type="number" id="inputChildrenNum" class="form-control" name="inputChildrenNum" style="width: 100px;" placeholder="0">
              </div>
            </div>
          </div>
          <div class="col" id="childConcernCol">
            <label for="inputConcern" class="form-label">(Optional) Special needs or concerns:</label>
            <textarea class="form-control" style="height: 120px; resize: none;" type="text" placeholder="Enter text here" name="inputConcern" id="inputConcern"></textarea>
          </div>
          <hr>
        </div>

        <div class="row">
          <div class="col d-flex flex-row justify-content-end gap-3">
            <button class="btn btn-outline-success" name="saveInfo" id="saveInfo" type="submit">Save</button>
            <button type="button" id="cancelInfo" name="cancelInfo" class="btn btn-secondary close-btn">Cancel</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
$(document).ready(function() {
  $('#editInfo').click(function() {
    const isDisabled = $('#inputFname').prop('disabled');
    $('#saveInfo, #inputFname, #inputLname, #inputMname, #inputStreet, #inputContact, #inputBirthdate, #inputPriority, #inputStAddress, #inputCity, #inputProvince, #inputMaritalStatus, #inputSize, #inputSex, #inputGender, #inputIncome, #inputChildren, #inputChildrenNum, #inputConcern').prop('disabled', !isDisabled);
    if (!isDisabled) {
      $('#inputFname, #inputLname, #inputMname, #inputStreet, #inputContact, #inputBirthdate, #inputPriority, #inputStAddress, #inputCity, #inputProvince, #inputMaritalStatus, #inputSize, #inputSex, #inputGender, #inputIncome, #inputChildren, #inputChildrenNum, #inputConcern').val('');
    }
    $('#editInfo').text(isDisabled ? 'Cancel' : 'Edit');
    $('#cancelInfo').toggle(!isDisabled);
    $('#saveInfo').toggle(isDisabled);
  });

  $('#saveInfo').click(function(e) {
    e.preventDefault();
    // location.reload(); // optional if using AJAX
  });
});
</script>