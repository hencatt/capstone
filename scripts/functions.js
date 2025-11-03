// ALL PARAMS ARE ID NG MGA NASA FORM
function filterFunction(
  checkbox,
  campus,
  dept,
  size,
  gender,
  position,
  tableId,
  summary,
  generateWhat
) {
  console.log("loaded");
  var CBshowSummary = $(checkbox);

  function filter() {
    var campusFilter = $(campus).val();
    var deptFilter = $(dept).val();
    var sizeFilter = $(size).val();
    var genderFilter = $(gender).val();
    var showRec = summary;
    var generate = generateWhat;
    var pos = position;
    var showSum = CBshowSummary.is(":checked") ? "yes" : "no";

    console.log(
      "currently posting: " +
      campusFilter +
      ", " +
      deptFilter +
      ", " +
      sizeFilter
    );
    console.log("show summary?: " + showSum);

    $.ajax({
      url: "../phpFunctions/filterFunction.php",
      method: "POST",
      data: {
        campusFilter: campusFilter,
        deptFilter: deptFilter,
        sizeFilter: sizeFilter,
        genderFilter: genderFilter,
        showSummary: showSum,
        showReceipt: showRec,
        whatGenerate: generate,
        currentPosition: pos,
      },
      success: function (data) {
        $(tableId).parent().html(data);
      },

    });
  }
  $(campus + ", " + dept + ", " + size + ", " + gender + ", " + checkbox).on(
    "change",
    filter
  );

  filter();
}

function restrictDeptAndCampus(position, dept, campus, deptId, campusId) {
  console.log(position);
  if (position === "Focal Person") {
    console.log("restricted");
    $(deptId).val(dept).change();
    $(campusId).val(campus).change();

    $(deptId).prop("disabled", true);
    $(campusId).prop("disabled", true);
  }
}

function resetFilterFunction(position) {
  var currentPos = position;

  $("#resetFilter").click(function () {
    if (currentPos !== "Director") {
      $("#filterSize").val("None").change();
      $("#filterGender").val("None").change();
    } else {
      $("#filterCampus").val("None").change();
      $("#filterSize").val("None").change();
      $("#filterDept").val("None").change();
      $("#filterGender").val("None").change();
    }
  });
}

function generateInventoryFilter(position, pdfButton, elementId, category, tableId) {
  const pdfBtn = $(pdfButton);
  const element = $(elementId);
  const categoryEl = $(category);

  function updateResult() {
    console.log("filter inventory");
    pdfBtn.fadeIn();
    element.fadeIn();

    $.ajax({
      url: "../phpFunctions/filterFunction_inventory.php",
      method: "POST",
      data: {
        currentPosition: position,
        item_category: categoryEl.val()
      },
      success: function (data) {
        $(tableId).html(data);
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
      }
    });
  }

  categoryEl.on("change", updateResult);


  // updateResult();
}

function receivedItemTable(inputName, inputItemName, inputReceived, inputDistributed, pdfButton, elementId, tableId) {

  const pdfBtn = $(pdfButton);
  const element = $(elementId);

  $(document).off("click", "#btnConvert").on("click", "#btnConvert", function () {

    const name = $(inputName).val();
    const itemId = $(inputItemName).val(); // ✅ this is ID now
    const received = $("#dbRemaining").val(); // ✅ from DB
    const distributed = $(inputDistributed).val();

    if (!name || !itemId || received === "" || distributed === "") {
      alert("Please fill all fields first.");
      return;
    }

    $("#receivedTable").html("<small class='text-muted'>Generating table...</small>");

    $.ajax({
      url: "../phpFunctions/receivedItems.php",
      type: "POST",
      data: { name, itemId, received, distributed }, // ✅ send itemId

      success: function (result) {
        $("#receivedTable").hide().html(result).fadeIn();
        $(tableId).html(result);
        pdfBtn.fadeIn();
        element.fadeIn();
      },

      error: function () {
        alert("Error processing request");
      }
    });
  });
}




function generateReportFilter(
  position,
  pdfButton,
  elementId,
  filterCampus,
  filterDepartment,
  filterSize,
  filterGender,
  checkBoxSum,
  checkboxReceipt,
  tableId
) {
  // make sure pdfButton & elementId are jQuery objects
  const pdfBTN = $(pdfButton);
  const element = $(elementId);

  function updateResult() {

    pdfBTN.fadeIn();
    element.fadeIn();

    const campusFilter = $(filterCampus).val();
    const deptFilter = $(filterDepartment).val();
    const sizeFilter = $(filterSize).val();
    const genderFilter = $(filterGender).val();

    const showSum = $(checkBoxSum).is(":checked") ? "yes" : "no";
    const showRec = $(checkboxReceipt).is(":checked") ? "yes" : "no";

    console.log("currently posting:", campusFilter, deptFilter, sizeFilter);
    console.log("show summary?:", showSum);

    $.ajax({
      url: "../phpFunctions/filterFunction.php",
      method: "POST",
      data: {
        deptFilter: deptFilter,
        campusFilter: campusFilter,
        sizeFilter: sizeFilter,
        genderFilter: genderFilter,
        showSummary: showSum,
        showReceipt: showRec,
        currentPosition: position,
        whatGenerate: "report"
      },
      success: function (data) {
        $(tableId).parent().html(data);
      }
    });
  }

  // bind all filters in one go
  $(filterCampus + ", " + filterDepartment + ", " + filterSize + ", " + filterGender + ", " + checkBoxSum + ", " + checkboxReceipt)
    .on("change", updateResult);

    updateResult();
}
