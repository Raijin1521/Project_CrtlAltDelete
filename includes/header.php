<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusTrace | Lost & Found</title>
    <link rel="stylesheet" href="/Project_CtrlAltDelete/assets/css/styles.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">CampusTrace</div>
    <div class="nav-links">
        <a href="/Project_CtrlAltDelete/pages/index.php">Dashboard</a>
        <a href="/Project_CtrlAltDelete/pages/lost_report.php">Report Lost</a>
        <a href="/Project_CtrlAltDelete/pages/found_report.php">Post Found</a>
        <a href="/Project_CtrlAltDelete/pages/items_list.php">Browse Items</a>
        <a href="/Project_CtrlAltDelete/pages/my_items.php">My Posts</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="/Project_CtrlAltDelete/pages/admin/dashboard.php" class="admin-link">Admin Panel</a>
        <?php endif; ?>
        <a href="/Project_CtrlAltDelete/pages/auth/logout.php">Logout</a>
    </div>
</nav>
<main class="container">
