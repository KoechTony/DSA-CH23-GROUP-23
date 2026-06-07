<?php
// Pull in the database connection
require_once 'db_connect.php';

// Now you can use the $conn variable to talk to your database!
// Example: Adding a reported post to the Queue
$post_id = 15; // Example post ID
$reporter_id = 2; // Example user ID

$sql = "INSERT INTO Reports_Queue (post_id, reported_by_user_id, status) VALUES ('$post_id', '$reporter_id', 'pending')";

if ($conn->query($sql) === TRUE) {
    echo "Report successfully added to the moderation queue.";
} else {
    echo "Error: " . $conn->error;
}

// Always close the connection when you are done
$conn->close();
?>