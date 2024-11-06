<?php include('../db_connect.php') ?>
<style>
    /* body {
        height: 30vh !important;
        /* Make the body full height */
        overflow: scroll !important ;
        /* Hide overflow on body to control scrolling with a wrapper */
    } */

    .scrollable-container {
        height: 100%;
        /* Take full height of body */
        overflow-y: auto;
        /* Enable vertical scrolling */
    }

    .view-order {
        height: 100vh;
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
        height: calc(80%);
        border: none;
        /* Removed borders for cleaner look */
    }

    main .card-body {
        height: calc(100%);
        overflow-y: auto;
        /* Only vertical scrolling */
        padding: 5px;
        position: relative;
        scrollbar-width: thin;
        /* Firefox */
        scrollbar-color: #888 #f1f1f1;
        /* Firefox scrollbar color */
    }

    #o-list {
        height: 50vh !important;
        overflow-y: auto;
        /* Enable vertical scrolling */
    }

    #calc {
        position: absolute;
        bottom: 1rem;
        /* height: calc(10%);
        width: calc(98%); */
    }

    .prod-item {
        min-height: 30vh;
        cursor: pointer;
        transition: opacity 0.2s;
        /* Smooth hover effect */
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
        transition: opacity 0.2s;
        /* Smooth hover effect */
    }

    .cat-item:hover {
        opacity: .8;
    }

    /* Custom Scrollbar Styles */
    ::-webkit-scrollbar {
        width: 12px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        /* Track color */
    }

    ::-webkit-scrollbar-thumb {
        background: #888;
        /* Scrollbar color */
        border-radius: 10px;
        /* Rounded corners */
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #555;
        /* Darker color on hover */
    }

    /* Additional styles for better aesthetics */
    .card-header {
        background: #e0e0e0;
        /* Light background for header */
        font-weight: bold;
        /* Bold header text */
        border-bottom: 2px solid #ccc;
        /* Bottom border for separation */
    }

    .btn {
        border-radius: 0.5rem;
        /* Rounded button corners */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        /* Button shadow */
    }

    .order-container {
        height: 100%;
        /* Set a height for the container to allow scrolling */
        overflow-y: auto;
        /* Enable vertical scrolling */
    }

    .card-body {
        padding: 0;
        overflow-y: auto;
        /* Enable vertical scrolling */ 
        position: relative;
        scrollbar-width: thin;
        /* Firefox scrollbar color */
        scrollbar-color: #888 #f1f1f1;
        /* Remove default padding for a cleaner look */
    }

    /* Style for selected radio button */
    input[type="radio"]:checked+label {
        font-weight: bold;
        color: #007bff;
        /* Change color to your preference */
    }

    .radio_btns-row {
        /* width: 2rem; */
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        gap: 1rem;

    }

 

    .prod-list {
        /* Set a height for the container to allow scrolling */
        overflow-y: scroll !important;
        /* Enable vertical scrolling */
    
    }

.product-container .products{
    height: 78vh !important;
}
  
