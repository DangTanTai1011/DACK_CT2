<?php
session_start();

$id = $_GET['id'] ?? null;

if ($id && isset($_SESSION['cart'][$_SESSION['user_id']][$id])) {
    unset($_SESSION['cart'][$_SESSION['user_id']][$id]);
    header("Location: cart.php");
    exit();
} else {
    header("Location: cart.php?error=Sản phẩm không tồn tại trong giỏ hàng");
    exit();
}
