<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /Project-CtrlAltDelete/pages/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';

$lost_items = $conn->prepare("SELECT lost_item.*, CASE WHEN EXISTS (
    SELECT 1 FROM claims c
    JOIN items found_item ON found_item.item_id = c.item_id
    WHERE c.status = 'approved' AND c.claimant_id = lost_item.reporter_id
      AND found_item.item_type = 'found'
      AND LOWER(TRIM(found_item.title)) = LOWER(TRIM(lost_item.title))
      AND LOWER(TRIM(COALESCE(found_item.category, ''))) = LOWER(TRIM(COALESCE(lost_item.category, '')))
      AND LOWER(TRIM(COALESCE(found_item.color, ''))) = LOWER(TRIM(COALESCE(lost_item.color, '')))
) THEN 'claimed' ELSE lost_item.status END AS display_status
FROM items lost_item
WHERE lost_item.item_type='lost' AND lost_item.reporter_id=?
ORDER BY lost_item.date_reported DESC");
$lost_items->execute([$user_id]);
$lost_items = $lost_items->fetchAll();

$found_items = $conn->prepare("SELECT * FROM items WHERE item_type='found' AND reporter_id=? ORDER BY date_reported DESC");
$found_items->execute([$user_id]);
$found_items = $found_items->fetchAll();

$claims = $conn->prepare("SELECT c.*, i.title FROM claims c JOIN items i ON c.item_id=i.item_id WHERE c.claimant_id=? ORDER BY c.created_at DESC");
$claims->execute([$user_id]);
$claims = $claims->fetchAll();

if ($isAdmin) {
    $lost_count = (int)$conn->query("SELECT COUNT(*) FROM items WHERE item_type='lost'")->fetchColumn();
    $found_count = (int)$conn->query("SELECT COUNT(*) FROM items WHERE item_type='found'")->fetchColumn();
    $claim_count = (int)$conn->query("SELECT COUNT(*) FROM claims")->fetchColumn();
} else {
    $lost_count = count($lost_items);
    $found_count = count($found_items);
    $claim_count = count($claims);
}

include __DIR__ . '/../includes/header.php';
?>

<h2>👋 Welcome, <?= e($_SESSION['full_name'] ?? 'User') ?></h2>

<div class="grid" style="margin:2rem 0;">
    <?php if (($_SESSION['role'] ?? '') !== 'admin'): ?>
    <div class="card">
        <h3>📋 Quick Actions</h3>
        <a href="/Project-CtrlAltDelete/pages/lost_report.php" class="btn" style="display:block; margin:0.5rem 0;">Report Something Lost</a>
        <a href="/Project-CtrlAltDelete/pages/found_report.php" class="btn" style="display:block; margin:0.5rem 0;">Post Something Found</a>
        <a href="/Project-CtrlAltDelete/pages/items_list.php" class="btn" style="display:block; margin:0.5rem 0;">Browse All Items</a>
    </div>
    <?php endif; ?>

    <div class="card">
        <h3><?= $isAdmin ? 'System Summary' : 'Your Summary' ?></h3>
        <p><strong>Lost Items:</strong> <?= $lost_count ?></p>
        <p><strong>Found Items Posted:</strong> <?= $found_count ?></p>
        <p><strong>Claims Submitted:</strong> <?= $claim_count ?></p>
    </div>
</div>

<div class="card">
    <h3>🔄 Recent Activity</h3>
    <?php if (empty($lost_items) && empty($found_items) && empty($claims)): ?>
    <p>No activity yet. Start by reporting an item above!</p>
    <?php else: ?>
    <?php if (!empty($lost_items)): ?>
    <h4>Your Lost Items</h4>
    <ul>
        <?php foreach ($lost_items as $item): ?>
        <li><a href="/Project-CtrlAltDelete/pages/item_detail.php?id=<?= $item['item_id'] ?>"><?= e($item['title']) ?></a> — <em><?= e($item['display_status'] ?? $item['status']) ?></em></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
