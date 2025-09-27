<?php
$servername = "localhost";
$username  = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);

//
if ($conn-> connect_error)  {
    echo "No Database connection!";
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully.";
?>