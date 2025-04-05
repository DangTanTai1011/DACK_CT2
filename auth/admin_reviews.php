<?php
session_start();
include '../config/db.php';

// Kiểm tra xem admin đã đăng nhập chưa
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Bạn cần đăng nhập với tài khoản admin để truy cập trang này.";
    header("Location: ../Auth/login.php");
    exit();
}

// Xử lý xóa đánh giá
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $review_id = $_POST['review_id'] ?? '';

    if ($review_id) {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        $_SESSION['message'] = "Đánh giá đã được xóa thành công!";
        header("Location: admin_reviews.php");
        exit();
    } else {
        $_SESSION['message'] = "Không tìm thấy đánh giá để xóa.";
        header("Location: admin_reviews.php");
        exit();
    }
}

// Lấy danh sách đánh giá
$stmt = $pdo->query("SELECT r.*, u.username, p.name AS product_name 
                     FROM reviews r 
                     LEFT JOIN users u ON r.user_id = u.id 
                     LEFT JOIN products p ON r.product_id = p.id 
                     ORDER BY r.created_at DESC");
$reviews = $stmt->fetchAll();

// Include header.php after all header() calls
include '../includes/header.php';
?>

<section class="furniture_section layout_padding">
    <div class="container">
        <div class="heading_container">
            <h2>Quản Lý Đánh Giá</h2>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <?php if (empty($reviews)): ?>
            <div class="box">
                <div class="detail-box">
                    <p>Chưa có đánh giá nào.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="box">
                <div class="detail-box">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Khách Hàng</th>
                                <th>Sản Phẩm</th>
                                <th>Nội Dung</th>
                                <th>Ngày Tạo</th>
                                <th>Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td><?php echo $review['id']; ?></td>
                                    <td><?php echo htmlspecialchars($review['username'] ?? 'Khách vãng lai'); ?></td>
                                    <td><?php echo htmlspecialchars($review['product_name'] ?? 'Không xác định'); ?></td>
                                    <td><?php echo htmlspecialchars($review['comment']); ?></td>
                                    <td><?php echo $review['created_at']; ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                                        </form>
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