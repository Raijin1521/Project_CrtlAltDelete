<?php
include __DIR__ . '/../../includes/session_check.php';
if ($_SESSION['role'] !== 'admin') { header("Location: /Project_CtrlAltDelete/pages/index.php"); exit; }
require_once __DIR__ . '/../../config/db_connect.php';

$stmt = $conn->query("
    SELECT h.*, i.title, u_admin.full_name as admin_name
    FROM handoff_log h
    JOIN items i ON h.item_id = i.item_id
    JOIN users u_admin ON h.released_by_admin_id = u_admin.user_id
    ORDER BY h.timestamp DESC
");
$logs = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<h2>📋 Chain-of-Custody / Handoff Log</h2>

<?php if (!$logs): ?>
<div class="card">
    <p>No handoffs recorded yet.</p>
</div>
<?php else: ?>
<div class="card">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#f1f5f9;">
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Item</th>
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Given To</th>
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Released By</th>
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Location</th>
                <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #e2e8f0;">Date/Time</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;"><?= e($log['title']) ?></td>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;"><?= e($log['given_to_name']) ?><br><small><?= e($log['given_to_school_id']) ?></small></td>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;"><?= e($log['admin_name']) ?></td>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;"><?= e($log['release_location']) ?></td>
                <td style="padding:0.75rem; border-bottom:1px solid #e2e8f0;"><?= date('M j, Y g:i A', strtotime($log['timestamp'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>