<?php
// Include the database connection
include('db_connect.php');
// include('redirect_to_login.php');

// Initialize total variables
$total_sales = 0;
$total_categories = 0;
$total_products = 0;
$total_users = 0;

// Function to get count from database
function getCount($conn, $table)
{
    $sql = "SELECT COUNT(*) AS total FROM $table";
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc()['total'] : 0;
}

// Get totals
$total_sales = getCount($conn, 'sales');
$total_categories = getCount($conn, 'categories');
$total_products = getCount($conn, 'products');
$total_users = getCount($conn, 'users');

// Initialize the array to store data points for the chart
$dataPoints = array();

// SQL query to fetch sales totals by date
$sql = "SELECT date_created, SUM(total_amount) AS total_sales 
        FROM sales 
        GROUP BY DATE(date_created) 
        ORDER BY date_created ASC";

$result = $conn->query($sql);

// Check if data exists
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Convert date_created to UNIX timestamp
        $timestamp = strtotime($row['date_created']) * 1000; // multiplied by 1000 for JavaScript
        // Add the data point with x as date (timestamp) and y as total sales
        $dataPoints[] = array("x" => $timestamp, "y" => floatval($row['total_sales']));
    }
}

// JSON encode data points
$jsonDataPoints = json_encode($dataPoints, JSON_NUMERIC_CHECK);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kruxton Dashboard</title>
    <link rel="stylesheet" href="./assets/css/home.php.css">
    <script src="assets/js/charts/canvasjs.min.js"></script>
</head>

<body>
    <div class="container-fluid">
        <div class="row mt-3 ml-3 mr-3 dashcard">
            <div class="col-md-12 mb-3">
                <div class="card bg-light shadow-sm border-0">
                    <div class="card-body">
                        <h4 class="text-dark">Dashboard Overview</h4>
                        <div id="chartContainer" style="height: 400px; width: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards with counts -->
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card bg-light shadow-sm border-0">
                    <div class="card-body">
                        <h4 class="text-dark">Category</h4>
                        <h3 class="mt-2"><?php echo $total_categories ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-light shadow-sm border-0">
                    <div class="card-body">
                        <h4 class="text-dark">Sales</h4>
                        <h3 class="mt-2"><?php echo $total_sales ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-light shadow-sm border-0">
                    <div class="card-body">
                        <h4 class="text-dark">Product</h4>
                        <h3 class="mt-2"><?php echo $total_products ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-light shadow-sm border-0">
                    <div class="card-body">
                        <h4 class="text-dark">User</h4>
                        <h3 class="mt-2"><?php echo $total_users ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Panel -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <b>List of Sales</b>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th>Sales Number</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                $sales = $conn->query("SELECT * FROM sales ORDER BY unix_timestamp(date_created) DESC");
                                if ($sales === false) {
                                    die("Error executing query: " . $conn->error);
                                }
                                while ($row = $sales->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i++ ?></td>
                                        <td><?php echo date("M d,Y", strtotime($row['date_created'])) ?></td>
                                        <td><?php echo $row['amount_tendered'] > 0 ? $row['ref_no'] : 'N/A' ?></td>
                                        <td><?php echo $row['order_number'] ?></td>
                                        <td class="text-right"><?php echo number_format($row['total_amount'], 2) ?></td>
                                        <td class="text-center">
                                            <?php if ($row['amount_tendered'] > 0): ?>
                                                <span class="badge badge-success">Paid</span>
                                            <?php else: ?>
                                                <span class="badge badge-primary">Unpaid</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function () {
        var chart = new CanvasJS.Chart("chartContainer", {
            animationEnabled: true,
            theme: "light2",
            title: {
                text: "Sales Over Time"
            },
            axisX: {
                title: "Date",
                valueFormatString: "MMM DD, YYYY", // Display format for the date
                crosshair: {
                    enabled: true
                }
            },
            axisY: {
                title: "Total Sales (GHC)",
                prefix: "Ghc",
                crosshair: {
                    enabled: true
                }
            },
            data: [{
                type: "spline", // Use spline for a smooth line chart
                markerSize: 5,
                xValueType: "dateTime", // Specifies that the x-values are dateTime
                dataPoints: <?php echo $jsonDataPoints; ?> // Insert the encoded data points
            }]
        });
        chart.render();
    }
    </script>
    <!-- <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script> -->
</body>

</html>