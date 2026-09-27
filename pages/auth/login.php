<?php
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../../includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school_id = trim($_POST['school_id']);
    $password = $_POST['password'];

    if (!validateSchoolID($school_id)) {
        $error = "School ID must be exactly 11 digits.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE school_id = ?");
        $stmt->execute([$school_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['school_id'] = $user['school_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            header("Location: /Project_CtrlAltDelete/pages/index.php");
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    }
}
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="card" style="max-width: 450px; margin: 3rem auto;">
    <h2>Login to CampusTrace</h2>
    <?php if ($error): ?><p style="color:red; margin:1rem 0;"><?= e($error) ?></p><?php endif; ?>
    
    <form method="POST">
        <label>School ID (11 digits)</label>
        <input type="text" name="school_id" pattern="\d{11}" maxlength="11" required placeholder="e.g. 12345678901">
        
        <label>Password</label>
        <input type="password" name="password" required>
        
        <button type="submit">Sign In</button>
    </form>
    <p style="margin-top:1.5rem; font-size:0.9rem;">
        Don't have an account? <a href="register.php">Register here</a>
    </p>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
