<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Bạn cần đăng nhập để xem chi tiết đơn hàng.";
    header("Location: ../Auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'] ?? '';

// Lấy thông tin đơn hàng
$stmt = $pdo->prepare("SELECT o.*, u.username 
                       FROM orders o 
                       LEFT JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['message'] = "Đơn hàng không tồn tại.";
    header("Location: admin_orders.php");
    exit();
}

// Lấy chi tiết đơn hàng
$stmt = $pdo->prepare("SELECT od.*, p.name AS product_name 
                       FROM order_details od 
                       JOIN products p ON od.product_id = p.id 
                       WHERE od.order_id = ?");
$stmt->execute([$order_id]);
$order_details = $stmt->fetchAll();

// Lấy lịch sử mua hàng của khách hàng
$user_id = $order['user_id'];
$stmt = $pdo->prepare("SELECT o.*, u.username 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.user_id = ? AND o.id != ? 
                       ORDER BY o.created_at DESC");
$stmt->execute([$user_id, $order_id]);
$order_history = $stmt->fetchAll();
?>

<section class="furniture_section layout_padding">
    <div class="container">
        <div class="heading_container">
            <h2>Chi Tiết Đơn Hàng #<?php echo $order['id']; ?></h2>
        </div>

        <h4>Thông Tin Đơn Hàng</h4>
        <p><strong>Khách Hàng:</strong> <?php echo htmlspecialchars($order['username'] ?? 'Khách vãng lai'); ?></p>
        <p><strong>Tổng Tiền:</strong> <?php echo number_format($order['total'], 0, ',', '.'); ?> VNĐ</p>
        <p><strong>Trạng Thái:</strong> <?php echo $order['status']; ?></p>
        <p><strong>Ngày Tạo:</strong> <?php echo $order['created_at']; ?></p>
        <p><strong>Họ và Tên:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
        <p><strong>Số Điện Thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
        <p><strong>Địa Chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
        <p><strong>Ghi Chú:</strong> <?php echo htmlspecialchars($order['note'] ?? 'Không có ghi chú'); ?></p>

        <h4>Danh Sách Sản Phẩm</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Sản Phẩm</th>
                    <th>Số Lượng</th>
                    <th>Giá</th>
                    <th>Tổng</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_details as $detail): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($detail['product_name']); ?></td>
                        <td><?php echo $detail['quantity']; ?></td>
                        <td><?php echo number_format($detail['price'], 0, ',', '.'); ?> VNĐ</td>
                        <td><?php echo number_format($detail['price'] * $detail['quantity'], 0, ',', '.'); ?> VNĐ</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h4>Lịch Sử Mua Hàng Của Khách Hàng</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Đơn Hàng</th>
                    <th>Tổng Tiền</th>
                    <th>Trạng Thái</th>
                    <th>Ngày Tạo</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_history as $history): ?>
                    <tr>
                        <td><?php echo $history['id']; ?></td>
                        <td><?php echo number_format($history['total'], 0, ',', '.'); ?> VNĐ</td>
                        <td><?php echo $history['status']; ?></td>
                        <td><?php echo $history['created_at']; ?></td>
                        <td>
                            <a href="order_details.php?order_id=<?php echo $history['id']; ?>" class="btn btn-info">Xem Chi Tiết</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include '../includes/footer.php'; ?>