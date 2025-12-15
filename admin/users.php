<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/path.php';
require_once __DIR__ . '/../config/db.php';

requireAdmin();

$page_title = 'Kelola User - Admin Veloce';

// Get all users
$stmt = $pdo->prepare("
    SELECT id, username, role, created_at 
    FROM akun 
    ORDER BY created_at DESC
");
$stmt->execute();
$users = $stmt->fetchAll();

include __DIR__ . '/../partials/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Kelola User</h1>
        <a href="<?php echo url('admin/dashboard.php'); ?>" class="btn-add">Kembali ke Dashboard</a>
    </div>

    <?php if (empty($users)): ?>
        <div class="admin-table">
            <p style="text-align: center; padding: 40px; color: #666;">Tidak ada user ditemukan.</p>
        </div>
    <?php else: ?>
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td>
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo $user['role'] === 'admin' ? 'Admin' : 'User'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="<?php echo url('admin/user-detail.php?id=' . $user['id']); ?>" class="btn-edit">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
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

.admin-table table {
    width: 100%;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.admin-table th {
    background: #1f3b83;
    color: white;
    padding: 15px;
    text-align: left;
    font-weight: 600;
}

.admin-table td {
    padding: 15px;
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
