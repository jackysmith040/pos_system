<?php
session_start();
include 'db_connect.php';

// Step 1: Validate sale_id
if (empty($_POST['sale_id'])) {
    echo 0; // Error if sale_id is missing
    exit;
}
$sale_id = $_POST['sale_id'];

// Step 2: Validate and retrieve arrays for items
$product_ids = $_POST['product_ids'] ?? [];
$quantities = $_POST['quantities'] ?? [];
$prices = $_POST['prices'] ?? [];

if (count($product_ids) !== count($quantities) || count($quantities) !== count($prices)) {
    echo 0; // Error if array lengths do not match
    exit;
}

// Step 3: Fetch configurable values from `system_settings`
$configQuery = $conn->query("SELECT * FROM system_settings LIMIT 1");
if ($configQuery->num_rows === 0) {
    echo 0; // Error if settings not found
    exit;
}
$settings = $configQuery->fetch_assoc();
extract($settings, EXTR_PREFIX_ALL, 'config');

// Step 4: Initialize totals
$total_amount = 0;
foreach ($product_ids as $index => $product_id) {
    $qty = $quantities[$index];
    $price = $prices[$index];
    $amount = round($qty * $price, 2);
    $total_amount += $amount;
}

// Step 5: Calculate discount, VAT, and COVID tax
$discount_amount = $total_amount * ($config_default_discount / 100);
$total_sales_vat_ex = $total_amount - $discount_amount;
$vat_amount = $total_sales_vat_ex * ($config_vat_percentage / 100);
$covid_tax_amount = $total_sales_vat_ex * ($config_covid_tax_percentage / 100);
$net_sales_amount = $total_sales_vat_ex + $vat_amount + $covid_tax_amount;

// Step 6: Calculate change due
$cash_paid = $_POST['cash_paid'];
$change_due = $cash_paid - $total_sales_vat_ex;

// Prepare cashier name from session
$cashier_name = $_SESSION['login_name'] ?? 'Unknown';

// Step 7: Generate invoice number
$invoiceQuery = $conn->query("SELECT MAX(order_number) as max_order_number FROM sales");
$currentInvoice = $invoiceQuery->num_rows > 0 ? $invoiceQuery->fetch_assoc()['max_order_number'] + 1 : 1;
$invoice_no = str_pad($currentInvoice, 8, '0', STR_PAD_LEFT);

// Step 8: Prepare and execute insert for each item
$insertReceiptQuery = $conn->prepare("
    INSERT INTO sales_receipt 
    (sale_id, invoice_no, date, time, cashier_name, item_description, qty, price, amount, sub_total, discount, discount_percentage, total_sales_vat_ex, vat_percentage, vat_amount, covid_tax_percentage, covid_tax_amount, net_sales_amount, cash_paid, change_due) 
    VALUES (?, ?, NOW(), CURTIME(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($product_ids as $index => $product_id) {
    $qty = $quantities[$index];
    $price = $prices[$index];
    $amount = round($qty * $price, 2);

    // Check stock level
    $stockCheckQuery = $conn->query("SELECT stock FROM products WHERE id = '$product_id' LIMIT 1");
    if ($stockCheckQuery->num_rows > 0) {
        $currentStock = $stockCheckQuery->fetch_assoc()['stock'];
        if ($currentStock < $qty) {
            echo 0; // Insufficient stock
            exit;
        }
    } else {
        echo 0; // Product not found
        exit;
    }

    // Update stock
    $conn->query("UPDATE products SET stock = stock - $qty WHERE id = '$product_id'");

    // Fetch item description
    $productQuery = $conn->query("SELECT name FROM products WHERE id = '$product_id' LIMIT 1");
    $item_description = $productQuery->num_rows > 0 ? $productQuery->fetch_assoc()['name'] : 'Unknown Item';

    // Bind and execute insert for each item
    $insertReceiptQuery->bind_param(
        "isssdddddddddddddd",
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
        $change_due
    );

    if (!$insertReceiptQuery->execute()) {
        echo 0; // Error inserting receipt item
        exit;
    }
}

echo 1; // Success
$conn->close();
