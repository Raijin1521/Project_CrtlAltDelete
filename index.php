<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>ACLC Lost & Found</title>
    <link rel="stylesheet" href="style.css">
</head>
<div style="position: absolute; top: 20px; left: 20px; z-index: 100;">
    <a href="admin-login.php" style="
        background: rgba(30, 58, 138, 0.9);
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s;
    " onmouseover="this.style.background='rgba(20, 40, 100, 0.95)'"
       onmouseout="this.style.background='rgba(30, 58, 138, 0.9)'">
        ⚙️ Admin Log In
    </a>
</div>   
    <div class="container">
        <div class="brand">
            <h1>LOST AND FOUND</h1>
            <p>Helping our school community find what matters.</p>
        </div>

        <div class="login-card">
            <h2>Welcome Back!</h2>
            <form action="login-process.php" method="POST">
                <div class="form-group">
                    <label>Student ID / Instructor ID</label>
                    <input type="text" name="user_id" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" class="btn primary-btn">Log In</button>

                <div class="divider">or</div>

                <div class="btn-group">
                    <a href="register-student.php" class="btn secondary-btn">Register as Student</a>
                    <a href="register-instructor.php" class="btn secondary-btn">Register as Instructor</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>