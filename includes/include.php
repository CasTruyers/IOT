<?php
$servername = "localhost";
$username = "student_12001510";
$password = "BaoXJqJser4j";
$database = "student_12001510";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully <br>";