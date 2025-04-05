<?php
session_start();
include '../config/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Bạn cần đăng nhập để thêm vào giỏ hàng.';
    header('Location: ../index.php'); // Quay lại trang sản phẩm
    exit();
}

// Kiểm tra ID sản phẩm
$id = $_POST['id'] ?? null;
if (!$id) {
    $_SESSION['message'] = 'Sản phẩm không hợp lệ.';
    header('Location: ../index.php');
    exit();
}

// Lấy thông tin sản phẩm từ cơ sở dữ liệu
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['message'] = 'Sản phẩm không tồn tại.';
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'][$user_id])) {
    $_SESSION['cart'][$user_id] = [];
}

// Thêm sản phẩm vào giỏ hàng
if (!isset($_SESSION['cart'][$user_id][$id])) {
    $_SESSION['cart'][$user_id][$id] = [
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => 1
    ];
} else {
    $_SESSION['cart'][$user_id][$id]['quantity']++;
}

$_SESSION['message'] = 'Thêm vào giỏ hàng thành công!';
header('Location: ../index.php'); // Quay lại trang sản phẩm
exit();
?>
