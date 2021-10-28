<?php
include_once 'includes/include.php';

$value = $_POST["value"];
$sensor_id = $_POST["sensor_id"];

$sql = "INSERT INTO samples(sample_value, sample_sensor_id) VALUES($value, $sensor_id);";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>

<body>
    <p>Inserting succes</p>
</body>

</html>