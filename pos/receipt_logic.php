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
extract($settings, EXTR_PREFIX_ALL, 'config'); // Use `config_*` variables

// Step 4: Fetch sale data from `sales` table using the provided sale_id
$saleQuery = $conn->query("SELECT * FROM sales WHERE id = '$sale_id' LIMIT 1");
if ($saleQuery->num_rows === 0) {
    echo 0; // Error if sale not found
    exit;
}
$sale = $saleQuery->fetch_assoc();
$cash_paid = $_POST['cash_paid'] ?? 0;

// Step 5: Calculate the total amount for all items
$total_amount = 0;
foreach ($product_ids as $index => $product_id) {
    $qty = $quantities[$index];
    $price = $prices[$index];
    $amount = round($qty * $price, 2); // Total amount for the item
    $total_amount += $amount; // Accumulate total amount
}

// Step 6: Calculate the Discount Amount on the total amount
$discount_amount = $total_amount * ($config_default_discount / 100); // Total discount

// Step 7: Calculate the Net Sales Amount (Subtotal minus Discount)
$net_sales_amount = $total_amount - $discount_amount;

// Step 8: Calculate VAT and COVID Tax on the Net Sales Amount
$vat_amount = $net_sales_amount * ($config_vat_percentage / 100);
$covid_tax_amount = $net_sales_amount * ($config_covid_tax_percentage / 100);

// Step 9: Calculate Total Sales VAT Exclusive
$total_sales_vat_ex = round($net_sales_amount - ($vat_amount + $covid_tax_amount), 2); // Correct calculation for total sales VAT exclusive

// Step 10: Round values for final display
$discount_amount = round($discount_amount, 2);
$vat_amount = round($vat_amount, 2);
$covid_tax_amount = round($covid_tax_amount, 2);
$net_sales_amount = round($net_sales_amount, 2);

// Step 11: Calculate Change Due based on cash paid
$change_due = round($cash_paid - $net_sales_amount, 2);

// Step 12: Prepare data for each item and insert into `sales_receipt`
$cashier_name = $_SESSION['login_name'] ?? 'Unknown'; // Assuming cashier's name in session
$invoice_no = $sale['ref_no']; // Using ref_no as invoice_no

// Prepare the insert query for sales receipt
$insertReceiptQuery = $conn->prepare("
    INSERT INTO sales_receipt 
    (sale_id, invoice_no, date, time, cashier_name, item_description, qty, price, amount, sub_total, discount, discount_percentage, total_sales_vat_ex, vat_percentage, vat_amount, covid_tax_percentage, covid_tax_amount, net_sales_amount, cash_paid, change_due, contact_tel) 
    VALUES (?, ?, NOW(), CURTIME(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// Loop through each item and insert into `sales_receipt`
foreach ($product_ids as $index => $product_id) {
    // Fetch item description based on product_id
    $productQuery = $conn->query("SELECT name FROM products WHERE id = '$product_id' LIMIT 1");
    $item_description = $productQuery->num_rows > 0 ? $productQuery->fetch_assoc()['name'] : 'Unknown Item';

    $qty = $quantities[$index];
    $price = $prices[$index];
    $amount = round($qty * $price, 2); // Total amount for the item

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
        $total_amount, // Sub-total for the item
        $discount_amount,
        $config_default_discount,
        $total_sales_vat_ex, // Total sales VAT Exclusive (final net sales amount)
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
