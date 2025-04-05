<?php
session_start();
include '../config/db.php';

// Kiểm tra xem admin đã đăng nhập chưa
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Bạn cần đăng nhập với tài khoản admin để truy cập trang này.";
    header("Location: ../Auth/login.php");
    exit();
}

// Xử lý xác nhận hoặc hủy đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? '';
    $action = $_POST['action'] ?? '';

    if ($order_id && in_array($action, ['confirm', 'cancel'])) {
        $status = $action === 'confirm' ? 'confirmed' : 'cancelled';
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        $_SESSION['message'] = "Đơn hàng đã được " . ($action === 'confirm' ? 'xác nhận' : 'hủy') . " thành công!";
        header("Location: admin_orders.php");
        exit();
    }
}

// Lấy danh sách đơn hàng
$stmt = $pdo->query("SELECT o.*, u.username 
                     FROM orders o 
                     LEFT JOIN users u ON o.user_id = u.id 
                     ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();

// Include header.php after all header() calls
include '../includes/header.php';
?>

<section class="furniture_section layout_padding">
    <div class="container">
        <div class="heading_container">
            <h2>Quản Lý Đơn Hàng</h2>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách Hàng</th>
                    <th>Tổng Tiền</th>
                    <th>Trạng Thái</th>
                    <th>Ngày Tạo</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['username'] ?? 'Khách vãng lai'); ?></td>
                        <td><?php echo number_format($order['total'], 0, ',', '.'); ?> VNĐ</td>
                        <td><?php echo $order['status']; ?></td>
                        <td><?php echo $order['created_at']; ?></td>
                        <td>
                            <?php if ($order['status'] === 'pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="action" value="confirm">
                                    <button type="submit" class="btn btn-success">Xác Nhận</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="action" value="cancel">
                                    <button type="submit" class="btn btn-danger">Hủy</button>
                                </form>
                            <?php endif; ?>
                            <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-info">Xem Chi Tiết</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include '../includes/footer.php'; ?>