<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <title>Document</title>
    <link rel="stylesheet" href="../Users/css/toast.css">
</head>

<body>
    <div>
        <div id="toast">
        </div>
    </div>
    <div>
        <button id="a">aaaaa

        </button>
    </div>


    <script>
        (function () {
            const a = document.getElementById("a");
            function showToast(message, bgColor = "white") {
                const toast = document.getElementById("toast");
                toast.style.visibility = "visible";
                toast.style.opacity = "1";
                toast.textContent = message;
                toast.style.backgroundColor = bgColor;
                toast.classList.add("show");

                setTimeout(() => {
                    toast.style.visibility = "hidden";
                    toast.style.opacity = "0";
                    toast.classList.remove("show");
                }, 3000);
            }
            a.addEventListener("click", () => {
                showToast("Ninja", "red")
            })

        })();
    </script>
</body>

</html>