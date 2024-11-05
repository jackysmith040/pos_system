<?php
session_start();
include 'db_connect.php';

// Ensure we have a sale ID from the query parameter
$sale_id = isset($_GET['id']) ? $_GET['id'] : 0; // Use GET instead of POST
if (!$sale_id) {
    echo "Invalid sale ID.";
    exit;
}

// Fetch all data for the sale from the sales_receipt table
$query = $conn->prepare("SELECT * FROM sales_receipt WHERE sale_id = ?"); // Fetching by sale_id
$query->bind_param("d", $sale_id); // Bind sale_id to the query
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    echo "Receipt not found.";
    exit;
}

// Initialize variables for calculations
$total_sub_amount = 0;
$total_discount_amount = 0;
$total_vat_amount = 0;
$total_covid_tax = 0;
$total_net_sales = 0;
$footer_message = "Thank you for your purchase!"; // Initialize footer message

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .receipt-container { width: 280px; margin: auto; padding: 10px; border: 1px solid #000; }
        .header, .footer { text-align: center; }
        .header { font-size: 16px; font-weight: bold; }
        .sub-header { font-size: 10px; }
        .invoice-info { font-size: 12px; font-weight: bold; margin-top: 5px; }
        .date-cashier { font-size: 10px; margin: 5px 0; }
        .item-table, .summary-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .item-table th, .item-table td { text-align: left; font-size: 10px; padding: 2px; }
        .summary-table td { text-align: right; font-size: 10px; }
        .total { font-weight: bold; }
        .footer { font-size: 10px; margin-top: 10px; }
        hr { border: none; border-top: 1px dashed #000; margin: 5px 0; }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="header">
        La Goil Shop (Go Cafe)<br>
        <span class="sub-header">P.O. Box C920, Cantonments, Accra</span><br>
        TIN#: C0004743008
    </div>

    <?php
    // Fetch and display invoice information
    $receipt = $result->fetch_assoc(); // Fetch the first row for invoice info
    ?>
    <div class="invoice-info">
        Invoice No: <span><?php echo htmlspecialchars($receipt['invoice_no']); ?></span><br>
        Date: <?php echo date("d-M-y"); ?> &nbsp;&nbsp;
        <?php echo date("h:i A"); ?><br>
        Cashier: <?php echo htmlspecialchars($_SESSION['login_name']); ?>
    </div>
    
    <hr>

    <table class="item-table">
        <tr>
            <th>Item</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Amount</th>
        </tr>
        <?php 
        do {
            $item_amount = number_format($receipt['amount'], 2);
            $total_sub_amount += $item_amount;
            $total_discount_amount += isset($receipt['discount_amount']) ? $receipt['discount_amount'] : 0; // Safely access discount_amount
            $total_vat_amount += isset($receipt['vat_amount']) ? $receipt['vat_amount'] : 0; // Safely access vat_amount
            $total_covid_tax += isset($receipt['covid_tax_amount']) ? $receipt['covid_tax_amount'] : 0; // Safely access covid_tax_amount
            $total_net_sales += isset($receipt['net_sales_amount']) ? $receipt['net_sales_amount'] : 0; // Safely access net_sales_amount
        ?>
            <tr>
                <td><?php echo htmlspecialchars($receipt['item_description']); ?></td>
                <td><?php echo $receipt['qty']; ?></td>
                <td><?php echo number_format($receipt['price'], 2); ?></td>
                <td><?php echo $item_amount; ?></td>
            </tr>
        <?php } while ($receipt = $result->fetch_assoc()); ?>
    </table>

    <table class="summary-table">
        <tr>
            <td>Total Amount:</td>
            <td class="total"><?php echo number_format($total_sub_amount, 2); ?></td>
        </tr>
        <tr>
            <td>Discount:</td>
            <td class="total"><?php echo number_format($total_discount_amount, 2); ?></td>
        </tr>
        <tr>
            <td>VAT:</td>
            <td class="total"><?php echo number_format($total_vat_amount, 2); ?></td>
        </tr>
        <tr>
            <td>COVID Tax:</td>
            <td class="total"><?php echo number_format($total_covid_tax, 2); ?></td>
        </tr>
        <tr>
            <td>Net Total:</td>
            <td class="total"><?php echo number_format($total_net_sales, 2); ?></td>
        </tr>
    </table>

    <div class="footer">
        <hr>
        <p><?php echo htmlspecialchars($footer_message); ?></p>
    </div>
</div>

</body>
</html>

<?php
$conn->close();
?>
