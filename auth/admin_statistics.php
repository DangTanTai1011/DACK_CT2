<?php
session_start();

// Debug session để kiểm tra
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    die("Session không tồn tại hoặc bị mất!");
}

// Kiểm tra quyền truy cập admin trước khi có bất kỳ output nào
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Bạn cần đăng nhập với tài khoản admin để truy cập trang này.";
    header("Location: ../Auth/login.php");
    exit();
}

// Include file sau khi kiểm tra session
include '../config/db.php';
include '../includes/header.php';

// Lấy dữ liệu thống kê
$total_revenue = $pdo->query("SELECT SUM(total) FROM orders WHERE status = 'confirmed'")->fetchColumn() ?? 0;
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'confirmed'")->fetchColumn() ?? 0;

// Thống kê sản phẩm bán chạy
$top_products = $pdo->query("SELECT p.name, SUM(od.quantity) as total_sold 
                             FROM order_details od 
                             JOIN products p ON od.product_id = p.id 
                             JOIN orders o ON od.order_id = o.id 
                             WHERE o.status = 'confirmed' 
                             GROUP BY p.id, p.name 
                             ORDER BY total_sold DESC 
                             LIMIT 5")->fetchAll();
?>

<section class="furniture_section layout_padding">
    <div class="container">
        <div class="heading_container">
            <h2>Thống Kê Doanh Thu</h2>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="box">
                    <div class="detail-box">
                        <h5>Tổng Doanh Thu</h5>
                        <p><?php echo number_format($total_revenue, 0, ',', '.'); ?> VNĐ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box">
                    <div class="detail-box">
                        <h5>Tổng Đơn Hàng</h5>
                        <p><?php echo $total_orders; ?> đơn hàng</p>
                    </div>
                </div>
            </div>
        </div>

        <h4>Sản Phẩm Bán Chạy</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Sản Phẩm</th>
                    <th>Số Lượng Bán</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo $product['total_sold']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include '../includes/footer.php'; ?>