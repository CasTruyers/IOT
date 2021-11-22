<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <title>insertData</title>
</head>

<body>
    <form action="includes/inserting.php" method="POST">
        <label for="value">Value:</label>
        <input type="text" id="value" name="value">
        <br>
        <input type="radio" id="Temperature" name="sensor_id" value=1>
        <label for="temperature">Temperature</label><br>
        <input type="radio" id="humidity" name="sensor_id" value=2>
        <label for="humidity">Humidity</label><br>
        <input type="submit">
    </form>
</body>

</html>