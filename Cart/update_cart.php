<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập để cập nhật giỏ hàng.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'][$user_id] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $action = $_POST['action'] ?? '';

    if (!isset($cart[$id])) {
        echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không tồn tại trong giỏ hàng.']);
        exit();
    }

    if ($action === 'increase') {
        $cart[$id]['quantity']++;
    } elseif ($action === 'decrease') {
        $cart[$id]['quantity']--;
        if ($cart[$id]['quantity'] <= 0) {
            unset($cart[$id]); // Xóa sản phẩm nếu số lượng <= 0
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hành động không hợp lệ.']);
        exit();
    }

    // Cập nhật giỏ hàng trong session
    if (empty($cart)) {
        unset($_SESSION['cart'][$user_id]);
    } else {
        $_SESSION['cart'][$user_id] = $cart;
    }

    // Trả về dữ liệu JSON
    echo json_encode([
        'status' => 'success',
        'new_quantity' => isset($cart[$id]) ? $cart[$id]['quantity'] : 0,
        'message' => 'Cập nhật số lượng thành công.'
    ]);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Yêu cầu không hợp lệ.']);
exit();
?>