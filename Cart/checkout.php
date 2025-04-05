<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Bạn cần đăng nhập để thanh toán.";
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'][$user_id] ?? [];
$total_price = 0;

// If the cart is empty, show a message
if (empty($cart)) {
    $_SESSION['error'] = "Giỏ hàng của bạn trống. Hãy thêm sản phẩm vào giỏ hàng.";
    header("Location: ../Cart/cart.php");
    exit();
}

// Lấy thông tin sản phẩm từ cơ sở dữ liệu
$cart_items = [];
if (!empty($cart)) {
    $product_ids = array_keys($cart);
    if (!empty($product_ids)) {
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $stmt = $pdo->prepare("SELECT id, name, price, image_url FROM products WHERE id IN ($placeholders)");
        $stmt->execute($product_ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Kết hợp thông tin từ cơ sở dữ liệu với giỏ hàng
        foreach ($products as $product) {
            $cart_items[$product['id']] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'image_url' => $product['image_url'],
                'quantity' => $cart[$product['id']]['quantity']
            ];
            $total_price += $product['price'] * $cart[$product['id']]['quantity'];
        }
    }
}

// Xử lý form thanh toán
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['customer_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $note = $_POST['note'] ?? '';

    // Validate input
    if (empty($customer_name) || empty($phone) || empty($address)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin thanh toán!";
        header("Location: checkout.php");
        exit();
    }

    // Create order in orders table
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status, customer_name, phone, address, note) VALUES (?, ?, 'pending', ?, ?, ?, ?)");
    $stmt->execute([$user_id, $total_price, $customer_name, $phone, $address, $note]);
    $order_id = $pdo->lastInsertId(); // Get the last inserted order ID

    // Save order details for each item in the cart
    foreach ($cart as $id => $item) {
        $stmt = $pdo->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $id, $item['quantity'], $item['price']]);
    }

    // Clear the cart after successful order
    unset($_SESSION['cart']);

    // Redirect to checkout success page
    $_SESSION['success'] = "Đặt hàng thành công!";
    header("Location: checkout_success.php");
    exit();
}

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán</title>
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
            background: #6c757d;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-back:hover {
            background: #5a6268;
        }

        .table {
            margin-bottom: 2rem;
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }

        .product-image {
            border-radius: 5px;
            width: 60px;
            height: 60px;
            object-fit: cover;
        }

        .total-price {
            font-size: 1.5rem;
            font-weight: 600;
            color: #f7444e;
            margin-bottom: 1rem;
            text-align: right;
        }

        .form-label {
            font-weight: 500;
            color: #333;
        }

        .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 0.5rem;
        }

        .form-control:focus {
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

        /* Responsive */
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

            .table th, .table td {
                font-size: 0.9rem;
            }

            .product-image {
                width: 40px;
                height: 40px;
            }

            .total-price {
                font-size: 1.2rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <a href="../Cart/cart.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Quay về giỏ hàng</a>
                <h2><i class="fas fa-credit-card"></i> Thông Tin Thanh Toán</h2>
                <div></div> <!-- Placeholder để giữ layout -->
            </div>
            <div class="card-body">
                <!-- Hiển thị chi tiết giỏ hàng -->
                <h4 class="mb-3">Chi tiết đơn hàng</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Tổng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $id => $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                        ?>
                            <tr>
                                <td>
                                    <img src="../image/<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-image">
                                </td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($subtotal, 0, ',', '.'); ?> VNĐ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="total-price">
                    Tổng tiền: <?php echo number_format($total_price, 0, ',', '.'); ?> VNĐ
                </div>

                <!-- Form thanh toán -->
                <h4 class="mb-3">Thông tin giao hàng</h4>
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label for="customer_name" class="form-label">Họ và tên:</label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="Nhập tên của bạn" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Số điện thoại:</label>
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="Nhập số điện thoại" required>
                    </div>
                    <div class="col-12">
                        <label for="address" class="form-label">Địa chỉ giao hàng:</label>
                        <input type="text" id="address" name="address" class="form-control" placeholder="Nhập địa chỉ giao hàng" required>
                    </div>
                    <div class="col-12">
                        <label for="note" class="form-label">Ghi chú:</label>
                        <textarea id="note" name="note" class="form-control" placeholder="Ghi chú" rows="3"></textarea>
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-submit"><i class="fas fa-check"></i> Xác Nhận Thanh Toán</button>
                    </div>
                </form>
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

<?php include '../includes/footer.php'; ?>
</body>
</html>