<?php 
include('db_connect.php'); 
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sale = $conn->query("SELECT details FROM sales WHERE id = $id");
    if ($sale->num_rows > 0) {
        $details = $sale->fetch_assoc()['details'];
        $items = json_decode($details, true);
    } else {
        $items = [];
    }
}
?>

<div class="container-fluid">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $item['name']; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
