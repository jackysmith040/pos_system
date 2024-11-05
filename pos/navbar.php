<style>
	.collapse a{
		text-indent:10px;
	}
	nav#sidebar{
		/*background: url(assets/uploads/<?php echo $_SESSION['system']['cover_img'] ?>) !important*/
	}
</style>

<nav id="sidebar" class='mx-lt-5 bg-white'>
	
	<div class="sidebar-list">
		<?php if($_SESSION['login_type'] == 1): ?>
			<a href="index.php?page=home" class="nav-item nav-home"><span class='icon-field'><i class="fa fa-tachometer-alt mr-3"></i></span> Dashboard</a>
		<?php endif; ?>

		<a href="index.php?page=sales" class="nav-item nav-sales"><span class='icon-field'><i class="fa fa-clipboard-list mr-3"></i></span> Sales</a>
		<a href="billing/index.php" class="nav-item nav-takesales"><span class='icon-field'><i class="fa fa-list-ol mr-3"></i></span> Take Sales</a>
		<a href="index.php?page=sales_report" class="nav-item nav-sales_report"><span class='icon-field'><i class="fa fa-th-list mr-3"></i></span> Sales Report</a>


		<?php if($_SESSION['login_type'] == 1): ?>
			<a href="index.php?page=categories" class="nav-item nav-categories"><span class='icon-field'><i class="fa fa-list-alt mr-3"></i></span>Manage Categories</a>
			<a href="index.php?page=add-category" class="nav-item nav-categories"><span class='icon-field'><i class="fa fa-list-alt mr-3"></i></span>Add Categories</a>
			<a href="index.php?page=products" class="nav-item nav-products"><span class='icon-field'><i class="fa fa-th-list mr-3"></i></span>Manage Products</a>
			<a href="index.php?page=add-product" class="nav-item nav-products"><span class='icon-field'><i class="fa fa-th-list mr-3"></i></span>Add Products</a>
			<a href="index.php?page=users" class="nav-item nav-users"><span class='icon-field'><i class="fa fa-users mr-3"></i></span> Users</a>
			<a href="index.php?page=site_settings" class="nav-item nav-site_settings"><span class='icon-field'><i class="fa fa-cogs mr-3"></i></span> System Settings</a>
		<?php endif; ?>
	</div>
</nav>

<script>
	$('.nav_collapse').click(function(){
		console.log($(this).attr('href'))
		$($(this).attr('href')).collapse()
	})
	$('.nav-<?php echo isset($_GET['page']) ? $_GET['page'] : '' ?>').addClass('active')

	function adjustSidebarHeight() {
    const navbar = document.querySelector('.navbar');
    const sidebar = document.querySelector('#sidebar');
    
    if (navbar && sidebar) {
        const navbarHeight = navbar.offsetHeight;
        sidebar.style.height = `calc(100vh - ${navbarHeight}px)`;
    }
}

// Call the function on page load and window resize
window.addEventListener('load', adjustSidebarHeight);
window.addEventListener('resize', adjustSidebarHeight);
</script>