<?php include('db_connect.php'); ?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="row">
            <!-- Table Panel -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <b>Product List</b>
                        <span class="float:right"><a class="btn btn-primary btn-block btn-sm col-sm-2 float-right"
                                href="index.php?page=add-product" id="new_order">
                                <i class="fa fa-plus"></i> Add Product </a></span>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Stock</th> <!-- New Stock Column -->
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                $cname = [];
                                $qry = $conn->query("SELECT * FROM categories ORDER BY name ASC");
                                while ($row = $qry->fetch_assoc()) {
                                    $cname[$row['id']] = ucwords($row['name']);
                                }

                                $product = $conn->query("SELECT * FROM products ORDER BY id ASC");
                                while ($row = $product->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i++ ?></td>
                                        <td><?php echo isset($cname[$row['category_id']]) ? $cname[$row['category_id']] : 'Unknown Category'; ?>
                                        </td>
                                        <td><?php echo $row['name'] ?></td>
                                        <td><?php echo $row['description'] ?></td>
                                        <td><?php echo number_format($row['price'], 2) ?></td>
                                        <td><?php echo $row['stock'] ?></td>
                                        <td><?php echo $row['status'] == 1 ? "Available" : "Unavailable" ?></td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <button class="btn btn-primary btn-sm edit_product" type="button"> <a
                                                        href="index.php?page=edit-product&id=<?php echo $row['id'] ?>"><i
                                                            class="fa fa-edit"></i></a>
                                                </button>
                                                <button class="btn btn-danger btn-sm delete_product" type="button"
                                                    data-id="<?php echo $row['id'] ?>"><i
                                                        class="fa fa-trash-alt"></i></button>



                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Table Panel -->
        </div>
    </div>
</div>

<style>
    td {
        vertical-align: middle !important;
    }

    .btn-group .btn {
        margin-right: 5px;
    }
</style>

<script>
    $('.delete_product').click(function () {
        _conf("Are you sure to delete this product?", "delete_product", [$(this).attr('data-id')])
    })
    function delete_product($id) {
        start_load()
        $.ajax({
            url: 'ajax.php?action=delete_product',
            method: 'POST',
            data: { id: $id },
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
    $('table').dataTable()
</script>