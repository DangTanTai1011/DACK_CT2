<?php
session_start();
include '../config/db.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Bạn cần đăng nhập để xem lịch sử mua hàng.";
    header("Location: ../Auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy danh sách đơn hàng của người dùng
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Include header.php after all header() calls
include '../includes/header.php';
?>

<section class="furniture_section layout_padding">
    <div class="container">
        <div class="heading_container">
            <h2>Lịch Sử Mua Hàng</h2>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="box">
                <div class="detail-box">
                    <p>Bạn chưa có đơn hàng nào.</p>
                    <a href="<?php echo BASE_URL; ?>/furniture.php" class="btn btn-primary" style="background-color: #f7444e; border: none;">Mua sắm ngay</a>
                </div>
            </div>
        <?php else: ?>
            <div class="box">
                <div class="detail-box">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID Đơn Hàng</th>
                                <th>Ngày Đặt</th>
                                <th>Tổng Tiền</th>
                                <th>Trạng Thái</th>
                                <th>Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo $order['created_at']; ?></td>
                                    <td><?php echo number_format($order['total'], 0, ',', '.'); ?> VNĐ</td>
                                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                                    <td>
                                    <a href="<?php echo BASE_URL; ?>/Auth/order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">Xem Chi Tiết</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>