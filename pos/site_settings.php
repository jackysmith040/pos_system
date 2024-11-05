<link rel="stylesheet" href="assets/wysiwyg/css/froala_editor.css">
<link rel="stylesheet" href="assets/wysiwyg/css/froala_style.css">
<link rel="stylesheet" href="assets/wysiwyg/css/plugins/code_view.css">
<link rel="stylesheet" href="assets/wysiwyg/css/plugins/draggable.css">
<link rel="stylesheet" href="assets/wysiwyg/css/plugins/colors.css">
<link rel="stylesheet" href="assets/wysiwyg/css/plugins/emoticons.css">
<link rel="stylesheet" href="assets/wysiwyg/css/plugins/image_manager.css">
<link rel="stylesheet" href="assets/wysiwyg/css/plugins/image.css">
<link rel="stylesheet" href="assets/wysiwyg/css/plugins/line_breaker.css">
<link rel="stylesheet" href="assets/wysiwyg/css/plugins/table.css">
<link rel="stylesheet" href="assets/wysiwyg/css/plugins/char_counter.css">
<link rel="stylesheet" href="assets/wysiwyg/css/plugins/video.css">
<link rel="stylesheet" href="assets/wysiwyg/css/plugins/fullscreen.css">
<link rel="stylesheet" href="assets/wysiwyg/css/plugins/file.css">
<link rel="stylesheet" href="assets/wysiwyg/css/plugins/quick_insert.css">
<link rel="stylesheet" href="assets/wysiwyg/css/plugins/help.css">
<link rel="stylesheet" href="assets/wysiwyg/css/third_party/spell_checker.css">
<link rel="stylesheet" href="assets/wysiwyg/css/plugins/special_characters.css">

<script src="assets/wysiwyg/js/froala_editor.min.js"></script>
<script src="assets/wysiwyg/js/plugins/align.min.js"></script>
<script src="assets/wysiwyg/js/plugins/char_counter.min.js"></script>
<script src="assets/wysiwyg/js/plugins/code_beautifier.min.js"></script>
<script src="assets/wysiwyg/js/plugins/code_view.min.js"></script>
<script src="assets/wysiwyg/js/plugins/colors.min.js"></script>
<script src="assets/wysiwyg/js/plugins/draggable.min.js"></script>
<script src="assets/wysiwyg/js/plugins/emoticons.min.js"></script>
<script src="assets/wysiwyg/js/plugins/entities.min.js"></script>
<script src="assets/wysiwyg/js/plugins/file.min.js"></script>
<script src="assets/wysiwyg/js/plugins/font_size.min.js"></script>
<script src="assets/wysiwyg/js/plugins/font_family.min.js"></script>
<script src="assets/wysiwyg/js/plugins/fullscreen.min.js"></script>
<script src="assets/wysiwyg/js/plugins/image.min.js"></script>
<script src="assets/wysiwyg/js/plugins/image_manager.min.js"></script>
<script src="assets/wysiwyg/js/plugins/line_breaker.min.js"></script>
<script src="assets/wysiwyg/js/plugins/inline_style.min.js"></script>
<script src="assets/wysiwyg/js/plugins/link.min.js"></script>
<script src="assets/wysiwyg/js/plugins/lists.min.js"></script>
<script src="assets/wysiwyg/js/plugins/paragraph_format.min.js"></script>
<script src="assets/wysiwyg/js/plugins/paragraph_style.min.js"></script>
<script src="assets/wysiwyg/js/plugins/quick_insert.min.js"></script>
<script src="assets/wysiwyg/js/plugins/quote.min.js"></script>
<script src="assets/wysiwyg/js/plugins/table.min.js"></script>
<script src="assets/wysiwyg/js/plugins/save.min.js"></script>
<script src="assets/wysiwyg/js/plugins/url.min.js"></script>
<script src="assets/wysiwyg/js/plugins/video.min.js"></script>
<script src="assets/wysiwyg/js/plugins/help.min.js"></script>
<script src="assets/wysiwyg/js/plugins/print.min.js"></script>
<script src="assets/wysiwyg/js/third_party/spell_checker.min.js"></script>
<script src="assets/wysiwyg/js/plugins/special_characters.min.js"></script>
<script src="assets/wysiwyg/js/plugins/word_paste.min.js"></script>


<!-- PHP to fetch system settings -->
<?php
include 'db_connect.php';
$settings = [];
$query = $conn->query("SELECT * FROM system_settings LIMIT 1");
if ($query->num_rows > 0) {
	$settings = $query->fetch_assoc();
}
?>

<style>
	/* Styles for messages */
	#message {
		position: fixed;
		top: 120px;
		right: 120px;
		z-index: 1000;
		max-width: 300px;
	}

	.alert {
		padding: 15px;
		margin-bottom: 20px;
		border-radius: 5px;
	}

	.alert-success {
		background-color: #dff0d8;
		color: #3c763d;
	}

	.alert-danger {
		background-color: #f2dede;
		color: #a94442;
	}

	.alert-info {
		background-color: #d9edf7;
		color: #31708f;
	}

	img#coverImage {
		max-height: 10vh;
		max-width: 6vw;
	}

	.container-fluid {
		margin-top: 20px;
	}

	.card {
		margin-bottom: 20px;
	}
</style>

