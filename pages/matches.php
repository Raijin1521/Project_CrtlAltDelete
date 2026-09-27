<?php
include __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../config/db_connect.php';

$msg = $_GET['new'] ?? 0;

// Get matches for user's lost items
$stmt = $conn->prepare("
    SELECT m.*, li.title as lost_title, fi.title as found_title, fi.item_id as found_item_id, fi.color, fi.brand, fi.location_found
    FROM matches m
    JOIN items li ON m.lost_item_id = li.item_id
    JOIN items fi ON m.found_item_id = fi.item_id
    WHERE li.reporter_id = ?
    ORDER BY m.match_score DESC, m.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$matches = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<h2>🔗 Potential Matches</h2>

<?php if ($msg): ?>
<div class="card" style="background:#ecfdf5; border-left:4px solid #10b981;">
    ✅ Your report was submitted! Below are items that may match what you lost.
</div>
<?php endif; ?>

<?php if (!$matches): ?>
<div class="card">
    <p>No matches found yet. New matches will appear here automatically.</p>
    <p style="margin-top:1rem;">
        <a href="items_list.php" style="color:#1e40af; font-weight:600;">Browse all found items →</a>
    </p>
</div>
<?php else: ?>
<p style="margin-bottom:1.5rem;">Matches are scored by how closely they match your details — higher = stronger match.</p>
<div class="grid">
    <?php foreach ($matches as $match): ?>
    <div class="card" style="margin:0; <?= $match['match_score']>=80?'border:2px solid #10b981;':'' ?>">
        <h4><?= e($match['found_title']) ?></h4>
        <p><strong>Match Score:</strong> <span style="font-size:1.2rem; font-weight:bold; color:<?= $match['match_score']>=80?'#059669':'#d97706' ?>;"><?= $match['match_score'] ?>%</span></p>
        <p><strong>Brand:</strong> <?= e($match['brand'] ?? '—') ?></p>
        <p><strong>Color:</strong> <?= e($match['color'] ?? '—') ?></p>
        <p><strong>Found at:</strong> <?= e($match['location_found'] ?? '—') ?></p>
        <a href="item_detail.php?id=<?= $match['found_item_id'] ?>" style="display:inline-block; margin-top:0.8rem; padding:0.5rem 1rem; background:#1e40af; color:white; border-radius:6px; text-decoration:none;">
            View & Claim →
        </a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
