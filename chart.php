<html>

<?php
include_once 'includes/connect.php';

$humi = '';
$temp = '';
$timeLabel = '';

$sql = "SELECT * FROM samples JOIN sensors ON sensor_id = sample_sensor_id;";
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
    <title>Charts</title>
</head>

<body>
    <div class="chart">
        <canvas id="myChart" canvas>
    </div>

    <script>
        const myChart = new Chart(
            document.getElementById('myChart'), {
                type: 'line',
                data: {
                    labels: [<?php echo $timeLabel; ?>],
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
                },
                options: {
                    spanGaps: true
                }
            }
        );
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