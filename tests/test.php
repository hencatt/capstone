<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <div id="modal" style="padding:20px; border-radius:10px;">
        <div class="row">
            <div class="col">
                <h4>Personal Info</h4>
            </div>
            <div class="col">
                <span class="material-symbols-outlined">
                    close
                </span>
            </div>
        </div>
        <form method="POST">
            <div style="width: 80%;">
                <hr>
                <div class="row d-flex">

                    <!-- GROUP 1 -->
                    <div class="col-8">
                        <!-- ADDRESS INFO -->
                        <div class="row d-flex flex-column">
                            <div class="col">

                            </div>
                            <div class="col">
                                <label for="inputAddress" class="form-label">Street Address</label>
                                <input type="text" name="inputAddress" id="inputAddress" placeholder="123 Main Street"
                                    class="form-control" require>
                            </div>
                            <div class="col">
                                <label for="inputCity" class="form-label">City</label>
                                <input type="text" name="inputCity" id="inputCity" placeholder="Enter City"
                                    class="form-control" require>
                            </div>
                            <div class="col">
                                <label for="inputProvince" class="form-label">Province</label>
                                <input type="text" name="inputProvince" id="inputProvince" placeholder="Enter Province"
                                    class="form-control" require>
                            </div>
                        </div>
                    </div>

                    <!-- GROUP 2 -->
                    <div class="col-4">
                        <!-- GENDER INFO -->
                        <div class="row d-flex flex-column">
                            <div class="col">
                                <div class="row">
                                    <div class="col">

                                        <label for="inputGender" class="form-label">Select Gender</label>
                                        <select name="inputGender" id="inputGender" class="form-select" require>
                                            <option value="" disabled selected>Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="LGBTQIA+">LGBTQIA+</option>
                                        </select>
                                        <input type="text" id="otherGender" name="otherGender"
                                            placeholder="Enter Gender" class="mt-3 form-control">
                                    </div>
                                </div>

                                <!-- INCOME -->
                                <div class="row mt-4">
                                    <div class="col">

                                        <label for="inputIncome">Select Income</label>
                                        <select name="inputIncome" id="inputIncome" class="form-select" require>
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
                <div class="row mt-5">
                    <div class="col">

                        <!-- GROUP 3 -->
                        <div class="col">
                            <!-- CHILD -->
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
                                            id="inputChildrenNo" value="No">
                                        <label for="inputChildren" class="form-check">No</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label for="inputChildrenNum">Please indicate the number of children or dependents
                                        under your care:</label>
                                    <input type="number" class="form-control" name="inputChildrenNum"
                                        style="width: 100px;" placeholder="1">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-10">
                                    <label for="inputConcern" class="form-label"> (Optional) Are there any medical or
                                        psychological
                                        conditions affecting your child or dependent that may require special
                                        attention?</label>
                                    <input class="form-control" type="text" placeholder="Enter text here"
                                        name="inputConcern" id="inputConcern">
                                </div>
                            </div>
                        </div>

                        <hr>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>

</html>