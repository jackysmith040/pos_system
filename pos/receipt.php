<?php
session_start();
include 'db_connect.php';

// Ensure we have a sale ID from the query parameter
$sale_id = isset($_GET['id']) ? $_GET['id'] : 0;
if (!$sale_id) {
    echo "Invalid sale ID.";
    exit;
}

// Fetch all data for the sale from the sales_receipt table
$query = $conn->prepare("SELECT * FROM sales_receipt WHERE sale_id = ?");
$query->bind_param("d", $sale_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo "Receipt not found.";
    exit;
}

// Initialize totals
$total_sub_amount = 0;
$total_discount_amount = 0;
$total_vat_amount = 0;
$total_covid_tax = 0;
$cash_paid = 0;
$change_due = 0;

// Fetch receipt data
$receipt_data = [];
while ($receipt = $result->fetch_assoc()) {
    $receipt_data[] = $receipt;
    $item_amount = $receipt['amount'];
    $total_sub_amount += $item_amount;
    $total_discount_amount += $receipt['discount']; // Assuming discount per item
    $cash_paid = $receipt['cash_paid'];
    $change_due = $receipt['change_due'];
}

// Fetch settings for dynamic percentages and footer message
$settingsQuery = $conn->query("SELECT vat_percentage, covid_tax_percentage, default_discount, footer_message, contact FROM system_settings LIMIT 1");
if ($settingsQuery->num_rows === 0) {
    echo "Settings not found.";
    exit;
}
$settings = $settingsQuery->fetch_assoc();
$vat_percentage = $settings['vat_percentage'];
$covid_tax_percentage = $settings['covid_tax_percentage'];
$default_discount = $settings['default_discount'];
$footer_message = $settings['footer_message'];
$contact_tel = $settings['contact'];

// Calculate totals
$discount_amount = ($total_sub_amount * $default_discount) / 100; // Discount on total
$total_after_discount = $total_sub_amount - $discount_amount; // Total after discount

// Calculate VAT and COVID tax based on the discounted total
$total_vat_amount = ($total_after_discount * $vat_percentage) / 100; // VAT based on discounted total
$total_covid_tax = ($total_after_discount * $covid_tax_percentage) / 100; // COVID tax based on discounted total

// Calculate Total Sales VAT Exclusive (Net Sales Amount - VAT - COVID Tax)
$total_sales_vat_ex = $total_after_discount - ($total_vat_amount + $total_covid_tax); // Total sales VAT exclusive

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sales Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .receipt-container {
            width: 280px;
            margin: auto;
            padding: 10px;
            border: 1px solid #000;
        }

        .header,
        .footer {
            text-align: center;
        }

        .header {
            font-size: 16px;
            font-weight: bold;
        }

        .sub-header {
            font-size: 10px;
        }

        .invoice-info {
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
            text-align: left;
        }

        .date-cashier {
            font-size: 10px;
            margin: 5px 0;
        }

        .item-table,
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .item-table th,
        .item-table td {
            text-align: left;
            font-size: 10px;
            padding: 2px;
        }

        .summary-table td {
            text-align: right;
            font-size: 10px;
        }

        .total {
            font-weight: bold;
        }

        .footer {
            font-size: 10px;
            margin-top: 10px;
        }

        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .invoice-header {
            background-color: #000;
            color: #fff;
            padding: 3px;
            font-weight: bold;
            text-align: center;
        }

        .footer-message {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="receipt-container">
        <div class="header">
            La Goil Shop (Go Cafe)<br>
            <span class="sub-header">P.O. Box C920, Cantonments, Accra</span><br>
            TIN#: C0004743008
        </div>

        <div class="invoice-header">Invoice No: <?php echo htmlspecialchars($receipt_data[0]['invoice_no']); ?></div>
        <div class="invoice-info">
            Date: <?php echo date("d-M-y"); ?> &nbsp;&nbsp; <?php echo date("h:i A"); ?><br>
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
            <?php foreach ($receipt_data as $receipt): ?>
                <tr>
                    <td><?php echo htmlspecialchars($receipt['item_description']); ?></td>
                    <td><?php echo $receipt['qty']; ?></td>
                    <td><?php echo number_format($receipt['price'], 2); ?></td>
                    <td><?php echo number_format($receipt['amount'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <hr>

        <table class="summary-table">
            <tr>
                <td>Sub-Total:</td>
                <td class="total"><?php echo number_format($total_sub_amount, 2); ?></td>
            </tr>
            <tr>
                <td>Discount (<?php echo number_format($default_discount, 2); ?>%):</td>
                <td class="total"><?php echo number_format($discount_amount, 2); ?></td>
            </tr>
            <tr>
                <td>Total Sales VAT Exclusive:</td> <!-- Changed label here -->
                <td class="total"><?php echo number_format($total_sales_vat_ex, 2); ?></td>
                <!-- This is the net sales amount -->
            </tr>
            <tr>
                <td><?php echo number_format($vat_percentage, 2); ?>% VAT:</td>
                <td class="total"><?php echo number_format($total_vat_amount, 2); ?></td>
            </tr>
            <tr>
                <td><?php echo number_format($covid_tax_percentage, 2); ?>% COVID Tax:</td>
                <td class="total"><?php echo number_format($total_covid_tax, 2); ?></td>
            </tr>
            <tr>
                <td>Total Sales (VAT Exc.):</td> <!-- Changed label here -->
                <td class="total"><?php echo number_format($total_after_discount, 2); ?></td>
                <!-- This is the total after discount -->
            </tr>
            <tr>
                <td>Cash Paid:</td>
                <td class="total"><?php echo number_format($cash_paid, 2); ?></td>
            </tr>
            <tr>
                <td>Change Due:</td>
                <td class="total"><?php echo number_format($change_due, 2); ?></td>
            </tr>
        </table>

        <div class="footer">
            <hr>
            <p class="footer-message"><?php echo htmlspecialchars($footer_message); ?></p>
            <p>TEL #: <span style="font-weight: bold;"><?php echo htmlspecialchars($contact_tel); ?></span></p>
            <p>Software By DonzyTech: 055 931 5905 / 024 508 3745</p>
        </div>
    </div>

</body>

</html>

<?php
$conn->close();
?>