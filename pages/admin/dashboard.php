<?php
include __DIR__ . '/../../includes/session_check.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: /Project-CtrlAltDelete/pages/index.php");
    exit;
}
require_once __DIR__ . '/../../config/db_connect.php';

$pending_claims = $conn->query("SELECT c.*, i.title, u.full_name FROM claims c JOIN items i ON c.item_id=i.item_id JOIN users u ON c.claimant_id=u.user_id WHERE c.status IN ('submitted', 'under_review')")->fetchAll();
$active_items = $conn->query("SELECT COUNT(*) FROM items WHERE status IN ('active','pending')")->fetchColumn();
$hold_expiring = $conn->query("SELECT COUNT(*) FROM items WHERE hold_until <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND status NOT IN ('claimed','archived','disposed')")->fetchColumn();

include __DIR__ . '/../../includes/header.php';
?>

<h2>🔐 Admin Dashboard</h2>

<div class="grid" style="margin:2rem 0;">
    <div class="card">
        <h3>Pending Claims</h3>
        <p style="font-size:2rem; font-weight:bold; color:#d97706;"><?= count($pending_claims) ?></p>
        <a href="#pending-claims" class="btn">Review Now →</a>
    </div>
    <div class="card">
        <h3>Active Items</h3>
        <p style="font-size:2rem; font-weight:bold; color:#1e40af;"><?= $active_items ?></p>
        <a href="/Project-CtrlAltDelete/pages/items_list.php" class="btn">View All →</a>
    </div>
    <div class="card">
        <h3>Expiring Hold</h3>
        <p style="font-size:2rem; font-weight:bold; color:#dc2626;"><?= $hold_expiring ?></p>
        <a href="/Project-CtrlAltDelete/pages/admin/archive.php" class="btn">Process →</a>
    </div>
</div>

<div class="card" id="pending-claims">
    <h3>Claims Awaiting Verification</h3>
    <?php if (!$pending_claims): ?>
    <p>No pending claims — all up to date!</p>
    <?php else: ?>
    <table style="width:100%; border-collapse:collapse;">
        <tr style="background:#f1f5f9;">
            <th style="padding:0.75rem; text-align:left;">Item</th>
            <th style="padding:0.75rem; text-align:left;">Claimant</th>
            <th style="padding:0.75rem; text-align:left;">Status</th>
            <th style="padding:0.75rem; text-align:left;">Action</th>
        </tr>
        <?php foreach ($pending_claims as $claim): ?>
        <tr>
            <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;"><?= e($claim['title']) ?></td>
            <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;"><?= e($claim['full_name']) ?></td>
            <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;"><?= e($claim['status']) ?></td>
            <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;">
                <a href="/Project-CtrlAltDelete/pages/admin/verify_claim.php?claim_id=<?= $claim['claim_id'] ?>" class="btn">Review</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
