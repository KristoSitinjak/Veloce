<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/path.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/orders.php';

requireAdmin();

$page_title = 'Detail User - Admin Veloce';

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get user details
$stmt = $pdo->prepare("
    SELECT * FROM akun WHERE id = ?
");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: " . url('admin/users.php'));
    exit;
}

// Get user's orders
$userOrders = getUserOrders($userId, 10);

include __DIR__ . '/../partials/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Detail User: <?php echo htmlspecialchars($user['username']); ?></h1>
        <a href="<?php echo url('admin/users.php'); ?>" class="btn-add">Kembali ke Daftar User</a>
    </div>

    <div class="user-detail-grid">
        <div class="user-detail-section">
            <h3>Informasi Akun</h3>
            <table class="detail-table">
                <tr>
                    <td><strong>ID</strong></td>
                    <td><?php echo $user['id']; ?></td>
                </tr>
                <tr>
                    <td><strong>Username</strong></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                </tr>
                <tr>
                    <td><strong>Role</strong></td>
                    <td>
                        <span class="role-badge role-<?php echo $user['role']; ?>">
                            <?php echo $user['role'] === 'admin' ? 'Admin' : 'User'; ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Tanggal Daftar</strong></td>
                    <td><?php echo date('d F Y, H:i', strtotime($user['created_at'])); ?></td>
                </tr>
            </table>
        </div>

        <div class="user-detail-section full-width">
            <h3>Riwayat Pesanan (<?php echo count($userOrders); ?> pesanan)</h3>
            <?php if (empty($userOrders)): ?>
                <p style="text-align: center; padding: 30px; color: #666;">User ini belum pernah melakukan pesanan.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userOrders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td><strong>Rp. <?php echo number_format($order['total'], 0, ',', '.'); ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo getOrderStatusLabel($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo url('admin/order-detail.php?id=' . $order['id']); ?>" class="btn-edit">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.user-detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.user-detail-section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.user-detail-section.full-width {
    grid-column: 1 / -1;
}

.user-detail-section h3 {
    color: #1f3b83;
    margin-bottom: 15px;
    font-size: 18px;
}

.detail-table {
    width: 100%;
    border-collapse: collapse;
}

.detail-table td {
    padding: 10px 0;
    border-bottom: 1px solid #ecf0ff;
}

.detail-table td:first-child {
    width: 40%;
    color: #666;
}

.detail-table tr:last-child td {
    border-bottom: none;
}

.role-badge {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-block;
}

.role-badge.role-admin {
    background: #d1ecf1;
    color: #0c5460;
}

.role-badge.role-user {
    background: #d4edda;
    color: #155724;
}

.status-badge {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-block;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-verified {
    background: #d1ecf1;
    color: #0c5460;
}

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-shipped {
    background: #d4edda;
    color: #155724;
}

.status-delivered {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.admin-table {
    width: 100%;
    margin-top: 15px;
}

.admin-table table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th {
    background: #f8faff;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #1f3b83;
    border-bottom: 2px solid #ecf0ff;
}

.admin-table td {
    padding: 12px;
    border-bottom: 1px solid #ecf0ff;
}

.admin-table tr:last-child td {
    border-bottom: none;
}

.admin-table tr:hover {
    background: #f8faff;
}

.btn-edit {
    background: #5b8af0;
    color: white;
    padding: 8px 16px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: background 0.3s;
    display: inline-block;
}

.btn-edit:hover {
    background: #1f3b83;
}
</style>

<?php include __DIR__ . '/../partials/footer.php'; ?>
