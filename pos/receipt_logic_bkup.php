<?php
session_start();
include 'db_connect.php'; // Database connection

// Step 1: Validate sale_id from POST request
if (!isset($_POST['sale_id']) || empty($_POST['sale_id'])) {
    echo 0; // Return error if sale_id is missing
    exit;
}
$sale_id = $_POST['sale_id'];

// Step 2: Validate and retrieve arrays for items
$product_ids = isset($_POST['product_ids']) ? $_POST['product_ids'] : [];
$quantities = isset($_POST['quantities']) ? $_POST['quantities'] : [];
$prices = isset($_POST['prices']) ? $_POST['prices'] : [];

// Ensure arrays are consistent in length
if (count($product_ids) !== count($quantities) || count($quantities) !== count($prices)) {
    echo 0; // Return error if array lengths do not match
    exit;
}

// Step 3: Fetch configurable values from `settings` table
$configQuery = $conn->query("SELECT * FROM system_settings LIMIT 1");
if ($configQuery->num_rows > 0) {
    $settings = $configQuery->fetch_assoc();
    $vat_percentage = $settings['vat_percentage'];
    $covid_tax_percentage = $settings['covid_tax_percentage'];
    $default_discount = $settings['default_discount'];
    $footer_message = $settings['footer_message'];
} else {
    echo 0; // Return error if settings not found
    exit;
}

// Step 4: Fetch the sale data from `sales` table using the provided sale_id
$saleQuery = $conn->query("SELECT * FROM sales WHERE id = '$sale_id' LIMIT 1");
if ($saleQuery->num_rows > 0) {
    $sale = $saleQuery->fetch_assoc();
    $total_amount = $sale['total_amount'];
} else {
    echo 0; // Return error if sale not found
    exit;
}

// Step 5: Calculate totals with VAT, COVID tax, and discount
$vat_amount = round(($total_amount * $vat_percentage) / 100, 2);
$covid_tax_amount = round(($total_amount * $covid_tax_percentage) / 100, 2);
$discount_amount = round(($total_amount * $default_discount) / 100, 2);
$net_total = round($total_amount + $vat_amount + $covid_tax_amount - $discount_amount, 2);

// Step 6: Prepare data for each item and insert into `sales_receipt`
$cashier_name = $_SESSION['login_name']; // Assuming cashier's name is stored in session
$invoice_no = $sale['ref_no']; // Using ref_no as invoice_no

foreach ($product_ids as $index => $product_id) {
    // Fetch item description based on product_id
    $productQuery = $conn->query("SELECT name FROM products WHERE id = '$product_id' LIMIT 1");
    $item_description = ($productQuery->num_rows > 0) ? $productQuery->fetch_assoc()['name'] : 'Unknown Item';

    $qty = $quantities[$index];
    $price = $prices[$index];
    $amount = round($qty * $price, 2); // Total amount for the item

    // Prepare and execute insertion for each item
    $insertReceiptQuery = $conn->prepare("
        INSERT INTO sales_receipt 
        (sale_id, invoice_no, date, time, cashier_name, item_description, qty, price, amount, sub_total, discount, discount_percentage, total_sales_vat_ex, vat_percentage, vat_amount, covid_tax_percentage, covid_tax_amount, net_sales_amount, cash_paid) 
        VALUES (?, ?, NOW(), CURTIME(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $insertReceiptQuery->bind_param(
        "isssddddddddddddd",
        $sale_id,
        $invoice_no,
        $cashier_name,
        $item_description,
        $qty,
        $price,
        $amount,
        $total_amount, // Total for sub-total, assuming this is a placeholder for the item
        $discount_amount,
        $default_discount,
        $total_amount, // Total sales VAT Excl.
        $vat_percentage,
        $vat_amount,
        $covid_tax_percentage,
        $covid_tax_amount,
        $net_total,
        $_POST['cash_paid'] // Pass cash paid as well
    );

    if (!$insertReceiptQuery->execute()) {
        echo 0; // Error inserting receipt item
        exit;
    }
}

echo 1; // Success

$conn->close();
