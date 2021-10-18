<?php
include_once 'includes/include.php';

$number = $_POST["number"];
$name = $_POST["name"];
$checkbox = $_POST["checkbox"];

$sql = "INSERT INTO task2(task2_checked, task2_value, task2_name) VALUES($checkbox, $number, '$name');";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>

<body>
    <p>Inserting succes</p>
</body>

</html>