<?php
include_once 'includes/connect.php';

$sql = "SELECT * FROM samples JOIN sensors ON sensor_id = sample_sensor_id ORDER BY sample_date DESC;";
$result = mysqli_query($conn, $sql);

$json_array = array();

while ($row = mysqli_fetch_assoc($result)) {
    $json_array[] = $row;
}

echo json_encode($json_array);
