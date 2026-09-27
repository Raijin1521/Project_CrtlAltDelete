<?php
include __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school_id = $_SESSION['school_id'];
    if (!validateSchoolID($school_id)) {
        $error = "Invalid School ID format.";
    } else {
        $hold_days = getHoldPeriod(
            !empty($_POST['is_high_value']),
            !empty($_POST['is_sensitive_doc'])
        );
        $hold_until = date('Y-m-d', strtotime("+$hold_days days"));

        $stmt = $conn->prepare("
            INSERT INTO items (
                item_type, title, description, category, brand, color,
                unique_identifier, location_found, reporter_id, reporter_school_id,
                is_high_value, is_sensitive_doc, hold_until
            ) VALUES ('found', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['title'], $_POST['description'], $_POST['category'],
            $_POST['brand'], $_POST['color'], $_POST['unique_identifier'],
            $_POST['location_found'], $_SESSION['user_id'], $school_id,
            !empty($_POST['is_high_value']), !empty($_POST['is_sensitive_doc']),
            $hold_until
        ]);

        header("Location: items_list.php?posted=1");
        exit;
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="card">
    <h2>📌 Post a Found Item</h2>
    <div class="id-note">
        Your School ID (<strong><?= e($_SESSION['school_id']) ?></strong>) is recorded as finder for chain-of-custody.
    </div>

    <form method="POST">
        <label>Title / Item Name</label>
        <input type="text" name="title" required placeholder="e.g. Black Samsung Galaxy S23">

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
        <input type="text" name="brand" placeholder="e.g. Apple, Nike, Dell">

        <label>Color</label>
        <input type="text" name="color" placeholder="e.g. Matte Black">

        <label>Unique Identifier <small>(Serial No., engraving, distinct mark — <strong>keep private until verified</strong>)</small></label>
        <input type="text" name="unique_identifier" placeholder="e.g. Serial: C7A2X...">

        <label>Description & Location Found</label>
        <textarea name="description" rows="4" required placeholder="Describe where and when found, any visible marks..."></textarea>
        
        <label>Location Found</label>
        <input type="text" name="location_found" required placeholder="Building, Room, Area">

        <label>
            <input type="checkbox" name="is_high_value"> This is a high-value item (laptop, phone, watch, wallet with cash)
        </label>
        <label>
            <input type="checkbox" name="is_sensitive_doc"> This is a sensitive document (ID, license, passport)
        </label>

        <button type="submit">Submit Found Item</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
