<?php
include __DIR__ . '/../../includes/session_check.php';
if ($_SESSION['role'] !== 'admin') { header("Location: /Project-CtrlAltDelete/pages/index.php"); exit; }
require_once __DIR__ . '/../../config/db_connect.php';

$claim_id = intval($_GET['claim_id'] ?? $_POST['claim_id'] ?? 0);

// Process action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $claim_id) {
    $action = $_POST['action'];
    $notes = trim($_POST['admin_notes'] ?? '');

    // Load claim + item
    $stmt = $conn->prepare("SELECT c.*, i.* FROM claims c JOIN items i ON c.item_id = i.item_id WHERE c.claim_id = ?");
    $stmt->execute([$claim_id]);
    $data = $stmt->fetch();

    if ($data) {
        if ($action === 'approve') {
            // Update claim
            $stmt = $conn->prepare("UPDATE claims SET status='approved', reviewed_by=?, reviewed_at=NOW(), admin_notes=? WHERE claim_id=?");
            $stmt->execute([$_SESSION['user_id'], $notes, $claim_id]);

            // Mark item as claimed
            $stmt = $conn->prepare("UPDATE items SET status='claimed' WHERE item_id=?");
            $stmt->execute([$data['item_id']]);

            // Log handoff
            $stmt = $conn->prepare("
                INSERT INTO handoff_log (item_id, given_to_name, given_to_school_id, released_by_admin_id, release_location, notes)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['item_id'],
                $data['claimant_id'], // We'll resolve name below
                $data['claimant_school_id'],
                $_SESSION['user_id'],
                'Admin Office',
                "Approved — " . $notes
            ]);

            // Fix: store actual name
            $stmt = $conn->prepare("UPDATE handoff_log SET given_to_name=(SELECT full_name FROM users WHERE user_id=?) WHERE log_id=LAST_INSERT_ID()");
            $stmt->execute([$data['claimant_id']]);

            $success = "✅ Claim APPROVED & handoff recorded.";
        } elseif ($action === 'reject') {
            $stmt = $conn->prepare("UPDATE claims SET status='rejected', reviewed_by=?, reviewed_at=NOW(), admin_notes=? WHERE claim_id=?");
            $stmt->execute([$_SESSION['user_id'], $notes, $claim_id]);
            $success = "Claim rejected.";
        } elseif ($action === 'dispute') {
            $stmt = $conn->prepare("UPDATE claims SET status='disputed', reviewed_by=?, reviewed_at=NOW(), admin_notes=? WHERE claim_id=?");
            $stmt->execute([$_SESSION['user_id'], $notes, $claim_id]);
            header("Location: disputes.php");
            exit;
        }
    }
}

// Load claim for review
$claim = null;
if ($claim_id) {
    $stmt = $conn->prepare("SELECT c.*, i.*, u_claim.full_name as claimant_name, u_rep.full_name as reporter_name 
        FROM claims c 
        JOIN items i ON c.item_id = i.item_id 
        JOIN users u_claim ON c.claimant_id = u_claim.user_id 
        JOIN users u_rep ON i.reporter_id = u_rep.user_id 
        WHERE c.claim_id = ?");
    $stmt->execute([$claim_id]);
    $claim = $stmt->fetch();
}

if (!$claim && !$success) {
    header("Location: dashboard.php");
    exit;
}
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<h2>🔐 Verify Claim</h2>

<?php if (!empty($success)): ?>
<div class="card" style="background:#ecfdf5; border-left:4px solid #10b981;">
    <?= e($success) ?>
    <p style="margin-top:1rem;"><a href="dashboard.php" style="color:#1e40af;">← Back to Dashboard</a></p>
</div>
<?php elseif ($claim): ?>

<div class="card">
    <h3>Item: <?= e($claim['title']) ?></h3>
    <p><strong>Reported by:</strong> <?= e($claim['reporter_name']) ?> (<?= e($claim['reporter_school_id']) ?>)</p>
    <p><strong>Item Unique ID:</strong> <code style="background:#f1f5f9; padding:0.25rem 0.5rem; border-radius:4px;"><?= e($claim['unique_identifier']) ?></code></p>

    <hr style="margin:1.5rem 0;">

    <h4>Claimant Information</h4>
    <p><strong>Name:</strong> <?= e($claim['claimant_name']) ?></p>
    <p><strong>School ID:</strong> <?= e($claim['claimant_school_id']) ?></p>
    <p><strong>Proof Description:</strong> <?= e($claim['proof_description']) ?></p>
    <p><strong>Provided Unique ID:</strong> <code style="background:#fef3c7; padding:0.25rem 0.5rem; border-radius:4px;"><?= e($claim['unique_identifier_proof']) ?></code></p>

    <?php 
    $match = (trim($claim['unique_identifier']) === trim($claim['unique_identifier_proof']));
    ?>
    <div style="margin:1rem 0; padding:1rem; border-radius:6px; background:<?= $match?'#ecfdf5':'#fffbeb' ?>;">
        <strong>Verification Check:</strong>
        <?= $match ? '✅ Unique Identifiers MATCH' : '⚠️ Identifiers DO NOT match — review manually' ?>
    </div>

    <form method="POST">
        <input type="hidden" name="claim_id" value="<?= $claim_id ?>">

        <label>Admin Notes / Handoff Details</label>
        <textarea name="admin_notes" rows="3" placeholder="Release location, condition, special instructions..."></textarea>

        <div style="display:flex; gap:1rem; margin-top:1.5rem;">
            <button type="submit" name="action" value="approve" style="background:#059669;">✅ Approve & Release</button>
            <button type="submit" name="action" value="reject" style="background:#dc2626;">❌ Reject</button>
            <button type="submit" name="action" value="dispute" style="background:#d97706;">⚠️ Flag as Dispute</button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>