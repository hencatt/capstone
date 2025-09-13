// $(document).ready(function() {
//         var CBshowSummary = $('#checkboxShowSummary');

//         function filter() {
//             var campusFilter = $('#filterCampus').val();
//             var deptFilter = $('#filterDept').val();
//             var sizeFilter = $('#filterSize').val();
//             var genderFilter = $('#filterGender').val();
//             var showRec = "no";
//             var generate = "filter";
//             var pos = "Focal Person";
//             var showSum = CBshowSummary.is(':checked') ? "yes" : "no";

//             console.log("currently posting: " + campusFilter + ', ' + deptFilter + ', ' + sizeFilter);
//             console.log("show summary?: " + showSum);

//             $.ajax({
//                 url: '../phpFunctions/filterFunction.php',
//                 method: 'POST',
//                 data: {
//                     campusFilter: campusFilter,
//                     deptFilter: deptFilter,
//                     sizeFilter: sizeFilter,
//                     genderFilter: genderFilter,
//                     showSummary: showSum,
//                     showReceipt: showRec,
//                     whatGenerate: generate,
//                     currentPosition: pos
//                 },
//                 success: function(data) {
//                     $("#employeeTable").parent().html(data);
//                 }
//             });
//         }

//         $('#filterCampus, #filterDept, #filterSize, #filterGender, #checkboxShowSummary').on('change', filter);
//     });




// const CBshowSummary = $('#checkboxShowSummary');
//             const CBshowReceipt = $('#checkboxShowReceipt');
//             const pdfBTN = $('#btnGeneratePDF');
//             const receiptBTN = $('#checkboxReceipt');
//             const pos = '{$currentPosition}';
//             const user = '{$currentUser}';
//             const generate = "report";
//             const orientation = $('#orientation');
//             const orientationLbl = $('#orientationLabel');
//             const element = $('#generatePDF');
//             const scale = $('#scale');
//             const scaleLbl = $('#scaleLabel');
//             const size = $('#size');
//             const sizeLbl = $('#sizeLabel');

//             pdfBTN.hide();
//             element.hide();

//             function updateResult() {
//                 pdfBTN.fadeIn();
//                 element.fadeIn();

//                 const campusFilter = $('#filterCampus').val();
//                 const deptFilter = $('#filterDepartment').val();
//                 const sizeFilter = $('#filterSize').val();
//                 const genderFilter = $('#filterGender').val();
//                 const showSum = CBshowSummary.is(':checked') ? "yes" : "no";
//                 const showRec = receiptBTN.is(':checked') ? "yes" : "no";

//                 console.log("currently posting: " + campusFilter + ', ' + deptFilter + ', ' + sizeFilter);
//                 console.log("show summary? :" + showSum);


//                 $.ajax({
//                     url: "../phpFunctions/filterFunction.php",
//                     method: "POST",
//                     data: {
//                         deptFilter: deptFilter,
//                         campusFilter: campusFilter,
//                         sizeFilter: sizeFilter,
//                         genderFilter: genderFilter,
//                         showSummary: showSum,
//                         showReceipt: showRec,
//                         currentPosition: pos,
//                         whatGenerate: generate
//                     },
//                     success: function(data) {
//                         $("#employeeTable").parent().html(data);
//                     }
//                 });
//             }

//             // Trigger AJAX when filters are changed
//             $('#filterCampus, #filterDepartment, #filterSize, #filterGender, #checkboxShowSummary, #checkboxReceipt').on('change', updateResult);
        