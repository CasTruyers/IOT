<html>

<?php
include_once 'includes/connect.php';

$humi = '';
$temp = '';
$timeLabel = '';

$sql = "SELECT * FROM samples JOIN sensors ON sensor_id = sample_sensor_id ORDER BY sample_date ASC;";
$result = mysqli_query($conn, $sql);
$resultCheck = mysqli_num_rows($result);

if ($resultCheck > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row["sensor_id"] == 1) {
            $temp = $temp . '"' . $row["sample_value"] . '",';
            $humi = $humi . '"NULL",';
            $timeLabel = $timeLabel . '"' . $row["sample_date"] . '",';
        } else if ($row["sensor_id"] == 2) {
            $humi = $humi . '"' . $row["sample_value"] . '",';
            $temp = $temp . '"NULL",';
            $timeLabel = $timeLabel . '"' . $row["sample_date"] . '",';
        } else
            log("sensor id != 1 or 2");
    }
} else
    log("No rows");

$temp = trim($temp, ",");
$humi = trim($humi, ",");
$timeLabel = trim($timeLabel, ",");
?>

<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.6.1/chart.min.js" integrity="sha512-O2fWHvFel3xjQSi9FyzKXWLTvnom+lOYR/AUEThL/fbP4hv1Lo5LCFCGuTXBRyKC4K4DJldg5kxptkgXAzUpvA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <title>Charts</title>
</head>

<body>
    <div class="chart">
        <canvas id="myChart"></canvas>
        <div class="sliderdiv">
            <input class="slider" oninput="sliceData(this)" type="range" id="slider" min="100" max="828" value="828">
        </div>
    </div>


    <script>
        //setup
        const dates = [<?php echo $timeLabel; ?>]
        const data = {
            labels: dates,
            datasets: [{
                    label: 'Temperature [°C]',
                    data: [<?php echo $temp; ?>],
                    backgroundColor: 'transparent',
                    borderColor: 'rgba(255, 0, 0)',
                    borderWidth: 1
                },
                {
                    label: 'Humidity [%]',
                    data: [<?php echo $humi; ?>],
                    backgroundColor: 'transparent',
                    borderColor: 'rgba(0, 255, 0)',
                    borderWidth: 1
                }
            ]
        };

        //config
        const config = {
            type: 'line',
            data,
            options: {
                spanGaps: true,
                scales: {
                    y: {
                        beginAtZero: true
                    },
                    x: {
                        type: 'time'
                    }
                }
            }
        }
        //render
        const myChart = new Chart(document.getElementById('myChart'), config)

        function sliceData(range) {
            const rangeValue = dates.slice(0, range.value);
            myChart.config.data.labels = rangeValue;
            myChart.update();
        }
    </script>
    <style>
        .chart {
            margin: auto;
            width: 70%;
            border: 3px solid green;
            padding: 10px;
            margin-top: 25px;
        }

        .sliderdiv {
            margin: auto;
            width: 10%;
            border: 2px solid green;
            padding: 5px;

        }
    </style>
</body>

</html>