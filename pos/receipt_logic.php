<?php
session_start();
include 'db_connect.php'; // Database connection

// Step 1: Validate sale_id from POST request
if (empty($_POST['sale_id'])) {
    echo 0; // Error if sale_id is missing
    exit;
}
$sale_id = $_POST['sale_id'];

// Step 2: Validate and retrieve arrays for items
$product_ids = $_POST['product_ids'] ?? [];
$quantities = $_POST['quantities'] ?? [];
$prices = $_POST['prices'] ?? [];

// Ensure arrays have consistent lengths
if (count($product_ids) !== count($quantities) || count($quantities) !== count($prices)) {
    echo 0; // Error if array lengths do not match
    exit;
}

// Step 3: Fetch configurable values from `settings` table
$configQuery = $conn->query("SELECT * FROM system_settings LIMIT 1");
if ($configQuery->num_rows === 0) {
    echo 0; // Error if settings not found
    exit;
}
$settings = $configQuery->fetch_assoc();
extract($settings, EXTR_PREFIX_ALL, 'config');

// (Other existing steps like calculation here)

// Step 12: Prepare data for each item and insert into `sales_receipt`
$cashier_name = $_SESSION['login_name'] ?? 'Unknown';
$invoice_no = $sale['ref_no'];

// Prepare the insert query for sales receipt
$insertReceiptQuery = $conn->prepare("
    INSERT INTO sales_receipt 
    (sale_id, invoice_no, date, time, cashier_name, item_description, qty, price, amount, sub_total, discount, discount_percentage, total_sales_vat_ex, vat_percentage, vat_amount, covid_tax_percentage, covid_tax_amount, net_sales_amount, cash_paid, change_due, contact_tel) 
    VALUES (?, ?, NOW(), CURTIME(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($product_ids as $index => $product_id) {
    $qty = $quantities[$index];
    $price = $prices[$index];
    $amount = round($qty * $price, 2);

    // Update stock
    $stockUpdate = $conn->query("UPDATE products SET stock = stock - $qty WHERE id = '$product_id'");

    if (!$stockUpdate) {
        echo 0; // Error updating stock
        exit;
    }

    // Fetch item description
    $productQuery = $conn->query("SELECT name FROM products WHERE id = '$product_id' LIMIT 1");
    $item_description = $productQuery->num_rows > 0 ? $productQuery->fetch_assoc()['name'] : 'Unknown Item';

    // Insert values for the current item
    $insertReceiptQuery->bind_param(
        "isssdddddddddddddds",
        $sale_id,
        $invoice_no,
        $cashier_name,
        $item_description,
        $qty,
        $price,
        $amount,
        $total_amount,
        $discount_amount,
        $config_default_discount,
        $total_sales_vat_ex,
        $config_vat_percentage,
        $vat_amount,
        $config_covid_tax_percentage,
        $covid_tax_amount,
        $net_sales_amount,
        $cash_paid,
        $change_due,
        $config_contact
    );

    if (!$insertReceiptQuery->execute()) {
        echo 0; // Error inserting receipt item
        exit;
    }
}

echo 1; // Success
$conn->close();
