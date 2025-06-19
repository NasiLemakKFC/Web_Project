<?php
    session_start();
    if (isset($_SESSION["username"])) {
        $_SESSION = array();
        session_destroy();
        echo "<meta http-equiv=\"refresh\" content=\"2;URL=login.html\">";
    }
?>