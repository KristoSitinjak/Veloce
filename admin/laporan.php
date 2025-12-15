<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/path.php';
require_once __DIR__ . '/../config/db.php';

requireAdmin();

$page_title = 'Laporan Penjualan - Admin Veloce';

// Get sales statistics
// Total penjualan
$stmt = $pdo->query("SELECT COUNT(*) as total_orders, SUM(total) as total_revenue FROM orders WHERE status != 'cancelled'");
$salesStats = $stmt->fetch();

// Penjualan per bulan (6 bulan terakhir)
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as total_orders,
        SUM(total) as revenue
    FROM orders
    WHERE status != 'cancelled'
    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");
$monthlySales = $stmt->fetchAll();

// Penjualan per status
$stmt = $pdo->query("
    SELECT status, COUNT(*) as count 
    FROM orders 
    GROUP BY status
");
$statusData = $stmt->fetchAll();

// Top 5 produk terlaris
$stmt = $pdo->query("
    SELECT 
        product_name,
        SUM(quantity) as total_sold,
        SUM(subtotal) as revenue
    FROM order_items
    GROUP BY product_name
    ORDER BY total_sold DESC
    LIMIT 5
");
$topProducts = $stmt->fetchAll();

// Penjualan per kategori
$stmt = $pdo->query("
    SELECT 
        product_category,
        SUM(quantity) as total_sold,
        SUM(subtotal) as revenue
    FROM order_items
    GROUP BY product_category
");
$categoryData = $stmt->fetchAll();

include __DIR__ . '/../partials/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Laporan Penjualan</h1>
        <a href="<?php echo url('admin/dashboard.php'); ?>" class="btn-add">Kembali ke Dashboard</a>
    </div>

    <!-- Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #5b8af0;">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($salesStats['total_orders'] ?? 0); ?></h3>
                <p>Total Pesanan</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #28a745;">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-info">
                <h3>Rp <?php echo number_format($salesStats['total_revenue'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Total Pendapatan</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #ffc107;">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-info">
                <h3>Rp <?php echo number_format(($salesStats['total_revenue'] ?? 0) / max($salesStats['total_orders'] ?? 1, 1), 0, ',', '.'); ?></h3>
                <p>Rata-rata per Pesanan</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #dc3545;">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo count($topProducts); ?></h3>
                <p>Produk Terlaris</p>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3>Penjualan Bulanan (6 Bulan Terakhir)</h3>
            <canvas id="monthlySalesChart"></canvas>
        </div>

        <div class="chart-card">
            <h3>Status Pesanan</h3>
            <canvas id="statusChart"></canvas>
        </div>

        <div class="chart-card">
            <h3>Penjualan per Kategori</h3>
            <canvas id="categoryChart"></canvas>
        </div>

        <div class="chart-card">
            <h3>Top 5 Produk Terlaris</h3>
            <canvas id="topProductsChart"></canvas>
        </div>
    </div>

    <!-- Top Products Table -->
    <div class="admin-table" style="margin-top: 30px;">
        <h3 style="margin-bottom: 15px;">Produk Terlaris</h3>
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Total Terjual</th>
                    <th>Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topProducts as $product): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($product['product_name']); ?></strong></td>
                        <td><?php echo $product['total_sold']; ?> unit</td>
                        <td><strong>Rp <?php echo number_format($product['revenue'], 0, ',', '.'); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
}

.stat-info h3 {
    margin: 0;
    font-size: 24px;
    color: #1f3b83;
}

.stat-info p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 14px;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.chart-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.chart-card h3 {
    margin: 0 0 20px 0;
    color: #1f3b83;
    font-size: 18px;
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

@media (max-width: 768px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Monthly Sales Chart
const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
new Chart(monthlySalesCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthlySales, 'month')); ?>,
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: <?php echo json_encode(array_column($monthlySales, 'revenue')); ?>,
            borderColor: '#5b8af0',
            backgroundColor: 'rgba(91, 138, 240, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($statusData, 'status')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($statusData, 'count')); ?>,
            backgroundColor: [
                '#ffc107',
                '#17a2b8',
                '#007bff',
                '#28a745',
                '#28a745',
                '#dc3545'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Category Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_map(function($cat) {
            return ucfirst(str_replace('_', ' ', $cat));
        }, array_column($categoryData, 'product_category'))); ?>,
        datasets: [{
            label: 'Total Terjual',
            data: <?php echo json_encode(array_column($categoryData, 'total_sold')); ?>,
            backgroundColor: ['#5b8af0', '#28a745', '#ffc107']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Top Products Chart
const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
new Chart(topProductsCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($topProducts, 'product_name')); ?>,
        datasets: [{
            label: 'Unit Terjual',
            data: <?php echo json_encode(array_column($topProducts, 'total_sold')); ?>,
            backgroundColor: '#1f3b83'
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
