<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard — ACLC Lost & Found</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <h2>ACLC LOST AND FOUND</h2>
        <div class="nav-links">
            <span>Welcome, <?php echo $_SESSION['full_name']; ?></span>
            <a href="logout.php" class="logout-btn">Log Out</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <h1>What would you like to do?</h1>
        <p>Choose an option below to get started</p>

        <div class="action-cards">
            <a href="report-lost.php" class="action-card lost-card">
                <h3>I Lost Something</h3>
                <p>Report a missing item</p>
            </a>

            <a href="report-found.php" class="action-card found-card">
                <h3>I Found Something</h3>
                <p>Help return an item to its owner</p>
            </a>

            <a href="listings.php" class="action-card view-card">
                <h3>View All Items</h3>
                <p>Browse all lost & found listings</p>
            </a>
        </div>
    </div>
</body>
</html>