<?php 
include('db_connect.php');

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $qry = $conn->query("SELECT * FROM products WHERE id = $id");
    if($qry->num_rows > 0) {
        $product = $qry->fetch_assoc();
    }
}
?>

<div class="container-fluid">
	<div class="col-lg-12">
		<div class="row">
			<!-- FORM Panel -->
			<div class="col-md-12">
				<form action="" id="manage-product">
					<div class="card">
						<div class="card-header">
							   Edit Product Form
					  	</div>
						<div class="card-body">
							<!-- Hidden ID Field -->
							<input type="hidden" name="id" value="<?php echo isset($product['id']) ? $product['id'] : '' ?>">
							
							<div class="form-group">
								<label class="control-label">Category</label>
								<select name="category_id" id="category_id" class="custom-select select2" required>
									<option value=""></option>
									<?php
									$qry = $conn->query("SELECT * FROM categories ORDER BY name ASC");
									while($row = $qry->fetch_assoc()):
									?>
										<option value="<?php echo $row['id'] ?>" <?php echo isset($product['category_id']) && $product['category_id'] == $row['id'] ? 'selected' : '' ?>>
											<?php echo ucwords($row['name']) ?>
										</option>
									<?php endwhile; ?>
								</select>
							</div>

							<div class="form-group">
								<label class="control-label">Name</label>
								<input type="text" class="form-control" name="name" value="<?php echo isset($product['name']) ? $product['name'] : '' ?>" required>
							</div>

							<div class="form-group">
								<label class="control-label">Description</label>
								<textarea name="description" id="description" cols="30" rows="4" class="form-control" required><?php echo isset($product['description']) ? $product['description'] : '' ?></textarea>
							</div>

							<div class="form-group">
								<label class="control-label">Price</label>
								<input type="number" class="form-control text-left" name="price" value="<?php echo isset($product['price']) ? $product['price'] : '' ?>" required>
							</div>

							<div class="form-group">
								<label class="control-label">Stock</label>
								<input type="number" class="form-control text-left" name="stock" value="<?php echo isset($product['stock']) ? $product['stock'] : '' ?>" required>
							</div>

							<div class="form-group">
								<div class="custom-control custom-switch">
									<input type="checkbox" class="custom-control-input" id="status" name="status" value="1" <?php echo isset($product['status']) && $product['status'] == 1 ? 'checked' : '' ?>>
									<label class="custom-control-label" for="status">Available</label>
								</div>
							</div>
						</div>
						
						<div class="card-footer">
							<div class="row">
								<div class="col-md-12 text-center">
									<button class="btn btn-primary">Save</button>
									<button class="btn btn-default" type="button" onclick="$('#manage-product').get(0).reset()">Cancel</button>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
			<!-- FORM Panel -->
		</div>
	</div>	
</div>

<style>
	td {
		vertical-align: middle !important;
	}
	td p {
		margin:unset;
	}
	.custom-switch {
		cursor: pointer;
	}
	.custom-switch * {
		cursor: pointer;
	}
</style>

<script>
	$('#manage-product').on('reset', function() {
		$('input:hidden').val('')
		$('.select2').val('').trigger('change')
	})
	
	$('#manage-product').submit(function(e) {
		e.preventDefault()
		start_load()
		$.ajax({
			url: 'ajax.php?action=save_product',
			data: new FormData($(this)[0]),
		    cache: false,
		    contentType: false,
		    processData: false,
		    method: 'POST',
		    type: 'POST',
			success: function(resp) {
				if (resp == 1) {
					alert_toast("Data successfully added", 'success')
					setTimeout(function() {
						location.href = 'index.php?page=products'
					}, 1500)
				} else if (resp == 2) {
					alert_toast("Data successfully updated", 'success')
					setTimeout(function() {
						location.href = 'index.php?page=products'
					}, 1500)
				}
			}
		})
	})
</script>
<!-- 
	$('.edit_product').click(function() {
		start_load()
		var cat = $('#manage-product')
		cat.get(0).reset()
		cat.find("[name='id']").val($(this).attr('data-id'))
		cat.find("[name='name']").val($(this).attr('data-name'))
		cat.find("[name='description']").val($(this).attr('data-description'))
		cat.find("[name='price']").val($(this).attr('data-price'))
		cat.find("[name='stock']").val($(this).attr('data-stock'))
		cat.find("[name='category_id']").val($(this).attr('data-category_id')).trigger('change')
		if ($(this).attr('data-status') == 1)
			$('#status').prop('checked', true)
		else
			$('#status').prop('checked', false)
		end_load()
	})
</script> -->
