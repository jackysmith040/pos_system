<?php
// session_start();

// Check if user is logged in by checking for a specific session variable, e.g., 'user_id'
if (!isset($_SESSION['login_id'])) {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit(); // Always call exit after a header redirect to prevent further execution
}