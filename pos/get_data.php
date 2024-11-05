<?php
// Include the database connection
include('db_connect.php');

// Initialize response data with default values
$data = array(
    'total_categories' => 0,
    'total_sales' => 0,
    'total_products' => 0,
    'total_users' => 0
);

// Function to get total count from a specific table
function getTotal($conn, $table) {
    $sql = "SELECT COUNT(*) AS total FROM $table";
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc()['total'] : 0;
}

// Retrieve totals from respective tables
$data['total_sales'] = getTotal($conn, 'sales');
$data['total_categories'] = getTotal($conn, 'categories');
$data['total_products'] = getTotal($conn, 'products');
$data['total_users'] = getTotal($conn, 'users');

// Send the data as a JSON response
header('Content-Type: application/json');
echo json_encode($data);

// Close the database connection
$conn->close();
