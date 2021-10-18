<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <title>insertData</title>
</head>

<body>
    <form action="insert.php" method="POST">
        <input type="hidden" name="checkbox" value="false">
        <input type="checkbox" name="checkbox" value="true">
        <label for="name">naam:</label>
        <input type="text" id="name" name="name">
        <label for="number">Integer waarde:</label>
        <input type="number" id="number" name="number">
        <input type="submit">
    </form>
</body>

</html>