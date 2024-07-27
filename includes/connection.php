<?php
$serverName = "localhost";
$userName = "root";
$password = "";
$dbName = "groupdb";

// create connection 
$conn = mysqli_connect($serverName, $userName, $password, $dbName);
// check connection
if (!$conn) {
    die("connection failed!" . mysqli_connect_error());
}