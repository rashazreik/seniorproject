<?php
// Include database connection
require_once 'includes/config.php';

// Start the session
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: index.php");
exit;
?>