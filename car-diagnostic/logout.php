<?php
// logout.php - User Logout
require_once 'config.php';

if(isLoggedIn()) {
    // Log activity
    logActivity($_SESSION['user_id'], 'logout', 'User logged out');
    
    // Destroy session
    session_unset();
    session_destroy();
}

redirect('login.php');
?>