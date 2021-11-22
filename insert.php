<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Calistoga&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="includes/stylesheet.css">
    <title>insertData</title>
</head>

<body>
    <form action="includes/inserting.php" method="POST" id="inputTable">
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