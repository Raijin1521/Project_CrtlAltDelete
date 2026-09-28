<?php
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../../includes/functions.php';

$error = '';
$adminLogin = isset($_GET['admin']) || (isset($_POST['admin_login']) && $_POST['admin_login'] === '1');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginUser = trim($_POST[$adminLogin ? 'admin_user' : 'school_id'] ?? '');
    $password = $_POST['password'];

    if (!$adminLogin && !validateSchoolID($loginUser)) {
        $error = "School ID must be exactly 11 digits.";
    } else {
        if ($adminLogin) {
            // Admins can use their existing account ID or email as the username.
            $stmt = $conn->prepare("SELECT * FROM users WHERE (school_id = ? OR email = ?) AND role = 'admin'");
            $stmt->execute([$loginUser, $loginUser]);
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE school_id = ?");
            $stmt->execute([$loginUser]);
        }
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash']) && !$adminLogin && $user['role'] !== 'student') {
            $error = $user['role'] === 'admin'
                ? 'Administrator accounts must sign in through Admin Login.'
                : 'This login is for student accounts only.';
        } elseif ($user && password_verify($password, $user['password_hash'])) {
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['school_id'] = $user['school_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            header("Location: /Project-CtrlAltDelete/pages/index.php");
            exit;
        } else {
            $error = $adminLogin ? "Invalid admin username or password." : "Invalid credentials.";
        }
    }
}
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="<?= $adminLogin ? '' : 'auth-layout' ?>">
    <div class="card" style="max-width: 450px; margin: 3rem auto;">
        <?php if ($adminLogin): ?>
            <a class="auth-back-link" href="login.php" aria-label="Back to student login">←</a>
        <?php endif; ?>
        <h2><?= $adminLogin ? 'Administrator Login' : 'Login to CampusTrace' ?></h2>
        <?php if ($error): ?><p style="color:red; margin:1rem 0;"><?= e($error) ?></p><?php endif; ?>

        <form method="POST">
            <?php if ($adminLogin): ?>
                <input type="hidden" name="admin_login" value="1">
                <label>Admin User</label>
                <input type="text" name="admin_user" required autocomplete="username" placeholder="Admin username or email">
            <?php else: ?>
                <label>School ID (11 digits)</label>
                <input type="text" name="school_id" pattern="\d{11}" maxlength="11" required placeholder="e.g. 12345678901">
            <?php endif; ?>

            <label>Password</label>
            <input type="password" name="password" required autocomplete="current-password">

            <button type="submit"><?= $adminLogin ? 'Sign In as Admin' : 'Sign In' ?></button>
        </form>
        <?php if (!$adminLogin): ?>
            <a class="admin-login-button" href="login.php?admin=1">Admin Login</a>
            <p style="margin-top:1.5rem; font-size:0.9rem;">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        <?php endif; ?>
    </div>

    <?php if (!$adminLogin): ?>
        <details class="card how-it-works">
            <summary>How it works &amp; limitations</summary>
            <div class="how-it-works-content">
                <p>A simple path from reporting an item to getting it back.</p>
                <ol>
                    <li><strong>Report or browse.</strong> Post a lost or found item, or check the listings for a possible match.</li>
                    <li><strong>Submit a claim.</strong> If you recognize a found item, provide the requested details to support your claim.</li>
                    <li><strong>Wait for staff review.</strong> An administrator checks the claim and may contact you for more information.</li>
                    <li><strong>Arrange a handoff.</strong> If approved, follow the staff instructions and confirm your identity when collecting the item.</li>
                </ol>
                <h3>Limitations</h3>
                <ul>
                    <li>Only items reported in CampusTrace can appear in its listings or matches.</li>
                    <li>A possible match does not confirm ownership; claims require staff review.</li>
                    <li>Approval and return depend on the information provided and staff verification, so a return is not guaranteed.</li>
                    <li>Review and handoff may take time; follow staff instructions for collection.</li>
                </ul>
            </div>
        </details>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
