<?php
session_start();
require '../config/db.php';
include '../includes/header.php'; // Khôi phục header.php

// Kiểm tra nếu user chưa đăng nhập
$user_id = $_SESSION['user_id'] ?? null;

// Lấy thông tin sản phẩm
$id = $_GET['id'] ?? null;
if (!$id) {
    $_SESSION['error'] = 'Sản phẩm không tồn tại.';
    header("Location: ../index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error'] = 'Sản phẩm không tồn tại.';
    header("Location: ../index.php");
    exit;
}

// Lấy đánh giá sản phẩm
$reviewsStmt = $pdo->prepare("SELECT r.*, u.username FROM reviews r 
                              JOIN users u ON r.user_id = u.id 
                              WHERE r.product_id = ? ORDER BY r.created_at DESC");
$reviewsStmt->execute([$id]);
$reviews = $reviewsStmt->fetchAll();

// Tính trung bình đánh giá
$avgStmt = $pdo->prepare("SELECT ROUND(AVG(rating), 1) FROM reviews WHERE product_id = ?");
$avgStmt->execute([$id]);
$avg = $avgStmt->fetchColumn() ?? 0;

// Xử lý gửi đánh giá mới
if ($_SERVER["REQUEST_METHOD"] === "POST" && $user_id) {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Thêm đánh giá mới
    $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt->execute([$id, $user_id, $rating, $comment])) {
        $_SESSION['success'] = 'Đánh giá của bạn đã được gửi thành công!';
    } else {
        $_SESSION['error'] = 'Gửi đánh giá thất bại. Vui lòng thử lại.';
    }

    header("Location: product_detail.php?id=" . $id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sản phẩm - <?= htmlspecialchars($product['name']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- CSS tùy chỉnh -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 0;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            background: #fff;
            border: none;
            margin-bottom: 2rem;
        }

        .card-header {
            background: #fff;
            border-bottom: none;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2, .card-header h4 {
            color: #333;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .card-header h2 i, .card-header h4 i {
            margin-right: 0.5rem;
            color: #f7444e;
        }

        .card-body {
            padding: 2rem;
        }

        .btn-back {
            background: #f7444e;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-back:hover {
            background: #ff6f61;
        }

        .product-image {
            border-radius: 10px;
            width: 100%;
            max-width: 300px;
            height: auto;
            object-fit: cover;
        }

        .product-info p {
            margin: 0.5rem 0;
            color: #666;
        }

        .product-info p strong {
            color: #333;
            display: inline-block;
            width: 150px;
        }

        .review-item {
            border-bottom: 1px solid #ddd;
            padding: 1rem 0;
        }

        .review-item:last-child {
            border-bottom: none;
        }

        .review-item p {
            margin: 0.25rem 0;
            color: #666;
        }

        .review-item p strong {
            color: #333;
        }

        .review-item .rating {
            color: #f1c40f;
        }

        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 0.5rem;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #f7444e;
            box-shadow: 0 0 5px rgba(247, 68, 78, 0.3);
        }

        .btn-submit {
            background: #f7444e;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-submit:hover {
            background: #ff6f61;
        }

        .related-product {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            background: #fff;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            text-align: center;
        }

        .related-product:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .related-product img {
            border-radius: 10px 10px 0 0;
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .related-product .card-body {
            padding: 1rem;
        }

        .related-product h6 {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .related-product p {
            color: #f7444e;
            font-weight: 600;
            margin-bottom: 0;
        }

        .no-data {
            text-align: center;
            color: #666;
            padding: 2rem;
        }

        /* Responsive */
        @media (max-width: 767px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .card-header h2, .card-header h4 {
                margin-bottom: 1rem;
            }

            .card-header .btn-back {
                margin-bottom: 1rem;
            }

            .product-image {
                max-width: 100%;
            }

            .product-info p {
                display: flex;
                flex-wrap: wrap;
            }

            .product-info p strong {
                width: 100%;
                margin-bottom: 0.25rem;
            }

            .related-product img {
                height: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Card: Thông tin sản phẩm -->
        <div class="card">
            <div class="card-header">
                <a href="../index.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Quay về</a>
                <h2><i class="fas fa-box-open"></i> Chi tiết sản phẩm</h2>
                <div></div> <!-- Placeholder để giữ layout -->
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img src="../image/<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                    </div>
                    <div class="col-md-8">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="product-info">
                            <p><strong>Giá:</strong> <span style="color: #f7444e; font-weight: 600;"><?= number_format($product['price'], 0, ',', '.') ?> VNĐ</span></p>
                            <p><strong>Danh mục:</strong> <?= htmlspecialchars($product['category_name']) ?></p>
                            <p><strong>Mô tả:</strong> <?= nl2br(htmlspecialchars($product['description'])) ?></p>
                            <p><strong>Đánh giá trung bình:</strong> <?= $avg ?>/5 <span class="rating"><?= str_repeat("⭐", round($avg)) ?></span></p>
                        </div>
                        <form action="../Cart/add_to_cart.php" method="POST" class="mt-3">
                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                            <button type="submit" class="btn btn-success"><i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Đánh giá người dùng -->
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-comments"></i> Đánh giá người dùng</h4>
            </div>
            <div class="card-body">
                <?php if ($reviews): ?>
                    <?php foreach ($reviews as $r): ?>
                        <div class="review-item">
                            <p><strong><?= htmlspecialchars($r['username']) ?>:</strong> <span class="rating"><?= str_repeat("⭐", $r['rating']) ?> (<?= $r['rating'] ?>/5)</span></p>
                            <p><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
                            <p><small class="text-muted">Đăng vào: <?= htmlspecialchars($r['created_at']) ?></small></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-data">
                        <p>Chưa có đánh giá nào.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card: Gửi đánh giá -->
        <?php if ($user_id): ?>
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-pen"></i> Gửi đánh giá của bạn</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="rating" class="form-label">Số sao:</label>
                            <select name="rating" id="rating" class="form-select" required>
                                <option value="">-- Chọn sao --</option>
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?= $i ?>"><?= $i ?> - <?= str_repeat("⭐", $i) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Nhận xét:</label>
                            <textarea name="comment" id="comment" rows="4" class="form-control" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-submit"><i class="fas fa-paper-plane"></i> Gửi đánh giá</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <p><em><i class="fas fa-lock"></i> Vui lòng <a href="../auth/login.php">đăng nhập</a> để gửi đánh giá.</em></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Card: Sản phẩm tương tự -->
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-sync-alt"></i> Sản phẩm tương tự</h4>
            </div>
            <div class="card-body">
                <?php
                $relatedStmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
                $relatedStmt->execute([$product['category_id'], $id]);
                $relatedProducts = $relatedStmt->fetchAll();
                ?>
                <?php if ($relatedProducts): ?>
                    <div class="row">
                        <?php foreach ($relatedProducts as $related): ?>
                            <div class="col-md-3 mb-4">
                                <div class="related-product">
                                    <a href="product_detail.php?id=<?= $related['id'] ?>">
                                        <img src="../image/<?= htmlspecialchars($related['image_url']) ?>" alt="<?= htmlspecialchars($related['name']) ?>">
                                        <div class="card-body">
                                            <h6><?= htmlspecialchars($related['name']) ?></h6>
                                            <p>₫ <?= number_format($related['price'], 0, ',', '.') ?> VNĐ</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p>Không có sản phẩm tương tự.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        // Hiển thị thông báo lỗi nếu có
        <?php if (isset($_SESSION['error'])): ?>
            Toastify({
                text: "<?= htmlspecialchars($_SESSION['error']) ?>",
                duration: 1500,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#dc3545',
            }).showToast();
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        // Hiển thị thông báo thành công nếu có
        <?php if (isset($_SESSION['success'])): ?>
            Toastify({
                text: "<?= htmlspecialchars($_SESSION['success']) ?>",
                duration: 1500,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#f7444e',
            }).showToast();
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
    </script>

<?php include '../includes/footer.php'; // Khôi phục footer.php ?>
</body>
</html>