<?php
// Force PHP to display errors if something breaks
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost"; 
$username = "root";        
$password = "";            
$dbname = "social_network_db"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection Failed! Reason: " . $conn->connect_error);
}