<div class="container-fluid">
	<div class="card col-lg-12">
		<div class="card-body">
			<form action="" id="settings-form">
				<div class="form-group">
					<label for="system_name" class="control-label">System Name</label>
					<input type="text" class="form-control" id="system_name" name="name"
						value="<?php echo htmlspecialchars($settings['name'] ?? ''); ?>" required>
				</div>

				<div class="form-group">
					<label for="about_content" class="control-label">About Content</label>
					<textarea name="about" class="text-jqte" id="about_content" required>
		<?php
		// Retrieve the content or set to an empty string if not available
		$about_content = $settings['about_content'] ?? '';

		// Decode any HTML entities to return to a readable format
		$about_content = htmlspecialchars_decode($about_content);

		// Strip all tags to remove any potential HTML markup
		$about_content = strip_tags($about_content, '<br>');

		// Convert <br> tags to newlines for readability in textarea
		$about_content = str_replace("<br>", "\n", $about_content);

		// Remove any additional HTML entities or unwanted characters
		$about_content = filter_var($about_content, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

		// Trim any excessive whitespace
		$about_content = trim($about_content);

		echo $about_content;
		?>
	</textarea>
				</div>




				<div class="form-group">
					<label for="cover_image" class="control-label">Image</label>
					<input type="file" class="form-control" name="img" onchange="previewImage(this)">
				</div>

				<div class="form-group">
					<img src="<?php echo isset($settings['cover_img']) ? 'assets/uploads/' . htmlspecialchars($settings['cover_img']) : ''; ?>"
						alt="" id="coverImage">
				</div>

				<hr>

				<div class="form-group">
					<label for="vat_percentage" class="control-label">VAT Percentage (%)</label>
					<input type="number" class="form-control" id="vat_percentage" name="vat_percentage" step="0.01"
						value="<?php echo htmlspecialchars($settings['vat_percentage'] ?? '0.00'); ?>" required>
				</div>

				<div class="form-group">
					<label for="covid_tax_percentage" class="control-label">COVID Tax Percentage (%)</label>
					<input type="number" class="form-control" id="covid_tax_percentage" name="covid_tax_percentage"
						step="0.01" value="<?php echo htmlspecialchars($settings['covid_tax_percentage'] ?? '0.00'); ?>"
						required>
				</div>

				<div class="form-group">
					<label for="default_discount" class="control-label">Default Discount (%)</label>
					<input type="number" class="form-control" id="default_discount" name="default_discount" step="0.01"
						value="<?php echo htmlspecialchars($settings['default_discount'] ?? '0.00'); ?>" required>
				</div>

				<div class="form-group">
					<label for="footer_message" class="control-label">Footer Message</label>
					<textarea name="footer_message" class="form-control" rows="3"
						required><?php echo htmlspecialchars($settings['footer_message'] ?? ''); ?></textarea>
				</div>

				<div class="form-group">
					<label for="contact_info" class="control-label">Contact Info</label>
					<input type="text" class="form-control" id="contact_info" name="contact"
						value="<?php echo htmlspecialchars($settings['contact'] ?? ''); ?>" required>
				</div>

				<center>
					<button type="submit" id="save-settings-button"
						class="btn btn-primary btn-block col-md-2">Save</button>
				</center>

				<div id="message"></div>
			</form>
		</div>
	</div>
</div>

<script>
	// Image Preview Function
	function previewImage(input) {
		if (input.files && input.files[0]) {
			const reader = new FileReader();
			reader.onload = function (e) {
				document.getElementById('coverImage').src = e.target.result;
			}
			reader.readAsDataURL(input.files[0]);
		}
	}

	// Initialize Froala Editor
	new FroalaEditor('#about_content', {
		heightMin: '40vh',
		imageUploadParam: 'img',
		imageUploadURL: 'ajax.php?action=save_page_img',
		imageUploadMethod: 'POST',
		imageMaxSize: 5 * 1024 * 1024,
		imageAllowedTypes: ['jpeg', 'jpg', 'png'],
		events: {
			'image.beforeUpload': () => start_load(),
			'image.uploaded': () => end_load(),
			'image.replaced': ($img, response) => console.log($img, response)
		}
	});

	document.getElementById('settings-form').addEventListener('submit', function (e) {
		e.preventDefault(); // Prevent default form submission

		// Clear previous message
		const messageDiv = document.getElementById('message');
		messageDiv.style.display = 'none';
		messageDiv.innerHTML = '';

		const formData = new FormData(this); // Create FormData object from the form


		$.ajax({
			url: 'ajax.php?action=save_settings',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (response) {
				let message = '';

				// Check for success
				if (response == 1) {
					message = '<div class="alert alert-success">Settings saved successfully!</div>';
				} else {
					message = '<div class="alert alert-danger">Error saving settings. Please try again.</div>';
				}

				// Display the message and ensure it fades out completely
				$('#message').html(message).fadeIn(200).delay(1000).fadeOut(200, function () {
					// Ensure the message is fully hidden
					$(this).hide();
				});
			},
			error: function () {
				$('#message').html('<div class="alert alert-danger">An unexpected error occurred.</div>')
					.fadeIn(200).delay(1000).fadeOut(200, function () {
						// Ensure the message is fully hidden
						$(this).hide();
					});
			}
		});

	});
</script>