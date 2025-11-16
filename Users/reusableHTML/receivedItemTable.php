<?php
session_start()
    ?>

<div class="row">
    <div class="col-6" id="inputFields">

        <div class="row mb-3 align-items-center">
            <label class="col-3 col-form-label fw-semibold">Name:</label>
            <div class="col">
                <input type="text" id="inputName" class="form-control" value="<?= $_SESSION['fullname'] ?>" readonly>
            </div>
        </div>

        <div class="row mb-3 align-items-center">
            <label class="col-3 col-form-label fw-semibold">Item Name:</label>
            <div class="col">
                <select id="inputItemName" class="form-control" required>
                    <option value="">Select Item</option>
                </select>
            </div>
            <input type="hidden" id="dbRemaining">
        </div>

        <div class="row mb-3 align-items-center">
            <label class="col-3 col-form-label fw-semibold">Received Item/s:</label>
            <div class="col">
                <input type="number" id="inputReceived" class="form-control" placeholder="Auto" readonly>
            </div>
        </div>

        <div class="row mb-3 align-items-center">
            <label class="col-3 col-form-label fw-semibold">Distributed Item/s:</label>
            <div class="col">
                <input type="number" id="inputDistributed" class="form-control" placeholder="e.g. 2" required>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="button" id="btnConvert" class="btn btn-primary px-4 fw-semibold">
                Convert to Table
            </button>
        </div>
    </div>

    <hr class="mt-4">

    <div id="receivedTable"></div>
</div>