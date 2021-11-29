<?php
include_once 'includes/connect.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>retrieve</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="includes/stylesheet.css">
    <script src="includes/javascript.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
</head>

<body>
    <div id="Adjust">
        <label for="darkmode">DarkMode</label>
        <input type="checkbox" id="darkmode" name="darkmode" onclick="darkMode()">
        <button class="button">UPDATE</button>
    </div>
    <div id="Table">
        <table>
            <tr>
                <th>type</th>
                <th>value</th>
                <th>unit</th>
                <th>date</th>
            </tr>
            <?php
            $sql = "SELECT * FROM samples JOIN sensors ON sensor_id = sample_sensor_id;";
            $result = mysqli_query($conn, $sql);
            $resultCheck = mysqli_num_rows($result);

            if ($resultCheck > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo
                    "<tr>
                        <td>" . $row["sensor_type"] . "</td>
                        <td>" . $row["sample_value"] . "</td>
                        <td>" . $row["sensor_unit"] . "</td>
                        <td>" . $row["sample_date"] . "</td>
                        </tr>";
                }
            } else
                echo "no results";
            ?>
        </table>
    </div>
</body>

<script>
    $(document).ready(function() {
        $("button").click(function() {
            $("#Table").load("includes/loadPHP.php")
        })
    })
</script>

</html>