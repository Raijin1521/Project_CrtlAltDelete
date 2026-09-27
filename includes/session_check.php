<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /Project_CtrlAltDelete/pages/auth/login.php");
    exit;
}
?>