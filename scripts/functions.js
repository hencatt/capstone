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
  if (position === "Technical Assistant" || position === "Focal Person") {
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
    const deptFilter   = $(filterDepartment).val();
    const sizeFilter   = $(filterSize).val();
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
}
