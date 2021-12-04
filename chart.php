<html>

<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.6.1/chart.min.js" integrity="sha512-O2fWHvFel3xjQSi9FyzKXWLTvnom+lOYR/AUEThL/fbP4hv1Lo5LCFCGuTXBRyKC4K4DJldg5kxptkgXAzUpvA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <title>Charts</title>
</head>

<body>
    <div class="chart">
        <canvas id="myChart" canvas>
    </div>

    <script>
        const ctx = document.getElementById("myChart").getContext("2d");

        const labels = [
            "2012",
            "2013",
            "2014",
            "2015",
            "2016",
            "2017",
            "2018",
            "2019",
            "2020",
            "2021",
        ];
        const data = {
            labels,
            datasets: [{
                data: [211, 332, 165, 350, 420, 370, 500, 375, 415, 372],
                label: "Minecraft Sales",
            }, ],
        };
        const config = {
            type: "line",
            data: data,
            options: {
                responsive: true,
            },
        };

        const myChart = new Chart(ctx, config);
    </script>

    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
    </style>

</body>

</html>