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

<div class="auth-layout">
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

        <label>Email</label>
        <input type="email" name="email" required placeholder="you@example.com">

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Register Account</button>
    </form>

    <p style="margin-top:1.5rem;">Already have an account? <a href="login.php">Login here</a></p>
</div>

<details class="card how-it-works">
    <summary>How it works &amp; limitations</summary>
    <div class="how-it-works-content">
        <p>Use your school account to help reunite people with lost belongings.</p>
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
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
