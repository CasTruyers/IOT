<?php
include_once 'includes/connect.php';

$sql = "SELECT * FROM samples JOIN sensors ON sensor_id = sample_sensor_id ORDER BY sample_date DESC;";
$result = mysqli_query($conn, $sql);
$resultCheck = mysqli_num_rows($result);

header("Content-Type: text/xml;charset-iso-8859-1");

$base_url = "https://12001510.pxl-ea-ict.be/IOT/project/";

echo "<?xml version='1.0' encoding='UTF-8' ?>" . PHP_EOL;

echo "<rss version='2.0'>" . PHP_EOL;

echo "<channel>" . PHP_EOL;

echo "<title>Temperature|Humidity</title>" . PHP_EOL;

echo "<link>" . $base_url . "createRss.php</link>" . PHP_EOL;

echo "<description>Two weeks of sampling Humidity and Temperature data on interval and action trigger. In descending order.</description>" . PHP_EOL;

echo "<github>https://github.com/CasTruyers/IoT</github>" . PHP_EOL;

echo "<language>en-us</language>" . PHP_EOL;

if ($resultCheck > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $publish_Date = date("D, d M Y H:i:s T", strtotime($row["sample_date"]));

        echo "<item xmlns:dc='ns:1'>" . PHP_EOL;
        echo "<title>" . $row["sensor_type"] . "</title>" . PHP_EOL;
        echo "<value>" . $row["sample_value"] . "</value>" . PHP_EOL;
        echo "<unit>" . $row["sensor_unit"] . "</unit>" . PHP_EOL;
        echo "<date>" . $publish_Date . "</date>" . PHP_EOL;
        echo "</item>" . PHP_EOL;
    }
}

echo "</channel>" . PHP_EOL;
echo "</rss>" . PHP_EOL;
