<?php
session_start();
require '../config/db.php';

// Kiểm tra nếu user chưa đăng nhập
$user_id = $_SESSION['user_id'] ?? null;

// Lấy thông tin sản phẩm
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: ../index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

// Lấy đánh giá sản phẩm
$reviewsStmt = $pdo->prepare("SELECT r.*, u.username FROM reviews r 
                              JOIN users u ON r.user_id = u.id 
                              WHERE r.product_id = ? ORDER BY r.created_at DESC");
$reviewsStmt->execute([$id]);
$reviews = $reviewsStmt->fetchAll();

// Tính trung bình đánh giá
$avgStmt = $pdo->prepare("SELECT ROUND(AVG(rating),1) FROM reviews WHERE product_id = ?");
$avgStmt->execute([$id]);
$avg = $avgStmt->fetchColumn() ?? 0;

// Xử lý gửi đánh giá mới
if ($_SERVER["REQUEST_METHOD"] === "POST" && $user_id) {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // ✅ Luôn thêm đánh giá mới thay vì cập nhật đánh giá cũ
    $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$id, $user_id, $rating, $comment]);

    header("Location: product_detail.php?id=" . $id);
    exit();
}
?>

<?php include '../includes/header.php'; ?>

<h2>🛒 Chi tiết sản phẩm</h2>
<h3><?= htmlspecialchars($product['name']) ?></h3>
<img src="../image/<?= htmlspecialchars($product['image_url']) ?>" width="250"><br><br>
<p><strong>Giá:</strong> <?= number_format($product['price'], 0, ',', '.') ?> VNĐ</p>
<p><strong>Danh mục:</strong> <?= htmlspecialchars($product['category_name']) ?></p>
<p><strong>Mô tả:</strong> <?= nl2br(htmlspecialchars($product['description'])) ?></p>
<p><strong>Đánh giá trung bình:</strong> <?= $avg ?>/5 ⭐</p>

<hr>
<h4>💬 Đánh giá người dùng</h4>
<?php if ($reviews): ?>
    <?php foreach ($reviews as $r): ?>
        <p><strong><?= htmlspecialchars($r['username']) ?>:</strong> ⭐ <?= $r['rating'] ?>/5<br>
        <?= nl2br(htmlspecialchars($r['comment'])) ?></p>
        <hr>
    <?php endforeach; ?>
<?php else: ?>
    <p><em>Chưa có đánh giá nào.</em></p>
<?php endif; ?>

<?php if ($user_id): ?>
    <h4>📝 Gửi đánh giá của bạn</h4>

    <form method="POST">
        Số sao:
        <select name="rating" required>
            <option value="">-- Chọn sao --</option>
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?= $i ?>"><?= $i ?> - <?= str_repeat("⭐", $i) ?></option>
            <?php endfor; ?>
        </select><br><br>

        Nhận xét:<br>
        <textarea name="comment" rows="4" cols="50" required></textarea><br><br>

        <button type="submit">Gửi đánh giá</button>
    </form>
<?php else: ?>
    <p><em>🔒 Vui lòng <a href="../auth/login.php">đăng nhập</a> để gửi đánh giá.</em></p>
<?php endif; ?>

<hr>
<h4>🔄 Sản phẩm tương tự</h4>
<div style="display: flex; gap: 15px;">
    <?php
    $relatedStmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
    $relatedStmt->execute([$product['category_id'], $id]);
    $relatedProducts = $relatedStmt->fetchAll();
    ?>

    <?php if ($relatedProducts): ?>
        <?php foreach ($relatedProducts as $related): ?>
            <div style="border: 1px solid #ddd; padding: 10px; width: 200px;">
                <a href="product_detail.php?id=<?= $related['id'] ?>">
                    <img src="../image/<?= htmlspecialchars($related['image_url']) ?>" width="150"><br>
                    <strong><?= htmlspecialchars($related['name']) ?></strong><br>
                    <?= number_format($related['price'], 0, ',', '.') ?> VNĐ
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p><em>Không có sản phẩm tương tự.</em></p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
