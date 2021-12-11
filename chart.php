<html>

<?php
include_once 'includes/connect.php';

$humi = '';
$temp = '';

$sql = "SELECT * FROM samples JOIN sensors ON sensor_id = sample_sensor_id;";
$result = mysqli_query($conn, $sql);
$resultCheck = mysqli_num_rows($result);

if ($resultCheck > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row["sensor_id"] == 1)
            $temp = $temp . '"' . $row["sample_value"] . '",';
        else if ($row["sensor_id"] == 2)
            $humi = $humi . '"' . $row["sample_value"] . '",';
        else
            log("sensor id != 1 or 2");
    }
} else
    log("No rows");

$temp = trim($temp, ",");
$humi = trim($humi, ",");
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
                    labels: [1, 2, 3, 4, 5, 6],
                    datasets: [{
                            label: 'Temp',
                            data: [<?php echo $temp; ?>],
                            backgroundColor: 'transparent',
                            borderColor: 'rgba(255, 0, 0)',
                            borderWidth: 3
                        },
                        {
                            label: 'Humi',
                            data: [<?php echo $humi; ?>],
                            backgroundColor: 'transparent',
                            borderColor: 'rgba(0, 255, 0)',
                            borderWidth: 3
                        }
                    ]
                },
                options: {}
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