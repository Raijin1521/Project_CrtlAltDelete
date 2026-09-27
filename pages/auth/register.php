<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../../includes/functions.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school_id = trim($_POST['school_id']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!validateSchoolID($school_id)) {
        $error = "School ID must be exactly 11 digits — no spaces, letters, or symbols.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                INSERT INTO users (school_id, full_name, email, password_hash, role)
                VALUES (?, ?, ?, ?, 'student')
            ");
            $stmt->execute([$school_id, $full_name, $email, $hash]);
            $success = "Account created! You can now <a href='login.php'>login</a>.";
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $error = "This School ID or email is already registered.";
            } else {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="card" style="max-width: 480px; margin: 2rem auto;">
    <h2>Create Your CampusTrace Account</h2>

    <?php if ($error): ?><p style="color:red; margin:1rem 0;"><?= e($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green; margin:1rem 0;"><?= $success ?></p><?php endif; ?>

    <form method="POST">
        <div class="id-note">
            ⚠️ Your School ID must be <strong>exactly 11 digits</strong> — this will be your unique identifier for all claims & handoffs.
        </div>

        <label>School ID (11 digits)</label>
        <input type="text" name="school_id" maxlength="11" pattern="\d{11}" required placeholder="e.g. 12345678901">

        <label>Full Name</label>
        <input type="text" name="full_name" required placeholder="Your full name as in school records">

        <label>School Email</label>
        <input type="email" name="email" required placeholder="yourname@school.edu.ph">

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Register Account</button>
    </form>

    <p style="margin-top:1.5rem;">Already have an account? <a href="login.php">Login here</a></p>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>