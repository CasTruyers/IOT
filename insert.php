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
    <div class="flexContainer">
        <form action="includes/inserting.php" method="POST" class="form">
            <input type="text" class="value" name="value" placeholder="Value">
            <br>
            <input type="radio" class="temperature" name="sensor_id" value=1>
            <label for="temperature" class="right">Temperature</label>
            <br>
            <input type="radio" class="humidity" name="sensor_id" value=2>
            <label for="humidity" class="right">Humidity</label>
            <br>
            <br>
            <button type="submit">SEND</button>
        </form>
    </div>
</body>

</html>