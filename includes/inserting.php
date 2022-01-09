<?php
include_once 'connect.php';

$value = $_POST["value"];
$sensor_id = $_POST["sensor_id"];

$sql = "INSERT INTO samples(sample_value, sample_sensor_id) VALUES($value, $sensor_id);";
$result = mysqli_query($conn, $sql);

if ($result) {
    $headers = array(
        "Authorization: Bearer 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",
        'Content-Type: application/json'
    );
    $data = array(
        "personalizations" => array(
            array(
                "to" => array(
                    array(
                        "email" =>  "12001510@student.pxl.be",
                        "name" => "CasTruyers"
                    )
                )
            )
        ),
        "from" => array(
            "email" => "cas.truyers@outlook.be"
        ),
        "subject" => "data added",
        "content" => array(
            array(
                "type" => "text/html",
                "value" => "New data is added to the database<br><br>table and chart of data: <a href='https://12001510.pxl-ea-ict.be/IOT/project/'>click here</a>"
            )
        )
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.sendgrid.com/v3/mail/send");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    echo "Inserting succes";
} else {
    echo "Inserting failure";
}
