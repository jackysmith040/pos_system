<?php
session_start();
include 'db_connect.php';  // Database connection

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

//  Just for the invoice
// $sales = $conn->query("SELECT * FROM sales ORDER BY unix_timestamp(date_created) DESC");

$invoiceQuery = $conn->query("SELECT MAX(order_number) as max_order_number FROM sales");

if ($invoiceQuery->num_rows > 0) {
    $currentInvoice = $invoiceQuery->fetch_assoc()['max_order_number'];
    $currentInvoice = $currentInvoice+ 1;
    // str_pad($currentInvoice, 8, '0', STR_PAD_LEFT);
    $invoice_no = str_pad($currentInvoice, 8, '0', STR_PAD_LEFT);
} else {
    $invoice_no = str_pad('1', 8, '0', STR_PAD_LEFT);;
}

// echo '<pre>';
// echo $invoice_no;
// echo '</pre>';
// die();
// $invoice_no = $sale['ref_no'];



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

    // Step 4: Check current stock level before updating
    $stockCheckQuery = $conn->query("SELECT stock FROM products WHERE id = '$product_id' LIMIT 1");
    if ($stockCheckQuery->num_rows > 0) {
        $currentStock = $stockCheckQuery->fetch_assoc()['stock'];

        // Ensure stock is sufficient before proceeding
        if ($currentStock < $qty) {
            echo 0; // Insufficient stock, cannot complete sale
            exit;
        }
    } else {
        echo 0; // Product not found
        exit;
    }

    // Update stock by reducing quantity
    $stockUpdate = $conn->query("UPDATE products SET stock = stock - $qty WHERE id = '$product_id'");
    if (!$stockUpdate) {
        echo 0; // Error updating stock
        exit;
    }

    // Check if stock is zero or below after update to mark as unavailable
    if (($currentStock - $qty) <= 0) {
        $conn->query("UPDATE products SET status = 0 WHERE id = '$product_id'");
    }

    // Fetch item description based on product_id
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
