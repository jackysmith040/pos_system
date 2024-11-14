<?php
session_start();
include 'db_connect.php';  // Database connection

// Clear all records from the sales_receipt table
$deleteQuery = $conn->query("DELETE FROM sales_receipt");

if ($deleteQuery) {
    echo 1;  // Success
} else {
    echo 0;  // Error deleting records
}

$conn->close();
