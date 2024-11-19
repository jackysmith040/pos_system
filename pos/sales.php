<?php include('db_connect.php'); ?>
<style>
    input[type=checkbox] {
        transform: scale(1.3);
        padding: 10px;
        cursor: pointer;
    }
</style>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="row mb-4 mt-4">
            <div class="col-md-12"></div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <b>Sales Summary</b>
                        <span class="float:right">
                            <a class="btn btn-primary btn-sm col-sm-2 float-right" href="billing/index.php"
                                id="new_order">
                                <i class="fa fa-plus"></i> New Sale
                            </a>
                        </span>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <!-- <th>Items</th> New Column for Item Details -->
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                $sales = $conn->query("SELECT * FROM sales ORDER BY unix_timestamp(date_created) DESC");
                                while ($row = $sales->fetch_assoc()):
                                    // Decode JSON details for items
                                    $items = json_decode($row['details'], true);
                                    $item_summary = "";
                                    foreach ($items as $item) {
                                        $item_summary .= $item['name'] . " (" . $item['quantity'] . "), ";
                                    }
                                    $item_summary = rtrim($item_summary, ", ");
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i++ ?></td>
                                        <td>
                                            <p><?php echo date("M d, Y h:i A", strtotime($row['date_created'])) ?></p>
                                        </td>
                                        <td>
                                            <p><?php echo $row['ref_no'] ?></p>
                                        </td>
                                        <!-- <td>
                                            <p><?php echo $item_summary ?></p>
                                        </td> Display Items -->
                                        <td>
                                            <p class="text-right"><?php echo number_format($row['total_amount'], 2) ?></p>
                                        </td>
                                        <td>
                                            <p><?php echo $row['payment_method'] ?></p>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info view_items" type="button"
                                                data-id="<?php echo $row['id'] ?>">
                                                <i class="fa fa-eye"></i> View
                                            </button>
                                            
                                            <?php if($_SESSION['login_type'] == 1):?>
                                            <button class="btn btn-sm btn-danger delete_order" type="button"
                                                data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash-alt"></i>
                                                Delete</button>
                                            <?php endif;?>
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
</div>

<style>
    td {
        vertical-align: middle !important;
    }

    td p {
        margin: unset;
    }
</style>

<script>
    $(document).ready(function () {
        $('table').dataTable()
    });

    $('.view_items').click(function () {
        uni_modal("Sales Items", "view_items_details.php?id=" + $(this).attr('data-id'), "mid-large");
    });

    $('.delete_order').click(function () {
        _conf("Are you sure to delete this order?", "delete_order", [$(this).attr('data-id')])
    });

    function delete_order(id) {
        start_load()
        $.ajax({
            url: 'ajax.php?action=delete_order',
            method: 'POST',
            data: { id: id },
            success: function (resp) {
                if (resp == 1) {
                    alert_toast("Data successfully deleted", 'success')
                    setTimeout(function () {
                        location.reload()
                    }, 1500)
                }
            }
        })
    }
</script>