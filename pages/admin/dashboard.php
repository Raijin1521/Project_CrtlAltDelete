<?php
include __DIR__ . '/../../includes/session_check.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: /Project-CtrlAltDelete/pages/index.php");
    exit;
}
require_once __DIR__ . '/../../config/db_connect.php';

$pending_claims = $conn->query("SELECT c.*, i.title, u.full_name FROM claims c JOIN items i ON c.item_id=i.item_id JOIN users u ON c.claimant_id=u.user_id WHERE c.status IN ('submitted', 'under_review')")->fetchAll();
$active_items = $conn->query("SELECT COUNT(*) FROM items i WHERE i.status IN ('active','pending')
    AND NOT EXISTS (SELECT 1 FROM claims c WHERE c.item_id = i.item_id AND c.status = 'approved')
    AND NOT EXISTS (SELECT 1 FROM claims c JOIN items f ON f.item_id = c.item_id WHERE c.status = 'approved' AND c.claimant_id = i.reporter_id AND i.item_type = 'lost' AND f.item_type = 'found' AND LOWER(TRIM(f.title)) = LOWER(TRIM(i.title)) AND f.category = i.category AND COALESCE(f.color, '') = COALESCE(i.color, ''))")->fetchColumn();
$hold_expiring = $conn->query("SELECT COUNT(*) FROM items i WHERE i.hold_until <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND i.status NOT IN ('claimed','archived','disposed')
    AND NOT EXISTS (SELECT 1 FROM claims c WHERE c.item_id = i.item_id AND c.status = 'approved')
    AND NOT EXISTS (SELECT 1 FROM claims c JOIN items f ON f.item_id = c.item_id WHERE c.status = 'approved' AND c.claimant_id = i.reporter_id AND i.item_type = 'lost' AND f.item_type = 'found' AND LOWER(TRIM(f.title)) = LOWER(TRIM(i.title)) AND f.category = i.category AND COALESCE(f.color, '') = COALESCE(i.color, ''))")->fetchColumn();
$open_disputes = $conn->query("SELECT COUNT(*) FROM disputes WHERE status='open'")->fetchColumn();
$handoff_count = $conn->query("SELECT COUNT(*) FROM handoff_log")->fetchColumn();
$recent_items = $conn->query("SELECT i.item_id, i.title, i.item_type,
    CASE WHEN EXISTS (SELECT 1 FROM claims c WHERE c.item_id = i.item_id AND c.status = 'approved')
      OR EXISTS (SELECT 1 FROM claims c JOIN items f ON f.item_id = c.item_id WHERE c.status = 'approved' AND c.claimant_id = i.reporter_id AND i.item_type = 'lost' AND f.item_type = 'found' AND LOWER(TRIM(f.title)) = LOWER(TRIM(i.title)) AND COALESCE(f.category, '') = COALESCE(i.category, '') AND COALESCE(f.color, '') = COALESCE(i.color, ''))
      THEN 'claimed' ELSE i.status END AS status,
    i.date_reported FROM items i ORDER BY i.date_reported DESC LIMIT 6")->fetchAll();

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

<div class="grid" style="margin:2rem 0;">
    <div class="card">
        <h3>Open Disputes</h3>
        <p style="font-size:2rem; font-weight:bold; color:#dc2626;"><?= $open_disputes ?></p>
        <a href="/Project-CtrlAltDelete/pages/admin/disputes.php" class="btn">Resolve Disputes</a>
    </div>
    <div class="card">
        <h3>Handoffs Recorded</h3>
        <p style="font-size:2rem; font-weight:bold; color:#059669;"><?= $handoff_count ?></p>
        <a href="/Project-CtrlAltDelete/pages/admin/handoff_log.php" class="btn">View Handoff Log</a>
    </div>
    <div class="card">
        <h3>Archive &amp; Disposition</h3>
        <p>Review items nearing the end of their hold period.</p>
        <a href="/Project-CtrlAltDelete/pages/admin/archive.php" class="btn">Manage Archived Items</a>
    </div>
</div>

<div class="card">
    <h3>Recently Reported Items</h3>
    <?php if (!$recent_items): ?>
        <p>No items have been reported yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Item</th><th>Type</th><th>Status</th><th>Reported</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recent_items as $item): ?>
                <tr>
                    <td><?= e($item['title']) ?></td>
                    <td><?= ucfirst(e($item['item_type'])) ?></td>
                    <td><?= ucfirst(e($item['status'])) ?></td>
                    <td><?= date('M j, Y', strtotime($item['date_reported'])) ?></td>
                    <td><a href="/Project-CtrlAltDelete/pages/item_detail.php?id=<?= (int)$item['item_id'] ?>">Review item</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
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
