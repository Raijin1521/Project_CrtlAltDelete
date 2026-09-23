<?php
session_start();
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin-dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Log In — Lost & Found</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div style="position: absolute; top: 20px; left: 20px;">
    <a href="index.php" style="
        background: rgba(0,0,0,0.2);
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
    ">← Back</a>
</div>
<div class="container">
    <div class="brand">
        <h1>LOST AND FOUND</h1>
        <p>Administration Portal — Secure Access</p>
    </div>

    <div class="login-card">
        <h2 style="text-align:center; margin-bottom:24px;">🔐 Admin Log In</h2>
        
        <form action="admin-login-process.php" method="POST">
            <div class="form-group">
                <label>Admin Key</label>
                <input type="password" name="admin_key" placeholder="Enter your admin key" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter admin password" required>
            </div>

            <button type="submit" class="btn primary-btn" style="width:100%; margin-top:10px;">
                Log In
            </button>
        </form>
    </div>
</div>
</body>
</html>