<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "portfolio_system";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    die("Database connection failed. Please check XAMPP MySQL.");
}

$conn->set_charset("utf8mb4");

?>
