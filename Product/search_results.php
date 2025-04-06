<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$rating = $_GET['rating'] ?? '';
$price_range = $_GET['price_range'] ?? '';

$min_price = '';
$max_price = '';
if ($price_range) {
    list($min_price, $max_price) = explode('-', $price_range);
}

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

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết Quả Tìm Kiếm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        .card-header h2 {
            color: #333;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .card-header h2 i {
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

        .product-card {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            background: #fff;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .product-card img {
            border-radius: 10px 10px 0 0;
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-card .card-body {
            padding: 1.5rem;
            text-align: center;
        }

        .product-card h5 {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .product-card p {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .product-card .price {
            color: #f7444e;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .product-card .btn-details {
            background: #007bff;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .product-card .btn-details:hover {
            background: #0056b3;
        }

        .product-card .btn-add-to-cart {
            background: #28a745;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .product-card .btn-add-to-cart:hover {
            background: #218838;
        }

        .no-products {
            text-align: center;
            color: #666;
            padding: 2rem;
        }

        @media (max-width: 767px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .card-header h2 {
                margin-bottom: 1rem;
            }

            .card-header .btn-back {
                margin-bottom: 1rem;
            }

            .product-card img {
                height: 150px;
            }

            .product-card .btn-details,
            .product-card .btn-add-to-cart {
                display: block;
                width: 100%;
                margin: 0.25rem 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <a href="../index.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Quay về</a>
                <h2><i class="fas fa-search"></i> Kết Quả Tìm Kiếm</h2>
                <div></div>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $p): ?>
                            <div class="col-md-4 mb-4">
                                <div class="product-card">
                                    <img src="../image/<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                                    <div class="card-body">
                                        <h5><?= htmlspecialchars($p['name']) ?></h5>
                                        <p><strong>Danh mục:</strong> <?= htmlspecialchars($p['category_name']) ?></p>
                                        <p class="price">₫ <?= number_format($p['price'], 0, ',', '.') ?> VNĐ</p>
                                        <p><strong>Đánh giá:</strong> <?= number_format($p['avg_rating'], 1) ?> / 5</p>
                                        <a href="../product/product_detail.php?id=<?= $p['id'] ?>" class="btn btn-details">
                                            <i class="fas fa-eye"></i> Xem Chi Tiết
                                        </a>
                                        <form action="../Cart/add_to_cart.php" method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-add-to-cart">
                                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-products">
                            <p>Không có sản phẩm nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        <?php if ($message): ?>
            Toastify({
                text: "<?= htmlspecialchars($message) ?>",
                duration: 1500,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#f7444e',
            }).showToast();
        <?php endif; ?>
    </script>

<?php include '../includes/footer.php'; ?>
</body>
</html>