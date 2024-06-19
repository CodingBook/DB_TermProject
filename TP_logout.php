<?php
    session_start();
    session_unset();
    session_destroy();
    header("Location: TP_Login.php");
    exit();
?>