<?php
include __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../config/db_connect.php';

$filter_type = $_GET['type'] ?? 'all';
$search = "%".($_GET['q'] ?? '')."%";

if ($filter_type === 'all') {
    $stmt = $conn->prepare("SELECT * FROM items WHERE status IN ('active','pending') AND (title LIKE ? OR description LIKE ? OR category LIKE ?) ORDER BY date_reported DESC");
    $stmt->execute([$search, $search, $search]);
} else {
    $stmt = $conn->prepare("SELECT * FROM items WHERE item_type=? AND status IN ('active','pending') AND (title LIKE ? OR description LIKE ? OR category LIKE ?) ORDER BY date_reported DESC");
    $stmt->execute([$filter_type, $search, $search, $search]);
}
$items = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<h2>Browse Items</h2>

<form method="GET" style="margin-bottom: 1.5rem; display:flex; gap:0.5rem; flex-wrap:wrap;">
    <input type="text" name="q" placeholder="Search by name, category, description..." style="flex:1; min-width:250px; padding:0.75rem; border-radius:6px; border:1px solid #cbd5e1;" value="<?= e($_GET['q'] ?? '') ?>">
    <select name="type" style="padding:0.75rem; border-radius:6px; border:1px solid #cbd5e1;" onchange="this.form.submit()">
        <option value="all" <?= $filter_type==='all'?'selected':'' ?>>All Items</option>
        <option value="lost" <?= $filter_type==='lost'?'selected':'' ?>>Lost Only</option>
        <option value="found" <?= $filter_type==='found'?'selected':'' ?>>Found Only</option>
    </select>
    <button type="submit" style="margin:0;">Search</button>
</form>

<?php if (!$items): ?>
<div class="card">
    <p>No items found matching your search.</p>
</div>
<?php else: ?>
<div class="grid">
    <?php foreach ($items as $item): ?>
    <div class="card" style="margin:0;">
        <span class="badge <?= $item['item_type']==='lost'?'badge-pending':'badge-active' ?>">
            <?= ucfirst($item['item_type']) ?>
        </span>
        <h3 style="margin:0.5rem 0;"><?= e($item['title']) ?></h3>
        <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.5rem;">
            <?= e($item['category']) ?> • <?= e($item['color'] ?? 'N/A') ?>
        </p>
        <p style="font-size:0.9rem;"><?= e(substr($item['description'],0,80)) ?>...</p>
        <p style="font-size:0.85rem; color:#94a3b8;">
            <?= date('M j, Y', strtotime($item['date_reported'])) ?>
        </p>
        <a href="item_detail.php?id=<?= $item['item_id'] ?>" style="display:inline-block; margin-top:0.5rem; color:#1e40af; font-weight:600;">
            <?= $item['item_type'] === 'found' && in_array($item['status'], ['active', 'pending'], true) ? 'View & Claim' : 'View Details' ?> →
        </a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
