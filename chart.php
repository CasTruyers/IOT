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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
    <title>Charts</title>
    <style>
        #ISSmap {
            width: 100%;
            height: 50%;
        }
    </style>
</head>

<body>
    <div class="chart">
        <canvas id="myChart"></canvas>
        <div class="sliderdiv">
            <input class="slider" oninput="sliceData(this)" type="range" id="slider" min="100" max="828" value="828">
        </div>
    </div>
    <div class="text">scroll down for awesomeness</div>
    <div class="chart">
        <h2>WHERE THE ISS AT?</h2>
        <p>Well lets have a look</p>
        <p>latitude: <span id="lat"></span>°</p>
        <p>longitude: <span id="lon"></span>°</p>
        <br>
        <p><b>Wait lemme show:</b></p>
        <div id="ISSmap"></div>
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

        //map and tiles
        const mymap = L.map('ISSmap').setView([0, 0], 1);
        const tileUrl = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
        const tiles = L.tileLayer(tileUrl);
        tiles.addTo(mymap);

        //custom icon
        const issIcon = L.icon({
            iconUrl: 'iss200.png',
            iconSize: [50, 32],
            iconAnchor: [25, 16],
        });
        const marker = L.marker([0, 0], {
            icon: issIcon
        }).addTo(mymap);

        let firstTime = true;

        async function getISS() {
            const response = await fetch("https://api.wheretheiss.at/v1/satellites/25544");
            const data = await response.json();
            console.log(data.latitude);
            console.log(data.longitude);
            const {
                latitude,
                longitude
            } = data;
            marker.setLatLng([latitude, longitude]);
            if (firstTime) {
                mymap.setView([latitude, longitude], 6);
                firstTime = false;
            }
            document.getElementById("lat").textContent = latitude.toFixed(2);
            document.getElementById("lon").textContent = longitude.toFixed(2);
        }

        getISS();
        setInterval(getISS, 1000);
    </script>
    <style>
        .chart {
            margin: auto;
            width: 70%;
            border: 3px solid green;
            padding: 10px;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .sliderdiv {
            margin: auto;
            width: 10%;
            border: 2px solid green;
            padding: 5px;

        }

        .text {
            text-align: center;
            margin-top: 150px;
            margin-bottom: 300px;
        }

        p {
            text-align: center
        }

        h2 {
            text-align: center
        }
    </style>
</body>

</html>