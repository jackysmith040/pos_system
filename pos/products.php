<?php include('db_connect.php'); ?>

<div class="container-fluid">
	<div class="col-lg-12">
		<div class="row">
			<!-- Form Panel omitted for brevity -->

			<!-- Table Panel -->
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>Product List</b>
						<span class="float:right"><a class="btn btn-primary btn-block btn-sm col-sm-2 float-right" href="index.php?page=add-product" id="new_order">
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
								// Load categories into cname array
								$cname = [];
								$qry = $conn->query("SELECT * FROM categories ORDER BY name ASC");
								while($row = $qry->fetch_assoc()) {
									$cname[$row['id']] = ucwords($row['name']);
								}

								// Fetch products
								$product = $conn->query("SELECT * FROM products ORDER BY id ASC");
								while($row = $product->fetch_assoc()):
								?>
								<tr>
									<td class="text-center"><?php echo $i++ ?></td>
									<td class="">
										<?php 
										// Check if category exists in $cname array
										echo isset($cname[$row['category_id']]) ? $cname[$row['category_id']] : 'Unknown Category'; 
										?>
									</td>
									<td class=""><?php echo $row['name'] ?></td>
									<td class=""><?php echo $row['description'] ?></td>
									<td class=""><?php echo number_format($row['price'], 2) ?></td>
									<td class=""><?php echo $row['stock'] ?></td> <!-- Display stock value -->
									<td class="">
										<?php echo $row['status'] == 1 ? "Available" : "Unavailable" ?>
									</td>
									<td class="text-center">
										<button class="btn btn-danger btn-sm delete_product" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash-alt"></i></button>
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
	td p {
		margin: unset;
	}
	.custom-switch {
		cursor: pointer;
	}
	.custom-switch * {
		cursor: pointer;
	}
</style>

<script>
	// Script for managing product form
	$('#manage-product').on('reset', function() {
		$('input:hidden').val('');
		$('.select2').val('').trigger('change');
	});
	
	$('#manage-product').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url:'ajax.php?action=save_product',
			data: new FormData($(this)[0]),
		    cache: false,
		    contentType: false,
		    processData: false,
		    method: 'POST',
		    type: 'POST',
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully added",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
				else if(resp==2){
					alert_toast("Data successfully updated",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	})
	$('.edit_product').click(function(){
		start_load()
		var cat = $('#manage-product')
		cat.get(0).reset()
		cat.find("[name='id']").val($(this).attr('data-id'))
		cat.find("[name='name']").val($(this).attr('data-name'))
		cat.find("[name='description']").val($(this).attr('data-description'))
		cat.find("[name='price']").val($(this).attr('data-price'))
		cat.find("[name='category_id']").val($(this).attr('data-category_id')).trigger('change')
		if($(this).attr('data-status') == 1)
			$('#status').prop('checked',true)
		else
			$('#status').prop('checked',false)
		end_load()
	})
	$('.delete_product').click(function(){
		_conf("Are you sure to delete this product?","delete_product",[$(this).attr('data-id')])
	})
	function delete_product($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_product',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	}
	$('table').dataTable()
</script>
