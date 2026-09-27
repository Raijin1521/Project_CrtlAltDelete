<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /Project_CtrlAltDelete/pages/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$lost_items = $conn->prepare("SELECT * FROM items WHERE item_type='lost' AND reporter_id=? ORDER BY date_reported DESC");
$lost_items->execute([$user_id]);
$lost_items = $lost_items->fetchAll();

$found_items = $conn->prepare("SELECT * FROM items WHERE item_type='found' AND reporter_id=? ORDER BY date_reported DESC");
$found_items->execute([$user_id]);
$found_items = $found_items->fetchAll();

$claims = $conn->prepare("SELECT c.*, i.title FROM claims c JOIN items i ON c.item_id=i.item_id WHERE c.claimant_id=? ORDER BY c.created_at DESC");
$claims->execute([$user_id]);
$claims = $claims->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h2>👋 Welcome, <?= e($_SESSION['full_name'] ?? 'User') ?></h2>

<div class="grid" style="margin:2rem 0;">
    <div class="card">
        <h3>📋 Quick Actions</h3>
        <a href="/Project_CtrlAltDelete/pages/lost_report.php" class="btn" style="display:block; margin:0.5rem 0;">Report Something Lost</a>
        <a href="/Project_CtrlAltDelete/pages/found_report.php" class="btn" style="display:block; margin:0.5rem 0;">Post Something Found</a>
        <a href="/Project_CtrlAltDelete/pages/items_list.php" class="btn" style="display:block; margin:0.5rem 0;">Browse All Items</a>
    </div>

    <div class="card">
        <h3>📊 Your Summary</h3>
        <p><strong>Lost Items:</strong> <?= count($lost_items) ?></p>
        <p><strong>Found Items Posted:</strong> <?= count($found_items) ?></p>
        <p><strong>Claims Submitted:</strong> <?= count($claims) ?></p>
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
        <li><a href="/Project_CtrlAltDelete/pages/item_detail.php?id=<?= $item['item_id'] ?>"><?= e($item['title']) ?></a> — <em><?= $item['status'] ?></em></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>