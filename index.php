<?php
session_start();  // Keep session_start() here, at the very top of index.php

include './config/db.php';
include './includes/header.php';

// Lấy danh sách danh mục
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

// Nhận dữ liệu lọc từ GET
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Prepare SQL query
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

// Nhóm các sản phẩm theo ID
$sql .= " GROUP BY p.id";

// Thực thi câu lệnh
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
    <title>Danh Sách Sản Phẩm</title>
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
        <h2 class="text-center">Danh Sách Sản Phẩm</h2>

        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Form lọc sản phẩm -->
        <form method="GET" action="Product/search_results.php" class="filter-form row">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="category" class="form-control">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="price_range" class="form-control">
                    <option value="">Chọn khoảng giá</option>
                    <option value="0-500000" <?= $min_price == '0' && $max_price == '500000' ? 'selected' : '' ?>>0 - 500.000 VNĐ</option>
                    <option value="500000-1000000" <?= $min_price == '500000' && $max_price == '1000000' ? 'selected' : '' ?>>500.000 - 1.000.000 VNĐ</option>
                    <option value="1000000-1500000" <?= $min_price == '1000000' && $max_price == '1500000' ? 'selected' : '' ?>>1.000.000 - 1.500.000 VNĐ</option>
                    <option value="1500000-2000000" <?= $min_price == '1500000' && $max_price == '2000000' ? 'selected' : '' ?>>1.500.000 - 2.000.000 VNĐ</option>
                    <!-- Add more ranges as needed -->
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                <a href="?" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="row">
            <?php foreach ($products as $p): ?>
                <div class="col-md-4">
                    <div class="box">
                        <div class="img-box">
                            <img src="./image/<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                        </div>
                        <div class="detail-box">
                            <h5><?= htmlspecialchars($p['name']) ?></h5>
                            <p><strong>Danh mục:</strong> <?= htmlspecialchars($p['category_name']) ?></p>
                            <div class="price_box">
                                <h6 class="price_heading">
                                    <span>₫</span> <?= number_format($p['price'], 0, ',', '.') ?> VNĐ
                                </h6>
                                <p><strong>Đánh giá trung bình:</strong> <?= number_format($p['avg_rating'], 1) ?> / 5</p>
                                <a href="./product/product_detail.php?id=<?= $p['id'] ?>" class="btn btn-info">Xem Chi Tiết</a>
                                 <!-- Nút Thêm vào giỏ hàng -->
                                 <form action="./Cart/add_to_cart.php" method="POST" class="mt-2">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-success">🛒 Thêm vào giỏ hàng</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>

<?php include './includes/footer.php'; ?>
