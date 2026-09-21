<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Register as Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="brand">
            <h1>LOST AND FOUND</h1>
            <p>Helping our school community find what matters.</p>
        </div>

        <div class="login-card">
            <h2>Register as Student</h2>
            <form action="register-process.php" method="POST">
                <input type="hidden" name="role" value="student">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="user_id" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <label>Course / Department</label>
                    <input type="text" name="department" required>
                </div>

                <button type="submit" class="btn primary-btn">Create Account</button>
                <p style="text-align:center; margin-top:15px; font-size:13px;">
                    Already have an account? <a href="index.php">Log in</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>