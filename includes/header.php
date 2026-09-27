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
    <title>CampusTrace | Lost &amp; Found</title>
    <script>
        (() => {
            try {
                const theme = localStorage.getItem('campustrace-theme');
                if (theme === 'light' || theme === 'dark') document.documentElement.dataset.theme = theme;
            } catch (error) {
                // Use the default light theme when browser storage is unavailable.
            }
        })();
    </script>
    <link rel="stylesheet" href="/Project-CtrlAltDelete/assets/css/styles.css?v=20260927-7">
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">CampusTrace</div>
    <div class="nav-links">
        <a href="/Project-CtrlAltDelete/pages/index.php">Dashboard</a>
        <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
            <a href="/Project-CtrlAltDelete/pages/lost_report.php">Report Lost</a>
            <a href="/Project-CtrlAltDelete/pages/found_report.php">Post Found</a>
        <?php endif; ?>
        <a href="/Project-CtrlAltDelete/pages/items_list.php">Browse Items</a>
        <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
            <a href="/Project-CtrlAltDelete/pages/my_items.php">My Posts</a>
        <?php endif; ?>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="/Project-CtrlAltDelete/pages/admin/dashboard.php" class="admin-link">Admin Panel</a>
        <?php endif; ?>
        <a href="/Project-CtrlAltDelete/pages/auth/logout.php">Logout</a>
        <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Switch to dark mode">Dark mode</button>
    </div>
</nav>
<script>
    (() => {
        const button = document.getElementById('theme-toggle');
        if (!button) return;

        const updateButton = () => {
            const isDark = document.documentElement.dataset.theme === 'dark';
            button.textContent = isDark ? 'Light mode' : 'Dark mode';
            button.setAttribute('aria-label', `Switch to ${isDark ? 'light' : 'dark'} mode`);
        };

        updateButton();
        button.addEventListener('click', () => {
            const theme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.theme = theme;
            try {
                localStorage.setItem('campustrace-theme', theme);
            } catch (error) {
                // Keep the selected theme for this page if browser storage is unavailable.
            }
            updateButton();
        });
    })();
</script>
<main class="container">
