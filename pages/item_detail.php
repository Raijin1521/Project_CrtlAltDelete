<?php
include __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

$item_id = intval($_GET['id'] ?? 0);

// Load item
$stmt = $conn->prepare("SELECT * FROM items WHERE item_id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    header("Location: items_list.php");
    exit;
}

$approved_claim_stmt = $conn->prepare("SELECT 1 FROM claims WHERE item_id = ? AND status = 'approved' LIMIT 1");
$approved_claim_stmt->execute([$item_id]);
$hasApprovedClaim = (bool)$approved_claim_stmt->fetchColumn();
if (!$hasApprovedClaim && $item['item_type'] === 'lost') {
    $matched_lost_claim = $conn->prepare("SELECT 1
        FROM claims c
        JOIN items found_item ON found_item.item_id = c.item_id
        WHERE c.status = 'approved' AND c.claimant_id = ?
          AND found_item.item_type = 'found'
          AND LOWER(TRIM(found_item.title)) = LOWER(TRIM(?))
          AND LOWER(TRIM(COALESCE(found_item.category, ''))) = LOWER(TRIM(COALESCE(?, '')))
          AND LOWER(TRIM(COALESCE(found_item.color, ''))) = LOWER(TRIM(COALESCE(?, '')))
        LIMIT 1");
    $matched_lost_claim->execute([
        $item['reporter_id'],
        $item['title'],
        $item['category'],
        $item['color'] ?? ''
    ]);
    $hasApprovedClaim = (bool)$matched_lost_claim->fetchColumn();
}
if ($hasApprovedClaim) {
    // Keep older/inconsistent records from appearing available after approval.
    $item['status'] = 'claimed';
}

// Handle claim submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $item['item_type'] === 'found') {
    if (($_SESSION['role'] ?? '') === 'admin') {
        http_response_code(403);
        exit('Administrators can view items but cannot submit claims.');
    }
    $proof_desc = trim($_POST['proof_description']);
    $proof_uid = trim($_POST['unique_identifier_proof']);

    if ($hasApprovedClaim || !in_array($item['status'], ['active', 'pending'], true)) {
        $error = "This item is no longer accepting claims.";
    } elseif (empty($proof_desc) || empty($proof_uid)) {
        $error = "Please provide both description and unique identifier proof.";
    } elseif ($item['reporter_id'] == $_SESSION['user_id']) {
        $error = "You reported this item — you cannot claim it as lost property.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO claims (item_id, claimant_id, claimant_school_id, proof_description, unique_identifier_proof)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$item_id, $_SESSION['user_id'], $_SESSION['school_id'], $proof_desc, $proof_uid]);

        // Auto-approve if unique identifiers match exactly
        if (!empty($item['unique_identifier']) && $proof_uid === $item['unique_identifier']) {
            $conn->prepare("UPDATE claims SET status='under_review' WHERE claim_id = LAST_INSERT_ID()")->execute();
            $conn->prepare("UPDATE items SET status='matched' WHERE item_id = ?")->execute([$item_id]);
            $auto_msg = "✅ Identifier matches on record — sent for final admin verification.";
        } else {
            $success = "Claim submitted! Admin will review your proof.";
        }
    }
}

// Load existing claims for this item (admin only)
$claims = [];
if ($_SESSION['role'] === 'admin') {
    $stmt = $conn->prepare("SELECT c.*, u.full_name FROM claims c JOIN users u ON c.claimant_id = u.user_id WHERE item_id = ? ORDER BY created_at DESC");
    $stmt->execute([$item_id]);
    $claims = $stmt->fetchAll();
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="card">
    <span class="badge <?= $item['item_type']==='lost'?'badge-pending':'badge-active' ?>">
        <?= ucfirst($item['item_type']) ?>
    </span>
    <h2 style="margin:1rem 0;"><?= e($item['title']) ?></h2>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem; margin:1.5rem 0;">
        <div>
            <p><strong>Category:</strong> <?= e($item['category']) ?></p>
            <p><strong>Brand:</strong> <?= e($item['brand'] ?? 'Not specified') ?></p>
            <p><strong>Color:</strong> <?= e($item['color'] ?? 'Not specified') ?></p>
            <p><strong>Location:</strong> <?= e($item['location_found'] ?? 'Not specified') ?></p>
            <p><strong>Reported:</strong> <?= date('F j, Y g:i A', strtotime($item['date_reported'])) ?></p>
            <?php if ($item['hold_until']): ?>
            <p><strong>Hold Until:</strong> <?= date('F j, Y', strtotime($item['hold_until'])) ?></p>
            <?php endif; ?>
            <p><strong>Status:</strong> 
                <span class="badge badge-<?= $item['status']==='claimed'?'claimed':($item['status']==='matched'?'active':'pending') ?>">
                    <?= ucfirst($item['status']) ?>
                </span>
            </p>
        </div>
        <div>
            <h4>Description</h4>
            <p><?= e($item['description']) ?></p>
        </div>
    </div>

    <?php if (($_SESSION['role'] ?? '') !== 'admin' && $item['item_type'] === 'found' && in_array($item['status'], ['active', 'pending'], true) && !$hasApprovedClaim): ?>
    <hr style="margin:2rem 0;">
    <h3>📝 Claim This Item</h3>
    <div class="id-note">
        Provide proof that only the true owner would know. Your School ID is recorded in the chain-of-custody log.
    </div>

    <?php if (!empty($error)): ?><p style="color:red;"><?= e($error) ?></p><?php endif; ?>
    <?php if (!empty($auto_msg)): ?><p style="color:green;"><?= e($auto_msg) ?></p><?php endif; ?>
    <?php if (!empty($success)): ?><p style="color:green;"><?= e($success) ?></p><?php endif; ?>

    <form method="POST">
        <label>Proof of Ownership — Description</label>
        <textarea name="proof_description" rows="3" required placeholder="Describe unique marks, contents, history..."></textarea>

        <label>Unique Identifier (Serial, ID number, private mark)</label>
        <input type="text" name="unique_identifier_proof" required placeholder="Must match what you would have reported as owner">

        <button type="submit">Submit Claim</button>
    </form>
    <?php endif; ?>

    <?php if (($_SESSION['role'] ?? '') !== 'admin' && $item['item_type'] === 'found' && (!in_array($item['status'], ['active', 'pending'], true) || $hasApprovedClaim)): ?>
    <hr style="margin:2rem 0;">
    <p class="id-note">This item is no longer accepting claims.</p>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'admin' && $claims): ?>
    <hr style="margin:2rem 0;">
    <h3>🔐 Claims for This Item (Admin Only)</h3>
    <?php foreach ($claims as $claim): ?>
    <div style="border:1px solid #e2e8f0; border-radius:6px; padding:1rem; margin:1rem 0;">
        <p><strong>Claimant:</strong> <?= e($claim['full_name']) ?> (<?= e($claim['claimant_school_id']) ?>)</p>
        <p><strong>Status:</strong> <span class="badge badge-<?= $claim['status']==='approved'?'claimed':($claim['status']==='rejected'?'disputed':'active') ?>"><?= ucfirst($claim['status']) ?></span></p>
        <p><strong>Proof Desc:</strong> <?= e($claim['proof_description']) ?></p>
        <p><strong>UID Proof:</strong> <code><?= e($claim['unique_identifier_proof']) ?></code></p>
        <a href="admin/verify_claim.php?claim_id=<?= $claim['claim_id'] ?>" style="color:#1e40af; font-weight:600;">Review & Verify →</a>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
