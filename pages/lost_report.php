<?php
include __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school_id = $_SESSION['school_id'];
    if (!validateSchoolID($school_id)) {
        $error = "Invalid School ID format.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO items (
                item_type, title, description, category, brand, color,
                unique_identifier, location_found, reporter_id, reporter_school_id, status
            ) VALUES ('lost', ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmt->execute([
            $_POST['title'], $_POST['description'], $_POST['category'],
            $_POST['brand'], $_POST['color'], $_POST['unique_identifier'],
            $_POST['location_lost'], $_SESSION['user_id'], $school_id
        ]);

        $new_item_id = $conn->lastInsertId();

        // Auto-match with existing found items
        $stmt = $conn->prepare("SELECT * FROM items WHERE item_type='found' AND status='active'");
        $stmt->execute();
        $found_items = $stmt->fetchAll();

        $insert_match = $conn->prepare("
            INSERT INTO matches (lost_item_id, found_item_id, match_score)
            VALUES (?, ?, ?)
        ");

        foreach ($found_items as $found) {
            $score = 0;
            if ($_POST['category'] === $found['category']) $score += 30;
            if (strtolower($_POST['title']) === strtolower($found['title'])) $score += 40;
            if (!empty($_POST['color']) && strtolower($_POST['color']) === strtolower($found['color'])) $score += 20;
            if (!empty($_POST['brand']) && strtolower($_POST['brand']) === strtolower($found['brand'])) $score += 20;
            if (!empty($_POST['unique_identifier']) && $_POST['unique_identifier'] === $found['unique_identifier']) $score += 100;

            if ($score >= 40) {
                $insert_match->execute([$new_item_id, $found['item_id'], $score]);
            }
        }

        header("Location: matches.php?new=1");
        exit;
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="card">
    <h2>🔍 Report a Lost Item</h2>
    <div class="id-note">
        Your School ID (<strong><?= e($_SESSION['school_id']) ?></strong>) is securely recorded for verification — never shared publicly.
    </div>

    <form method="POST">
        <label>Item Name / Title</label>
        <input type="text" name="title" required placeholder="e.g. Silver MacBook Air M2">

        <label>Category</label>
        <select name="category" required>
            <option value="">Select...</option>
            <option>Electronics</option>
            <option>ID & Documents</option>
            <option>Accessories</option>
            <option>Clothing</option>
            <option>Books</option>
            <option>Personal Items</option>
            <option>Other</option>
        </select>

        <label>Brand / Model</label>
        <input type="text" name="brand" placeholder="e.g. Apple, Samsung, Nike">

        <label>Color</label>
        <input type="text" name="color" placeholder="e.g. Space Gray">

        <label>Unique Identifier <small>(Critical for verification — Serial No., engraving, scratch pattern, lockscreen photo, etc.)</small></label>
        <input type="text" name="unique_identifier" required placeholder="e.g. Serial: C02XYZ... / Lockscreen: photo of my dog">

        <label>Last Seen Location</label>
        <input type="text" name="location_lost" required placeholder="Building, Room, Area">

        <label>Detailed Description</label>
        <textarea name="description" rows="4" required placeholder="Include any distinguishing marks, when exactly lost, contact info..."></textarea>

        <button type="submit">Submit Lost Report & Find Matches</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
