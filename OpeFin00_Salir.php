<?php
    session_start();
    session_unset();
    session_destroy();
    header("Location: OpeFin00_home.php");
?>