<?php
include __DIR__ . '/../../includes/session_check.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: /Project-CtrlAltDelete/pages/index.php");
    exit;
}
require_once __DIR__ . '/../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['item_id'])) {
    $action = $_POST['disposition_action'];
    $item_id = $_POST['item_id'];
    $notes = trim($_POST['disp_notes'] ?? '');

    if ($action === 'archive') {
        $stmt = $conn->prepare("UPDATE items SET status='archived' WHERE item_id=?");
        $stmt->execute([$item_id]);
    } elseif ($action === 'donate') {
        $stmt = $conn->prepare("UPDATE items SET status='disposed' WHERE item_id=?");
        $stmt->execute([$item_id]);
    } elseif ($action === 'destroy_shred') {
        $stmt = $conn->prepare("UPDATE items SET status='disposed' WHERE item_id=?");
        $stmt->execute([$item_id]);
    }
    $success = "Item disposition updated.";
}

$conn->query("UPDATE items SET status='archived' WHERE hold_until < CURDATE() AND status NOT IN ('claimed','archived','disposed')");

$expiring = $conn->query("SELECT * FROM items WHERE hold_until <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status NOT IN ('claimed','archived','disposed') ORDER BY hold_until ASC")->fetchAll();
$archived = $conn->query("SELECT * FROM items WHERE status IN ('archived','disposed') ORDER BY date_reported DESC LIMIT 20")->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<h2>📦 Archive & Disposition Management</h2>

<?php if (!empty($success)): ?>
<div class="card" style="background:#ecfdf5; border-left:4px solid #10b981;"><?= e($success) ?></div>
<?php endif; ?>

<div class="card">
    <h3>⏳ Items Expiring Soon / Past Hold Period</h3>
    <p style="color:#64748b; font-size:0.9rem; margin-bottom:1rem;">
        High-value: 30 days • Sensitive Docs: 60 days • Standard: 7 days
    </p>
    <?php if (!$expiring): ?>
    <p>No items expiring soon.</p>
    <?php else: ?>
    <?php foreach ($expiring as $item): ?>
    <div style="border:1px solid #e2e8f0; border-radius:6px; padding:1rem; margin-bottom:1rem;">
        <h4 style="margin:0 0 0.5rem;"><?= e($item['title']) ?></h4>
        <p>Category: <?= e($item['category']) ?> • 
           Hold Until: <strong><?= date('M j, Y', strtotime($item['hold_until'])) ?></strong>
           <?php if (strtotime($item['hold_until']) < time()): ?>
           <span style="color:#dc2626; font-weight:bold;"> — EXPIRED</span>
           <?php endif; ?>
        </p>
        <form method="POST" style="margin-top:0.5rem; display:flex; gap:0.5rem; flex-wrap:wrap;">
            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
            <button type="submit" name="disposition_action" value="archive" style="background:#6366f1; color:white; border:none; padding:0.5rem 1rem; border-radius:4px; cursor:pointer;">Archive</button>
            <?php if (!empty($item['is_sensitive_doc'])): ?>
            <button type="submit" name="disposition_action" value="destroy_shred" style="background:#dc2626; color:white; border:none; padding:0.5rem 1rem; border-radius:4px; cursor:pointer;">Secure Shred</button>
            <?php else: ?>
            <button type="submit" name="disposition_action" value="donate" style="background:#059669; color:white; border:none; padding:0.5rem 1rem; border-radius:4px; cursor:pointer;">Donate</button>
            <?php endif; ?>
            <input type="text" name="disp_notes" placeholder="Notes..." style="max-width:200px; padding:0.5rem; border:1px solid #cbd5e1; border-radius:4px;">
        </form>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="card">
    <h3>📁 Archived / Disposed Records</h3>
    <?php if (!$archived): ?>
    <p>No archived items yet.</p>
    <?php else: ?>
    <table style="width:100%; border-collapse:collapse; margin-top:1rem;">
        <thead>
            <tr style="background:#f1f5f9;">
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Item</th>
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Category</th>
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Status</th>
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Reported</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($archived as $item): ?>
            <tr>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;"><?= e($item['title']) ?></td>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;"><?= e($item['category']) ?></td>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;">
                    <span class="badge <?= $item['status']==='archived'?'badge-pending':'badge-disputed' ?>">
                        <?= ucfirst($item['status']) ?>
                    </span>
                </td>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;">
                    <?= date('M j, Y', strtotime($item['date_reported'])) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>