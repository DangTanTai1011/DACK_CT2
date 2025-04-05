<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Lấy danh sách danh mục
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

// Nhận dữ liệu lọc từ GET
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$rating = $_GET['rating'] ?? '';
$price_range = $_GET['price_range'] ?? '';

// Xử lý khoảng giá
$min_price = '';
$max_price = '';
if ($price_range) {
    list($min_price, $max_price) = explode('-', $price_range);
}

// Xây dựng câu lệnh SQL để tìm kiếm sản phẩm
$sql = "SELECT p.*, c.name AS category_name, 
        COALESCE(AVG(r.rating), 0) AS avg_rating
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN reviews r ON p.id = r.product_id
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}

if ($category) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category;
}

if ($min_price !== '') {
    $sql .= " AND p.price >= ?";
    $params[] = $min_price;
}

if ($max_price !== '') {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
}

$sql .= " GROUP BY p.id";

if ($rating !== '') {
    $sql .= " HAVING avg_rating >= ?";
    $params[] = $rating;
}

// Thực thi câu lệnh SQL và lấy danh sách sản phẩm
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Kiểm tra xem có thông báo nào không
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết Quả Tìm Kiếm</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .filter-form { margin-bottom: 20px; }
        .filter-form .form-control { margin-bottom: 10px; }
        .box { border: 1px solid #ddd; padding: 15px; text-align: center; margin-bottom: 20px; border-radius: 8px; }
        .img-box img { max-width: 100%; height: auto; border-radius: 8px; }
        .price_heading { font-size: 18px; color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">Kết Quả Tìm Kiếm</h2>

        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Hiển thị danh sách sản phẩm -->
        <div class="row">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $p): ?>
                    <div class="col-md-4">
                        <div class="box">
                            <div class="img-box">
                                <img src="../image/<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                            </div>
                            <div class="detail-box">
                                <h5><?= htmlspecialchars($p['name']) ?></h5>
                                <p><strong>Danh mục:</strong> <?= htmlspecialchars($p['category_name']) ?></p>
                                <div class="price_box">
                                    <h6 class="price_heading">
                                        <span>₫</span> <?= number_format($p['price'], 0, ',', '.') ?> VNĐ
                                    </h6>
                                    <p><strong>Đánh giá trung bình:</strong> <?= number_format($p['avg_rating'], 1) ?> / 5</p>
                                    <a href="../product/product_detail.php?id=<?= $p['id'] ?>" class="btn btn-info">Xem Chi Tiết</a>
                                    <form action="../Cart/add_to_cart.php" method="POST" class="mt-2">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-success">🛒 Thêm vào giỏ hàng</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center">Không có sản phẩm nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php include '../includes/footer.php'; ?>
