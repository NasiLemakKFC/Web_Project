<?php
session_start();

// Destroy session for any user type
$_SESSION = array();
session_destroy();

// Show logout message
echo "You have been logged out.";
echo "<meta http-equiv=\"refresh\" content=\"2;URL=../auth/login.html\">";
?>
