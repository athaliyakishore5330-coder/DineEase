<?php
// api/staff_list.php
header('Content-Type: application/json');
require __DIR__ . '/../db_connect.php';

// --- TEMPORARY: BYPASS AUTH FOR PREVIEW ---
$is_preview_mode = true; // Set to false to re-enable security

session_start();

// --- Access Control ---
if (
    !$is_preview_mode &&
    (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')
) {
    http_response_code(403);
    die(json_encode([
        'success' => false,
        'message' => 'Access Denied. Admin privileges required.'
    ]));
}

// --- Preview mode fallback (auto-assign an admin role for demo/testing) ---
if ($is_preview_mode) {
    $stmt = $pdo->query("SELECT staff_id, role FROM staff WHERE role = 'admin' LIMIT 1");
    $preview_user = $stmt->fetch();
    if ($preview_user) {
        $_SESSION['staff_id'] = $preview_user['staff_id'];
        $_SESSION['role'] = $preview_user['role'];
    }
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // --- READ STAFF LIST ---
        $stmt = $pdo->query("
            SELECT staff_id, username, role, created_at
            FROM staff
            ORDER BY staff_id ASC
        ");
        $staff = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'staff' => $staff
        ]);
    } else {
        // Unsupported HTTP Method
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not supported.'
        ]);
    }
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred.',
        'error' => $e->getMessage() // (optional: remove in production)
    ]);
}
?>
