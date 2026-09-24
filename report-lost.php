<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
include 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Report a Lost Item — ACLC Lost & Found</title>
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

    <div class="container">
        <h1 class="page-title">Report a Lost Item</h1>

        <div class="form-card">
            <form action="submit-lost.php" method="POST">
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="item_name" placeholder="e.g. Blue ID Lace, Black Wallet" required>
                </div>

                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" rows="4" placeholder="Color, brand, marks, contents..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="ID & School Items">ID & School Items</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Wallet & Cards">Wallet & Cards</option>
                        <option value="Bags & Containers">Bags & Containers</option>
                        <option value="Clothing">Clothing</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Location Last Seen *</label>
                    <input type="text" name="location_lost" placeholder="e.g. Library, Canteen, Room 302" required>
                </div>

                <div class="form-group">
                    <label>Date Lost</label>
                    <input type="date" name="date_lost">
                </div>

                <div class="form-group">
                    <label>Your Contact / Message</label>
                    <textarea name="contact_info" rows="2" placeholder="Where can they reach you?"></textarea>
                </div>

                <button type="submit" class="btn red-btn">Submit Report</button>
                <p style="text-align:center; margin-top:18px; font-size:14px;">
                    <a href="home.php" style="color:#64748B;">← Back to Dashboard</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>