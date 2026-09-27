<?php
include __DIR__ . '/../../includes/session_check.php';
if ($_SESSION['role'] !== 'admin') { header("Location: /Project_CtrlAltDelete/pages/index.php"); exit; }
require_once __DIR__ . '/../../config/db_connect.php';

// Resolve dispute
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['dispute_id'])) {
    $stmt = $conn->prepare("UPDATE disputes SET status='resolved' WHERE dispute_id=?");
    $stmt->execute([$_POST['dispute_id']]);
    $success = "Dispute marked resolved.";
}

$stmt = $conn->query("
    SELECT d.*, i.title, c.claimant_school_id, u.full_name as filed_by_name
    FROM disputes d
    JOIN claims c ON d.claim_id = c.claim_id
    JOIN items i ON c.item_id = i.item_id
    JOIN users u ON d.filed_by = u.user_id
    ORDER BY d.status='open' DESC, d.created_at DESC
");
$disputes = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<h2>⚠️ Dispute Management</h2>

<?php if (!empty($success)): ?>
<div class="card" style="background:#ecfdf5; border-left:4px solid #10b981;"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!$disputes): ?>
<div class="card"><p>No disputes at this time ✅</p></div>
<?php else: ?>
<?php foreach ($disputes as $d): ?>
<div class="card" style="<?= $d['status']==='open'?'border-left:4px solid #dc2626;':'opacity:0.7;' ?>">
    <h3><?= e($d['title']) ?> — <span class="badge <?= $d['status']==='open'?'badge-disputed':'badge-claimed' ?>"><?= ucfirst($d['status']) ?></span></h3>
    <p><strong>Filed by:</strong> <?= e($d['filed_by_name']) ?></p>
    <p><strong>Against Claimant ID:</strong> <?= e($d['claimant_school_id']) ?></p>
    <p><strong>Reason:</strong> <?= e($d['reason']) ?></p>
    <?php if ($d['evidence']): ?><p><strong>Evidence:</strong> <?= e($d['evidence']) ?></p><?php endif; ?>
    <p><strong>Filed:</strong> <?= date('M j, Y g:i A', strtotime($d['created_at'])) ?></p>
    
    <?php if ($d['status']==='open'): ?>
    <form method="POST" style="margin-top:1rem;">
        <input type="hidden" name="dispute_id" value="<?= $d['dispute_id'] ?>">
        <button type="submit" style="background:#059669;">Mark Resolved</button>
    </form>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>