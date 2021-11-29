<?php
include_once 'connect.php';
?>

<html>
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

</html>