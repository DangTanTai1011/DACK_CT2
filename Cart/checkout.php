<?php
session_start();
include '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Bạn cần đăng nhập để thanh toán.";
    header("Location: ../Auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'][$user_id] ?? [];
$total_price = 0;

// If the cart is empty, show a message
if (empty($cart)) {
    $_SESSION['message'] = "Giỏ hàng của bạn trống. Hãy thêm sản phẩm vào giỏ hàng.";
    header("Location: ../Cart/cart.php");
    exit();
}

// Calculate total price from the cart
foreach ($cart as $item) {
    if (isset($item['price'], $item['quantity'])) {
        $total_price += $item['price'] * $item['quantity'];
    } else {
        $_SESSION['message'] = "Dữ liệu sản phẩm trong giỏ hàng không hợp lệ!";
        header("Location: ../Cart/cart.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['customer_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $note = $_POST['note'] ?? '';

    // Validate input
    if (empty($customer_name) || empty($phone) || empty($address)) {
        $_SESSION['message'] = "Vui lòng điền đầy đủ thông tin thanh toán!";
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
    $_SESSION['message'] = "Đặt hàng thành công!";
    header("Location: checkout_success.php");
    exit();
}

// Include header.php after all header() calls
include '../includes/header.php';
?>

<section class="furniture_section layout_padding">
    <div class="container">
        <div class="heading_container">
            <h2>Thông Tin Thanh Toán</h2>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="box">
            <div class="detail-box">
                <form method="POST" style="max-width: 600px; margin: 0 auto;">
                    <div style="margin-bottom: 15px;">
                        <label for="customer_name" style="display: block; margin-bottom: 5px;">Họ và tên:</label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="Nhập tên của bạn" required style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label for="phone" style="display: block; margin-bottom: 5px;">Số điện thoại:</label>
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="Nhập số điện thoại" required style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label for="address" style="display: block; margin-bottom: 5px;">Địa chỉ giao hàng:</label>
                        <input type="text" id="address" name="address" class="form-control" placeholder="Nhập địa chỉ giao hàng" required style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label for="note" style="display: block; margin-bottom: 5px;">Ghi chú:</label>
                        <textarea id="note" name="note" class="form-control" placeholder="Ghi chú" style="width: 100%; height: 100px; padding: 8px; border-radius: 5px; border: 1px solid #ddd;"></textarea>
                    </div>
                    <div style="text-align: center;">
                        <button type="submit" class="btn btn-success" style="padding: 8px 15px; background-color: #f7444e; border: none; color: white; border-radius: 5px;">Xác Nhận Thanh Toán</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>