<?php
session_start();

// Destroy session for any user type
$_SESSION = array();
session_destroy();

// Show logout message and redirect
echo "<script>
    alert('You have been logged out.');
    window.location.href='../auth/login.html';
</script>";
?>

