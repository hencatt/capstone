<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Chart/Graph Link -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <title>chartTest</title>
</head>

<body>
    <h1 id="thisNinja" style="color: #ae2c7cff">Website</h1>
    <div class="container">
        <canvas id="myChart"></canvas>
    </div>

    <script>
        let myChart = document.getElementById("myChart").getContext('2d');
        let chart = new Chart(myChart, {
            type: "bar",
            data: {
                labels: ["Ninjas", "White Ninjas", "Brown Ninjas", "Yellow Ninjas"],
                datasets: [{
                    label: "Ninja Population",
                    data: [
                        132345,
                        123213,
                        335434,
                        63432
                    ],
                    backgroundColor: [
                        "#353535ff",
                        "#b2b2b2",
                        "#d1a266",
                        "#eee877ff"
                    ],
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Ninja Population',
                    },
                    legend: {
                        display: true,
                        position: 'right',
                    }
                }
            }
        });

        function walangLaman(){
            return "walang laman to";
        };

        console.log(walangLaman());
    </script>
</body>



</html>