</style>
<?php
function generateOrderNumber()
{
    global $conn;
    // Get the current date and time
    // $date = date('YmdHis');
    // You can also use a sequential number from the database
    $result = $conn->query("SELECT MAX(order_number) as max_order_number FROM sales");
    $row = $result->fetch_assoc();
    $order_number = $row['max_order_number'] + 1;
    return str_pad($order_number, 8, '0', STR_PAD_LEFT);
    // return $date;
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
<div class="view-order">
    <div class="row">

        <div class="col-lg-8 product-container">
            <div class="products pb-5">
                <div class="card-header text-dark">
                    <b>Products</b>
                </div>
                <div class="card-body">
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
                        <tbody class="prod-list">
                            <?php
                            // Fetch all products where status = 1
                            $prod = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 1 ORDER BY p.name ASC");
                            while ($row = $prod->fetch_assoc()): ?>
                                <tr data-json='<?php echo json_encode($row) ?>'
                                    data-category-id="<?php echo $row['category_id'] ?>">
                                    <td><?php echo $row['name'] ?></td>
                                    <td><?php echo ucwords($row['category_name']); // Displaying the actual category name ?>
                                    </td>
                                    <td><?php echo number_format($row['price'], 2) ?></td>
                                    <td>
                                        <button class="btn btn-primary add-to-order"
                                            data-id="<?php echo $row['id'] ?>">Add</button>
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
                    <span class="float:right"><a class="btn btn-primary btn-sm col-sm-3 float-right"
                            href="../index.php?page=sales_report" id="">
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
                                                <td class="text-right">
                                                    <input type="hidden" name="price[]" id=""
                                                        value="<?php echo $row['price'] ?>">
                                                    <input type="hidden" name="amount[]" id=""
                                                        value="<?php echo $row['amount'] ?>">
                                                    <span class="amount"><?php echo number_format($row['amount'], 2) ?></span>
                                                </td>
                                                <td>
                                                    <span class=" btn-rem"><b><i class="fa fa-trash-alt"></i></b></span>
                                                </td>
                                            </tr>
                                            <script>
                                                $(document).ready(function () {
                                                    qty_func()
                                                    calc()
                                                    cat_func();
                                                })
                                            </script>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-block bg-white" id="calc">
                            <table class="" width="100%">
                                <tbody>
                                    <tr>
                                        <td><b>
                                                <h6>Total</h6>
                                            </b></td>
                                        <td class="text-right">
                                            <input type="hidden" name="total_amount" value="0">
                                            <input type="hidden" name="total_tendered" value="0">
                                            <span class="">
                                                <h6><b id="total_amount">0.00</b></h6>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Hidden container for order items to be sent as POST data -->
                        <div id="order_items"></div>

                        <input type="hidden" name="cash_paid" id="cash_paid">

                    </form>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex flex-column align-items-center">
                    <!-- Payment Method Radio Buttons -->
                    <div class="form-group col-sm-8">
                        <!-- <label style="position: relative; top: -0.80rem;">Payment Method</label> -->
                        <radio-row class="radio_btns-row">
                        <div>
                            <label for="payment_method_cash">
                                <input type="radio" id="payment_method_cash" name="payment_method" value="Cash" checked>
                                Cash
                            </label>
                        </div>
                        <div>
                            <label for="payment_method_mobile_money">
                                <input type="radio" id="payment_method_mobile_money" name="payment_method"
                                    value="Mobile Money"> Mobile Money
                            </label>
                        </div>
                        </radio-row>
                    </div>


                    <!-- Proceed to Pay Button -->
                    <button class="btn btn-primary col-sm-6 py-2" type="button" id="pay">Proceed to Pay</button>

                </div>
            </div>


        </div>
    </div>
</div>
<div class="modal fade" id="pay_modal" role='dialog'>
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><b>Pay</b></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="form-group">
                        <label for="">Amount Payable</label>
                        <input type="number" class="form-control text-right" id="apayable" readonly="" value="">
                    </div>
                    <div class="form-group">
                        <label for="">Amount Tendered</label>
                        <input type="text" class="form-control text-right" id="tendered" value="" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="">Change</label>
                        <input type="text" class="form-control text-right" id="change" value="0.00" readonly="">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-sm" form="manage-order">Pay</button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
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


        // For each item added, update the hidden form fields
        updateOrderFormFields();
    })

    function updateOrderFormFields() {
        // Clear previous items
        $('#order_items').empty();

        // Loop through each item in the order list and add hidden inputs for each field
        $('#o-list tbody tr').each(function () {
            var product_id = $(this).find('[name="product_id[]"]').val();
            var qty = $(this).find('[name="qty[]"]').val();
            var price = $(this).find('[name="price[]"]').val();

            // Append hidden fields for each product's ID, quantity, and price
            $('#order_items').append(`
                <input type="hidden" name="product_ids[]" value="${product_id}">
                <input type="hidden" name="quantities[]" value="${qty}">
                <input type="hidden" name="prices[]" value="${price}">
            `);
        });
    }


    function qty_func() {
        $('#o-list').on('click', '.btn-minus', function () {
            var qty = $(this).siblings('input').val()
            qty = qty > 1 ? parseInt(qty) - 1 : 1;
            $(this).siblings('input').val(qty).trigger('change')
            calc()
        });

        $('#o-list').on('click', '.btn-plus', function () {
            var qty = $(this).siblings('input').val()
            qty = parseInt(qty) + 1;
            $(this).siblings('input').val(qty).trigger('change')
            calc()
        });

        $('#o-list').on('click', '.btn-rem', function () {
            $(this).closest('tr').remove()
            calc()
        });
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

    $(document).ready(function () {
        // Initialize DataTable
        $('#productTable').DataTable();

        // Handle category filtering
        $('.cat-item').click(function () {
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
        $('#productTable').on('click', '.add-to-order', function () {
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
        <div class="d-flex align-items-center justify-content-between">
            <span class="btn-minus" style="cursor: pointer;"><b>-</b></span>
            <input type="number" name="qty[]" value="${qty}" min="1" style="width: 60px; text-align: center;">
            <span class="btn-plus" style="cursor: pointer;"><b>+</b></span>
        </div>
    </td>
    <td>
        <input type="hidden" name="item_id[]" value="">
        <input type="hidden" name="product_id[]" value="${data.id}">
        <span class="font-weight-bold">${data.name}</span>
        <small class="psmall text-muted">(${parseFloat(data.price).toLocaleString('en-US', { style: 'decimal', minimumFractionDigits: 2 })})</small>
    </td>
    <td class="text-right">
        <input type="hidden" name="price[]" value="${data.price}">
        <input type="hidden" name="amount[]" value="${data.price}">
        <span class="amount font-weight-bold">${parseFloat(data.price).toLocaleString('en-US', { style: 'decimal', minimumFractionDigits: 2 })}</span>
    </td>
    <td>
        <span class="btn-rem" style="cursor: pointer;"><b><i class="fa fa-trash-alt text-danger"></i></b></span>
    </td>
</tr>
`;

            $('#o-list tbody').append(newRow);
            qty_func();
            calc();
        });
    });


    // $('#save_order').click(function () {
    //     $('#tendered').val('').trigger('change')
    //     $('[name="total_tendered"]').val('')
    //     $('#manage-order').submit()
    // })

    $('#pay_modal').on('hide.bs.modal', function () {
        $('#cash_paid').val($('#tendered').val()); // Set cash paid value in form
    });

    $("#pay").click(function () {
        start_load()
        var amount = $('[name="total_amount"]').val()
        if ($('#o-list tbody tr').length <= 0) {
            alert_toast("Please add atleast 1 product first.", 'danger')
            end_load()
            return false;
        }


        // Check if a payment method is selected
        var paymentMethod = $('input[name="payment_method"]:checked').val();
        if (paymentMethod === undefined) {
            alert_toast("Please select a payment method.", 'danger');
            end_load();
            return false;
        }
        console.log("Selected Payment Method: ", paymentMethod); // Log the selected payment method



        let payableAmount = parseFloat(amount);

        // Ensure amount is valid
        if (!isNaN(payableAmount) && payableAmount >= 0) {
            $('#apayable').val(payableAmount); // Directly set value without formatting
        } else {
            $('#apayable').val('0.00');
        }

        updateOrderFormFields();

        $('#pay_modal').modal('show');
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

        // Get the selected payment method
        var paymentMethod = $('input[name="payment_method"]:checked').val();
        var cashPaid = $('#tendered').val().replace(/,/g, '');

        // Prepare the data object
        var formData = $(this).serialize() + '&payment_method=' + paymentMethod + '&cash_paid=' + cashPaid;



        $.ajax({
            url: '../ajax.php?action=save_order',
            method: 'POST',
            data: formData,
            success: function (resp) {
                if (resp > 0) {
                    // After saving the order, call receipt_logic.php
                    $.ajax({
                        url: '../receipt_logic.php',
                        method: 'POST',
                        data: {
                            sale_id: resp,
                            cash_paid: cashPaid,
                            product_ids: $('[name="product_ids[]"]').map(function () { return this.value; }).get(),
                            quantities: $('[name="quantities[]"]').map(function () { return this.value; }).get(),
                            prices: $('[name="prices[]"]').map(function () { return this.value; }).get(),
                        },
                        success: function () {
                            if ($('[name="total_tendered"]').val() > 0) {
                                alert_toast("Data successfully saved.", 'success');
                                setTimeout(function () {
                                    // Open a new window for printing
                                    var printWindow = window.open('../receipt.php?id=' + resp, "_blank", "width=900,height=600");

                                    // Wait for the new window to load
                                    printWindow.onload = function () {
                                        printWindow.print(); // Trigger the print dialog

                                        // Event listener for when the print dialog is closed
                                        printWindow.onafterprint = function () {
                                            printWindow.close(); // Close the window after printing



                                            // Clear the sales_receipt record
                                            $.ajax({
                                                url: '../clear_receipt.php',
                                                method: 'POST',
                                                data: { sale_id: resp }, // Pass the sale ID
                                                success: function () {
                                                    location.reload(); // Refresh the page
                                                }
                                            });
                                        };

                                        // Additional event listener to handle closing the window manually
                                        printWindow.onbeforeunload = function () {
                                            location.reload(); // Refresh the current page if the window is closed manually
                                        };
                                    };
                                }, 500);
                            } else {
                                alert_toast("Data successfully saved.", 'success');
                                setTimeout(function () {
                                    location.reload();
                                }, 500);
                            }
                        },
                        error: function () {
                            alert_toast("Error generating receipt.", 'danger');
                        }
                    });
                }
            }
        });
    });

</script>