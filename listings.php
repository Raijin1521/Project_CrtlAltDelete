<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Filters
$type = $_GET['type'] ?? 'all';
$search = $_GET['search'] ?? '';
$item_name = $_GET['item_name'] ?? '';

$where = [];
if ($type === 'lost') $where[] = "status = 'lost'";
if ($type === 'found') $where[] = "status = 'found'";
if ($search) {
    $s = "%$search%";
    $where[] = "(item_name LIKE '$s' OR description LIKE '$s' OR location LIKE '$s')";
}
if ($item_name) {
    $in = "%$item_name%";
    $where[] = "item_name LIKE '$in'";
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$result = $conn->query("SELECT * FROM items $where_sql ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>View All Items — ACLC Lost & Found</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="listings-body">
    <!-- Top Header Bar -->
    <header class="top-header">
        <h3>ACLC LOST AND FOUND</h3>
        <div class="header-right">
            <span>Welcome, <?php echo $_SESSION['full_name']; ?></span>
            <a href="logout.php" class="logout-btn">Log Out</a>
        </div>
    </header>

    <!-- Main Card -->
    <div class="listings-card">
        <!-- Close Button -->
        <a href="home.php" class="close-btn">✕</a>

        <h1>View All Items</h1>

        <!-- Toggle Buttons -->
        <div class="toggle-buttons">
            <a href="listings.php?type=lost" class="toggle-btn <?= $type==='lost' || $type==='all' ? 'active' : '' ?>">Lost Items</a>
            <a href="listings.php?type=found" class="toggle-btn <?= $type==='found' ? 'active' : '' ?>">Founded Items</a>
        </div>

        <!-- Search Bar -->
        <form method="GET" class="search-form">
            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="search-icon">🔍</button>
        </form>

        <!-- Item Name Filter & View Details -->
        <form method="GET" class="detail-form">
            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
            <input type="text" name="item_name" placeholder="Item Name*" value="<?= htmlspecialchars($item_name) ?>">
            <button type="submit" class="view-btn">View Details</button>
        </form>

        <!-- Results Table -->
        <?php if ($result && $result->num_rows > 0): ?>
        <div class="results-table">
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['item_name']) ?></strong></td>
                        <td class="status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></td>
                        <td><?= htmlspecialchars($row['category'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td><?= substr($row['created_at'], 0, 10) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="no-results">No items found.</p>
        <?php endif; ?>
    </div>
</body>
</html>