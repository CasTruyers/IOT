<?php
include_once 'includes/include.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>retrieve</title>
</head>

<body>
    <?php
    $sql = "SELECT * FROM samples;";
    $result = mysqli_query($conn, $sql);
    $resultCheck = mysqli_num_rows($result);

    if ($resultCheck > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['sample_sensor_id'] == 1)
                echo "Temperature: " . $row['sample_value'] . "°C" . "\x20\x20\x20" . "Date: " . $row['sample_date'] . "<br>";
            else if ($row['sample_sensor_id'] == 2)
                echo "Humidity: " . $row['sample_value'] . "%" . "\x20\x20\x20" . "Date: " . $row['sample_date'] . "<br>";

            //echo $row['sample_value'] . "  date:" . $row['sample_date'] . "<br>";
        }
    }

    ?>
</body>

</html>