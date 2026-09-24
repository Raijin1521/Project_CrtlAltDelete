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
    <title>Submit a Found Item — ACLC Lost & Found</title>
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
        <h1 class="page-title">Submit a Found Item</h1>

        <div class="form-card">
            <form action="submit-found.php" method="POST">
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="item_name" placeholder="e.g. Black Umbrella, Flash Drive" required>
                </div>

                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" rows="4" placeholder="Color, unique features, condition..." required></textarea>
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
                    <label>Location Found *</label>
                    <input type="text" name="location_found" placeholder="e.g. Gym, Main Gate, Room 205" required>
                </div>

                <div class="form-group">
                    <label>Date Found</label>
                    <input type="date" name="date_found">
                </div>

                <div class="form-group">
                    <label>Where is it kept now?</label>
                    <input type="text" name="item_location" placeholder="e.g. Student Affairs Office, with me at Room 101">
                </div>

                <button type="submit" class="btn green-btn">Submit Report</button>
                <p style="text-align:center; margin-top:18px; font-size:14px;">
                    <a href="home.php" style="color:#64748B;">← Back to Dashboard</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>