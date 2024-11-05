<?php include('../db_connect.php') ?>
<style>
    body {
        height: 100vh; /* Make the body full height */
        overflow: hidden; /* Hide overflow on body to control scrolling with a wrapper */
    }

    .scrollable-container {
        height: 100%; /* Take full height of body */
        overflow-y: auto; /* Enable vertical scrolling */
    }

    .bg-gradient-primary {
        background: linear-gradient(149deg, rgba(119, 172, 233, 1) 5%, rgba(83, 163, 255, 1) 10%, rgba(46, 51, 227, 1) 41%, rgba(40, 51, 218, 1) 61%, rgba(75, 158, 255, 1) 93%, rgba(124, 172, 227, 1) 98%);
    }

    .btn-primary-gradient {
        background: linear-gradient(to right, #1e85ff 0%, #00a5fa 80%, #00e2fa 100%);
    }

    .btn-danger-gradient {
        background: linear-gradient(to right, #f25858 7%, #ff7840 50%, #ff5140 105%);
    }

    main .card {
        height: calc(100%);
        border: none; /* Removed borders for cleaner look */
    }

    main .card-body {
        height: calc(100%);
        overflow-y: auto; /* Only vertical scrolling */
        padding: 5px;
        position: relative;
        scrollbar-width: thin; /* Firefox */
        scrollbar-color: #888 #f1f1f1; /* Firefox scrollbar color */
    }

    #o-list {
        height: calc(87%);
        overflow-y: auto; /* Enable vertical scrolling */
    }

    #calc {
        position: absolute;
        bottom: 1rem;
        height: calc(10%);
        width: calc(98%);
    }

    .prod-item {
        min-height: 12vh;
        cursor: pointer;
        transition: opacity 0.2s; /* Smooth hover effect */
    }

    .prod-item:hover {
        opacity: .8;
    }

    .prod-item .card-body {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    input[name="qty[]"] {
        width: 30px;
        text-align: center;
    }

    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .cat-item {
        cursor: pointer;
        transition: opacity 0.2s; /* Smooth hover effect */
    }

    .cat-item:hover {
        opacity: .8;
    }

    /* Custom Scrollbar Styles */
    ::-webkit-scrollbar {
        width: 12px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1; /* Track color */
    }

    ::-webkit-scrollbar-thumb {
        background: #888; /* Scrollbar color */
        border-radius: 10px; /* Rounded corners */
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #555; /* Darker color on hover */
    }

    /* Additional styles for better aesthetics */
    .card-header {
        background: #e0e0e0; /* Light background for header */
        font-weight: bold; /* Bold header text */
        border-bottom: 2px solid #ccc; /* Bottom border for separation */
    }

    .btn {
        border-radius: 0.5rem; /* Rounded button corners */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Button shadow */
    }
</style>

<?php
function generateOrderNumber()
{
    global $conn;
    $date = date('YmdHis');
    return $date;
}

if (isset($_GET['id'])):
    $order = $conn->query("SELECT * FROM sales where id = {$_GET['id']}");
    foreach ($order->fetch_array() as $k => $v) {
        $$k = $v;
    }
    $items = $conn->query("SELECT o.*,p.name FROM sale_items o inner join products p on p.id = o.product_id where o.order_id = $id ");
endif;

$order_number = isset($order_number) ? $order_number : generateOrderNumber();
?>

<div class="container-fluid o-field scrollable-container">
    <div class="row mt-3 ml-3 mr-3">

        <div class="col-lg-8 p-field">
            <div class="card">
                <div class="card-header text-dark">
                    <b>Products</b>
                </div>
                <div class="card-body">
                    <div class="row justify-content-start align-items-center" id="cat-list">
                        <div class="mx-3 cat-item" data-id='all'>
                            <button class="btn btn-primary"><b class="text-white">All</b></button>
                        </div>
                        <?php
                        $qry = $conn->query("SELECT * FROM categories order by name asc");
                        while ($row = $qry->fetch_assoc()): ?>
                            <div class="mx-3 cat-item" data-id='<?php echo $row['id'] ?>'>
                                <button class="btn btn-primary"><?php echo ucwords($row['name']) ?></button>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <hr>

                    <table id="productTable" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $prod = $conn->query("SELECT * FROM products WHERE status = 1 ORDER BY name ASC");
                            while ($row = $prod->fetch_assoc()): ?>
                                <tr data-json='<?php echo json_encode($row) ?>' data-category-id="<?php echo $row['category_id'] ?>">
                                    <td><?php echo $row['name'] ?></td>
                                    <td><?php echo ucwords($row['category_id']); // Fetch category name if needed ?></td>
                                    <td><?php echo number_format($row['price'], 2) ?></td>
                                    <td>
                                        <button class="btn btn-primary add-to-order" data-id="<?php echo $row['id'] ?>">Add</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header text-dark">
                    <b>Order List</b>
                    <span class="float:right"><a class="btn btn-primary btn-sm col-sm-3 float-right" href="../index.php" id="">
                            <i class="fa fa-home"></i> Home
                        </a></span>
                </div>
                <div class="card-body">
                    <form action="" id="manage-order">
                        <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '' ?>">
                        <input type="hidden" name="order_number" value="<?php echo $order_number ?>">
                        <div class="bg-white" id='o-list'>
                            <div class="d-flex w-100 bg-white mb-1">
                                <label for="" class="text-dark"><b>Order No.</b></label>
                                <span class="form-control-sm"><?php echo $order_number ?></span>
                            </div>
                            <table class="table bg-light mb-5">
                                <colgroup>
                                    <col width="20%">
                                    <col width="40%">
                                    <col width="40%">
                                    <col width="5%">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>QTY</th>
                                        <th>Order</th>
                                        <th>Amount</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (isset($items)):
                                        while ($row = $items->fetch_assoc()):
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <span class=" btn-minus"><b> </b></span>
                                                        <input type="number" name="qty[]" id=""
                                                            value="<?php echo $row['qty'] ?>">
                                                        <span class="btn-plus"><b></b></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="hidden" name="item_id[]" id=""
                                                        value="<?php echo $row['id'] ?>">
                                                    <input type="hidden" name="product_id[]" id=""
                                                        value="<?php echo $row['product_id'] ?>"><?php echo ucwords($row['name']) ?>
                                                    <small class="psmall">
                                                        (<?php echo number_format($row['price'], 2) ?>)</small>
                                                </td>
                                                <td>
                                                    <input type="hidden" name="amount[]"
                                                        value="<?php echo $row['price'] * $row['qty'] ?>">
                                                    <b><?php echo number_format($row['price'] * $row['qty'], 2) ?></b>
                                                </td>
                                                <td>
                                                    <button class="btn btn-danger btn-sm remove-item" type="button">Remove</button>
                                                </td>
                                            </tr>
                                        <?php endwhile;
                                    endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="calc">
                            <div class="bg-light w-100 d-flex justify-content-between">
                                <b>Total:</b>
                                <b id="total">0.00</b>
                            </div>
                            <div class="d-flex w-100">
                                <button class="btn btn-danger col-sm-5 mx-1" id="btn-cancel">Cancel</button>
                                <button class="btn btn-primary col-sm-5 mx-1" id="btn-submit">Pay</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    var total;
    cat_func();
    $('#prod-list .prod-item').click(function () {
        var data = $(this).attr('data-json')
        data = JSON.parse(data)
        if ($('#o-list tr[data-id="' + data.id + '"]').length > 0) {
            var tr = $('#o-list tr[data-id="' + data.id + '"]')
            var qty = tr.find('[name="qty[]"]').val();
            qty = parseInt(qty) + 1;
            qty = tr.find('[name="qty[]"]').val(qty).trigger('change')
            calc()
            return false;
        }
        var tr = $('<tr class="o-item"></tr>')
        tr.attr('data-id', data.id)
        tr.append('<td><div class="d-flex align-items-center"><span class="btn-minus"><b></i></b></span><input type="number" name="qty[]" id="" value="1"><span class=" btn-plus"><b></b></span></div></td>')

        tr.append('<td><input type="hidden" name="item_id[]" id="" value=""><input type="hidden" name="product_id[]" id="" value="' + data.id + '">' + data.name + ' <small class="psmall">(' + (parseFloat(data.price).toLocaleString("en-US", { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 })) + ')</small></td>')

        tr.append('<td class="text-right"><input type="hidden" name="price[]" id="" value="' + data.price + '"><input type="hidden" name="amount[]" id="" value="' + data.price + '"><span class="amount">' + (parseFloat(data.price).toLocaleString("en-US", { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 })) + '</span></td>')

        tr.append('<td><span class="btn-rem"><b><i class="fa fa-trash-alt text"></i></b></span></td>')
        $('#o-list tbody').append(tr)
        qty_func()
        calc()
        cat_func();
    })
    function qty_func() {
        $('#o-list .btn-minus').click(function () {
            var qty = $(this).siblings('input').val()
            qty = qty > 1 ? parseInt(qty) - 1 : 1;
            $(this).siblings('input').val(qty).trigger('change')
            calc()
        })
        $('#o-list .btn-plus').click(function () {
            var qty = $(this).siblings('input').val()
            qty = parseInt(qty) + 1;
            $(this).siblings('input').val(qty).trigger('change')
            calc()
        })
        $('#o-list .btn-rem').click(function () {
            $(this).closest('tr').remove()
            calc()
        })

    }
    function calc() {
        $('[name="qty[]"]').each(function () {
            $(this).change(function () {
                var tr = $(this).closest('tr');
                var qty = $(this).val();
                var price = tr.find('[name="price[]"]').val()
                var amount = parseFloat(qty) * parseFloat(price);
                tr.find('[name="amount[]"]').val(amount)
                tr.find('.amount').text(parseFloat(amount).toLocaleString("en-US", { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }))

            })
        })
        var total = 0;
        $('[name="amount[]"]').each(function () {
            total = parseFloat(total) + parseFloat($(this).val())
        })
        console.log(total)
        $('[name="total_amount"]').val(total)
        $('#total_amount').text(parseFloat(total).toLocaleString("en-US", { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }))
    }
    function cat_func() {
        $('.cat-item').click(function () {
            var id = $(this).attr('data-id')
            console.log(id)
            if (id == 'all') {
                $('.prod-item').parent().toggle(true)
            } else {
                $('.prod-item').each(function () {
                    if ($(this).attr('data-category-id') == id) {
                        $(this).parent().toggle(true)
                    } else {
                        $(this).parent().toggle(false)
                    }
                })
            }
        })
    }

    $(document).ready(function() {
    // Initialize DataTable
    $('#productTable').DataTable();

    // Handle category filtering
    $('.cat-item').click(function() {
        var id = $(this).data('id');
        var table = $('#productTable').DataTable();
        if (id === 'all') {
            table.columns().search('').draw(); // Show all products
        } else {
            // Search based on category
            table.columns(1).search(id).draw(); // Assuming category is in the second column
        }
    });

    // Handle adding products to order
    $('#productTable').on('click', '.add-to-order', function() {
        var data = $(this).closest('tr').data('json');
        var qty = 1; // Default quantity

        if ($('#o-list tr[data-id="' + data.id + '"]').length > 0) {
            var tr = $('#o-list tr[data-id="' + data.id + '"]');
            var currentQty = tr.find('[name="qty[]"]').val();
            tr.find('[name="qty[]"]').val(parseInt(currentQty) + 1).trigger('change');
            calc();
            return false;
        }

        var newRow = `
            <tr class="o-item" data-id="${data.id}">
                <td>
                    <div class="d-flex align-items-center">
                        <span class="btn-minus"><b>-</b></span>
                        <input type="number" name="qty[]" value="${qty}">
                        <span class="btn-plus"><b>+</b></span>
                    </div>
                </td>
                <td>
                    <input type="hidden" name="item_id[]" value="">
                    <input type="hidden" name="product_id[]" value="${data.id}">${data.name}
                    <small class="psmall">(${parseFloat(data.price).toLocaleString('en-US', { style: 'decimal', minimumFractionDigits: 2 })})</small>
                </td>
                <td class="text-right">
                    <input type="hidden" name="price[]" value="${data.price}">
                    <input type="hidden" name="amount[]" value="${data.price}">
                    <span class="amount">${parseFloat(data.price).toLocaleString('en-US', { style: 'decimal', minimumFractionDigits: 2 })}</span>
                </td>
                <td>
                    <span class="btn-rem"><b><i class="fa fa-trash-alt"></i></b></span>
                </td>
            </tr>
        `;
        $('#o-list tbody').append(newRow);
        qty_func();
        calc();
    });
});


    $('#save_order').click(function () {
        $('#tendered').val('').trigger('change')
        $('[name="total_tendered"]').val('')
        $('#manage-order').submit()
    })
    $("#pay").click(function () {
        start_load()
        var amount = $('[name="total_amount"]').val()
        if ($('#o-list tbody tr').length <= 0) {
            alert_toast("Please add atleast 1 product first.", 'danger')
            end_load()
            return false;
        }
        $('#apayable').val(parseFloat(amount).toLocaleString("en-US", { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }))
        $('#pay_modal').modal('show')
        setTimeout(function () {
            $('#tendered').val('').trigger('change')
            $('#tendered').focus()
            end_load()
        }, 500)

    })
    $('#tendered').keyup('input', function (e) {
        if (e.which == 13) {
            $('#manage-order').submit();
            return false;
        }
        var tend = $(this).val()
        tend = tend.replace(/,/g, '')
        $('[name="total_tendered"]').val(tend)
        if (tend == '')
            $(this).val('')
        else
            $(this).val((parseFloat(tend).toLocaleString("en-US")))
        tend = tend > 0 ? tend : 0;
        var amount = $('[name="total_amount"]').val()
        var change = parseFloat(tend) - parseFloat(amount)
        $('#change').val(parseFloat(change).toLocaleString("en-US", { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }))
    })

    $('#tendered').on('input', function () {
        var val = $(this).val()
        val = val.replace(/[^0-9 \,]/, '');
        $(this).val(val)
    })
    $('#manage-order').submit(function (e) {
        e.preventDefault();
        start_load();
        $.ajax({
            url: '../ajax.php?action=save_order',
            method: 'POST',
            data: $(this).serialize(),
            success: function (resp) {
                if (resp > 0) {
                    if ($('[name="total_tendered"]').val() > 0) {
                        alert_toast("Data successfully saved.", 'success');
                        setTimeout(function () {
                            var nw = window.open('../receipt.php?id=' + resp, "_blank", "width=900,height=600");
                            // Keep the window open and allow the user to manually close it
                            setTimeout(function () {
                                nw.print();
                            }, 500);
                        }, 500);
                    } else {
                        alert_toast("Data successfully saved.", 'success');
                        setTimeout(function () {
                            location.reload();
                        }, 500);
                    }
                }
            }
        });
    });

</script>