<div id="modal" class="modalInfo">
    <div class="modalInfo-content" style="width: 90%">
        <div class="row">
            <div class="col">
                <h4>Personal Info</h4>
            </div>
            <div class="col close-btn d-flex justify-content-end">
                <span class="material-symbols-outlined">
                    close
                </span>
            </div>
        </div>
        <form method="POST">
            <hr>
            <div class="row d-flex">

                <!-- GROUP 1 -->
                <div class="col-8">
                    <!-- ADDRESS INFO -->
                    <div class="row">
                        <div class="col">
                            <label for="inputFname" class="form-label">First Name</label>
                            <input type="text" class="form-control" name="inputFname" id="inputFname" placeholder="Enter Text Here...">
                        </div>
                        <div class="col">
                            <label for="inputMname" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="inputMname" id="inputMname" placeholder="Enter Text Here...">
                        </div>
                        <div class="col">
                            <label for="inputLname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="inputLname" id="inputLname" placeholder="Enter Text Here...">
                        </div>
                    </div>
                    <div class="row mt-4 d-flex">
                        <div class="col">
                            <label for="inputBirthdate" class="form-label">Birthdate</label>
                            <input type="date" class="form-control" name="inputBirthdate" id="inputBirthdate">
                        </div>
                        <div class="col">
                            <label for="inputContact" class="form-label">Contact no.</label>
                            <input type="text" placeholder="0987654321" class="form-control" maxlength="11"
                                name="inputContact" id="inputContact">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <label for="inputAddress" class="form-label">Street Address</label>
                            <input type="text" name="inputAddress" id="inputAddress" placeholder="123 Main Street"
                                class="form-control" required>
                        </div>
                        <div class="col">
                            <label for="inputCity" class="form-label">City</label>
                            <input type="text" name="inputCity" id="inputCity" placeholder="Enter City"
                                class="form-control" required>
                        </div>
                        <div class="col">
                            <label for="inputProvince" class="form-label">Province</label>
                            <input type="text" name="inputProvince" id="inputProvince" placeholder="Enter Province"
                                class="form-control" required>
                        </div>
                    </div>


                    <div class="row mt-4">
                        <div class="col">
                            <label for="inputMaritalStatus" class="form-label">Marital Status</label>
                            <select name="inputMaritalStatus" id="inputMaritalStatus" class="form-select">
                                <option value="" disabled selected required>Select Marital Status</option>
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
                    <!-- GENDER INFO -->
                    <div class="row d-flex">
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <label for="inputSex" class="form-label">Select Sex</label>
                                    <select name="inputSex" id="inputSex" class="form-select">
                                        <option value="" disabled selected required>Select Sex</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col">
                                    <label for="inputGender" class="form-label">Select Gender</label>
                                    <select name="inputGender" id="inputGender" class="form-select" required>
                                        <option value="" disabled selected required>Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="LGBTQIA+">LGBTQIA+</option>
                                    </select>
                                    <input type="text" id="otherGender" name="otherGender" placeholder="Please specify"
                                        class="mt-3 form-control">
                                </div>
                            </div>

                            <!-- INCOME -->
                            <div class="row mt-4">
                                <div class="col">

                                    <label for="inputIncome">Select Income</label>
                                    <select name="inputIncome" id="inputIncome" class="form-select" required>
                                        <option value="" disabled selected>Select Income</option>
                                        <option value="Below 10000">Below 10,000</option>
                                        <option value="10,000 - 30,000">10,000 - 30,000</option>
                                        <option value="40,000 - 50,000">40,000 - 50,000</option>
                                        <option value="Above 50,000">Above 65,000</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <!-- ROW 2 -->
            <div class="row">
                <div class="col">

                    <!-- GROUP 3 -->
                    <div class="col">
                        <!-- CHILD -->
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col">
                                        <label>
                                            Do you have any children or other individuals for whom you are the primary
                                            caregiver?
                                        </label>
                                        <div class="mt-2 form-check">
                                            <input type="radio" class="form-check-input" name="inputChildren"
                                                id="inputChildrenYes" value="Yes">
                                            <label for="inputChildren" class="form-check">Yes</label>
                                            <input type="radio" class="form-check-input" name="inputChildren"
                                                id="inputChildrenNo" checked value="No">
                                            <label for="inputChildren" class="form-check">No</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col" id="childrenNumCol">
                                        <label for="inputChildrenNum">Please indicate the number of children or
                                            dependents
                                            under your care:</label>
                                        <input type="number" id="inputChildrenNum" class="form-control"
                                            name="inputChildrenNum" style="width: 100px;" placeholder="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col" id="childConcernCol">
                                <label for="inputConcern" class="form-label"> (Optional) Are there any medical or
                                    psychological
                                    conditions affecting your child or dependent that may required special
                                    attention?</label>
                                <textarea class="form-control" style="height: 120px; resize: none;" type="text"
                                    placeholder="Enter text here" name="inputConcern" id="inputConcern"></textarea>
                            </div>


                        </div>
                    </div>

                    <hr>
                </div>
                <div class="row">
                    <div class="col d-flex flex-row justify-content-end gap-3">
                        <button class="btn btn-outline-secondary" name="editInfo" id="editInfo"
                            type="button">Edit</button>
                        <button class="btn btn-outline-success" name="saveInfo" id="saveInfo">Save</button>
                        <button type="button" id="cancelInfo" class="btn btn-secondary close-btn">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>