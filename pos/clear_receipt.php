<?php
session_start();
include 'db_connect.php'; // Database connection

function clearReceipt($sale_id, $deleteSalesRecord = false) {
    global $conn; // Access the database connection

    // Step 1: Delete the sales_receipt entry for the specified sale_id
    $deleteReceiptQuery = $conn->prepare("DELETE FROM sales_receipt WHERE sale_id = ?");
    $deleteReceiptQuery->bind_param("i", $sale_id);

    if (!$deleteReceiptQuery->execute()) {
        return false; // Error deleting receipt
    }

    // Step 2: Optional: Delete related sales record if specified
    if ($deleteSalesRecord) {
        $deleteSalesQuery = $conn->prepare("DELETE FROM sales WHERE id = ?");
        $deleteSalesQuery->bind_param("i", $sale_id);

        if (!$deleteSalesQuery->execute()) {
            return false; // Error deleting sales record
        }
    }

    return true; // Success
}

// Example of how to call this function
if (isset($_POST['sale_id'])) {
    $sale_id = $_POST['sale_id'];
    $deleteSalesRecord = isset($_POST['delete_sales_record']) && $_POST['delete_sales_record'] == 'true'; // Check if the sales record should be deleted

    if (clearReceipt($sale_id, $deleteSalesRecord)) {
        echo 1; // Success
    } else {
        echo 0; // Error
    }
}
