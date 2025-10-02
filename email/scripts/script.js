$(document).ready(function () {
    console.log("Ready");
    // Notification  Button
    $(document).on("click", "#notifyBtn", function (e) {
        e.stopPropagation(); // prevent bubbling up to document
        console.log("Clicked!");
        $(".notifications").slideToggle(100);
    });

    // Hide when clicking outside
    $(document).on("click", function (e) {
        if (!$(e.target).closest(".notifications, #notifyBtn").length) {
            $(".notifications").slideUp(100);
        }
    });

});