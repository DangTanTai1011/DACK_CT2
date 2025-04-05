<?php
session_start();

// Get product ID to remove
$id = $_GET['id'] ?? null;

if ($id && isset($_SESSION['cart'][$_SESSION['user_id']][$id])) {
    // Remove the item from the cart
    unset($_SESSION['cart'][$_SESSION['user_id']][$id]);
    header("Location: cart.php");
    exit();
} else {
    // If product not found in the cart, redirect with an error
    header("Location: cart.php?error=Sản phẩm không tồn tại trong giỏ hàng");
    exit();
}
