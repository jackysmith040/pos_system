<?php
include 'db_connect.php';
$month = isset($_GET['month']) ? $_GET['month'] : date('m'); // Only month
$day = isset($_GET['day']) ? $_GET['day'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <b>Sales Report</b>
            </div>
            <div class="card-body">
                <div class="row justify-content-center pt-4">
                    <label for="year" class="mt-2">Year</label>
                    <div class="col-sm-3">
                        <input type="number" name="year" id="year" value="<?php echo $year ?>" class="form-control" min="2000" max="<?php echo date('Y'); ?>">
                    </div>
                    <label for="month" class="mt-2">Month</label>
                    <div class="col-sm-3">
                        <select name="month" id="month" class="form-control">
                            <?php
                            for ($m = 1; $m <= 12; $m++) {
                                $selected = ($m == $month) ? 'selected' : '';
                                echo "<option value='$m' $selected>" . date("F", mktime(0, 0, 0, $m, 1)) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <label for="day" class="mt-2">Day</label>
                    <div class="col-sm-3">
                        <input type="number" name="day" id="day" value="<?php echo $day ?>" class="form-control" min="1" max="31">
                    </div>
                </div>
                <hr>
                <div class="col-md-12">
                    <table class="table table-bordered" id='report-list'>
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th>Date</th>
                                <th>Invoice</th>
                                <th>Items</th> <!-- New column for item details -->
                                <th>Amount</th>
                                <th>Payment Method</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $i = 1;
                        $total = 0;
                        $date_filter = "YEAR(date_created) = '$year'";
                        if (!empty($month)) {
                            $date_filter .= " AND MONTH(date_created) = $month";
                        }
                        if (!empty($day)) {
                            $date_filter .= " AND DAY(date_created) = $day";
                        }
                        $sales = $conn->query("SELECT * FROM sales WHERE amount_tendered > 0 AND $date_filter ORDER BY UNIX_TIMESTAMP(date_created) ASC");

                        if ($sales->num_rows > 0):
                            while ($row = $sales->fetch_array()):
                                $total += $row['total_amount'];
                                $items = json_decode($row['details'], true);
                                $item_summary = "";
                                foreach ($items as $item) {
                                    $item_summary .= $item['name'] . " (" . $item['quantity'] . "), ";
                                }
                                $item_summary = rtrim($item_summary, ", ");
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $i++ ?></td>
                                <td><p><b><?php echo date("M d, Y", strtotime($row['date_created'])) ?></b></p></td>
                                <td><p><b><?php echo $row['ref_no'] ?></b></p></td>
                                <td><p><b><?php echo $item_summary ?></b></p></td> <!-- Display items -->
                                <td><p class="text-right"><b><?php echo number_format($row['total_amount'], 2) ?></b></p></td>
                                <td><p><b><?php echo $row['payment_method'] ?? 'N/A'; ?></b></p></td>
                            </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                        <tr>
                            <th class="text-center" colspan="6">No Data.</th>
                        </tr>
                        <?php 
                        endif; 
                        ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">Total</th>
                                <th class="text-right"><?php echo number_format($total, 2) ?></th>
                                <th></th> <!-- Empty cell for alignment -->
                            </tr>
                        </tfoot>
                    </table>
                    <hr>
                    <div class="col-md-12 mb-4">
                        <center>
                            <button class="btn btn-success btn-sm col-sm-3" type="button" id="print"><i class="fa fa-print"></i> Print</button>
                        </center>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<noscript>
    <style>
        table#report-list {
            width: 100%;
            border-collapse: collapse;
        }
        table#report-list td, table#report-list th {
            border: 1px solid;
        }
        p {
            margin: unset;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</noscript>

<script>
$('#month, #day, #year').change(function(){
    const month = $('#month').val();
    const day = $('#day').val();
    const year = $('#year').val();
    location.replace(`index.php?page=sales_report&month=${month}&day=${day}&year=${year}`);
});

$('#print').click(function(){
    var _c = $('#report-list').clone();
    var ns = $('noscript').clone();
    ns.append(_c);
    var nw = window.open('', '_blank', 'width=1600,height=700');
    nw.document.write('<p class="text-center"><b>Sales Report as of <?php echo date("F, Y", strtotime($year . "-" . $month . "-01")) ?></b></p>');
    nw.document.write(ns.html());
    nw.document.close();
    nw.print();
    setTimeout(() => {
        nw.close();
    }, 500);
});
</script>
