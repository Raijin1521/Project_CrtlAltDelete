<?php
// Validate School ID = exactly 11 digits
function validateSchoolID($id) {
    return preg_match('/^\d{11}$/', $id);
}

// Calculate hold period based on item type
function getHoldPeriod($isHighValue, $isSensitiveDoc) {
    if ($isSensitiveDoc) return 60; // days
    if ($isHighValue) return 30;
    return 7;
}

// Basic auto-matching logic
function findMatches($conn, $newItem, $type) {
    $opposite = ($type === 'lost') ? 'found' : 'lost';
    $stmt = $conn->prepare("
        SELECT * FROM items 
        WHERE item_type = ? 
          AND status = 'active'
          AND (category = ? OR title LIKE ? OR color = ?)
    ");
    $titleLike = "%{$newItem['title']}%";
    $stmt->execute([$opposite, $newItem['category'], $titleLike, $newItem['color']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Sanitize output to prevent XSS
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES);
}
?>