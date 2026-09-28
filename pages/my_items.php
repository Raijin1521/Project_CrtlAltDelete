<?php
include __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../config/db_connect.php';

// Show a lost report as claimed when an approved claim completed the matching handoff.
$stmt = $conn->prepare("SELECT i.*,
    CASE WHEN EXISTS (
        SELECT 1 FROM claims c
        JOIN items found_item ON found_item.item_id = c.item_id
        WHERE c.status = 'approved' AND c.claimant_id = i.reporter_id
          AND i.item_type = 'lost' AND found_item.item_type = 'found'
          AND LOWER(TRIM(found_item.title)) = LOWER(TRIM(i.title))
          AND LOWER(TRIM(COALESCE(found_item.category, ''))) = LOWER(TRIM(COALESCE(i.category, '')))
          AND LOWER(TRIM(COALESCE(found_item.color, ''))) = LOWER(TRIM(COALESCE(i.color, '')))
    ) THEN 'claimed' ELSE i.status END AS display_status
    FROM items i WHERE i.reporter_id = ? ORDER BY i.date_reported DESC");
$stmt->execute([$_SESSION['user_id']]);
$my_items = $stmt->fetchAll();

// My claims
$stmt = $conn->prepare("
    SELECT c.*, i.title, i.item_type 
    FROM claims c 
    JOIN items i ON c.item_id = i.item_id 
    WHERE c.claimant_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$my_claims = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<h2>My Dashboard</h2>

<div class="card">
    <h3>📋 My Reports</h3>
    <?php if (!$my_items): ?>
    <p>You haven't reported any items yet.</p>
    <?php else: ?>
    <table style="width:100%; border-collapse:collapse; margin-top:1rem;">
        <thead>
            <tr style="background:#f1f5f9;">
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Item</th>
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Type</th>
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Status</th>
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($my_items as $item): ?>
            <tr>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;"><?= e($item['title']) ?></td>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;"><?= ucfirst($item['item_type']) ?></td>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;">
                    <?php $displayStatus = $item['display_status'] ?? $item['status']; ?>
                    <span class="badge <?= $displayStatus === 'claimed' ? 'badge-claimed' : ($displayStatus === 'disputed' ? 'badge-disputed' : ($displayStatus === 'pending' ? 'badge-pending' : 'badge-active')) ?>">
                        <?= ucfirst(e($displayStatus)) ?>
                    </span>
                </td>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;">
                    <a href="item_detail.php?id=<?= $item['item_id'] ?>">View</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<div class="card">
    <h3>📝 My Claims</h3>
    <?php if (!$my_claims): ?>
    <p>You haven't submitted any claims yet.</p>
    <?php else: ?>
    <table style="width:100%; border-collapse:collapse; margin-top:1rem;">
        <thead>
            <tr style="background:#f1f5f9;">
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Item</th>
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Status</th>
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Date</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($my_claims as $claim): ?>
            <tr>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;"><?= e($claim['title']) ?></td>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;">
                    <span class="badge <?= $claim['status']==='approved'?'badge-claimed':($claim['status']==='rejected'?'badge-disputed':'badge-active') ?>">
                        <?= ucfirst($claim['status']) ?>
                    </span>
                </td>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;"><?= date('M j, Y', strtotime($claim['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
