<?php
    /* 세션 재설정 */

    session_start();
    session_unset();
    session_destroy();
    header("Location: TP_Login.php");
    exit();
?>