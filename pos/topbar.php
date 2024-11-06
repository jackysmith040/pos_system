



<style>
  
 /* Style for the logo */
.custom-logo {
  position: relative; 
  /* left: 2rem; */
  transition: transform 0.3s ease; /* Smooth transition for scaling */
}

.custom-logo:hover {
  transform: scale(1.05);
}

</style>

<nav class="navbar navbar-light fixed-top bg-white">
  <div class="container-fluid mt-2 mb-2">
    <div class="row w-100">
      <div class="col-md-2 d-flex align-items-center">
        <!-- Logo Section -->
        <img src="./assets/uploads/fav-ico.svg" width="100px" height="40px" class="custom-logo" alt="Logo">
      </div>

      <div class="col-md-8">
        <!-- Optional Alert Section (Commented Out) -->
        <!-- <p style="color: red; font-size: 14px;"><b>Alert</b> This Project is developed for Academic study purpose only. | Never Sell or Distribute with your Name OR Branding. </p> -->
      </div>

      <div class="col-md-2 d-flex justify-content-end align-items-center">
        <!-- Account Dropdown -->
        <div class="dropdown">
          <a href="#" class="text-dark dropdown-toggle" id="account_settings" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?php echo $_SESSION['login_name']; ?>
          </a>
          <div class="dropdown-menu" aria-labelledby="account_settings">
            <a class="dropdown-item" href="index.php?page=sales_report"><i class="fa fa-chart-line"></i> Sales Report</a>
            <a class="dropdown-item" href="javascript:void(0)" id="manage_my_account"><i class="fa fa-user-cog"></i> Manage Account</a>
            <a class="dropdown-item" href="ajax.php?action=logout"><i class="fa fa-power-off"></i> Logout</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>

<script>
  $('#manage_my_account').click(function () {
    uni_modal("Manage Account", "manage_user.php?id=<?php echo $_SESSION['login_id'] ?>&mtype=own")
  })
</script>

