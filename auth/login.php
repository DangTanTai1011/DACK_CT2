<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $passwordInput = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($passwordInput, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];  // ✅ Lưu user_id vào session
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // ✅ Điều hướng theo quyền
        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit();
    } else {
        echo "Sai tài khoản hoặc mật khẩu.";
    }
}
?>

<form method="post">
    <h2>Đăng nhập</h2>
    Username: <input type="text" name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Đăng nhập</button>
</form>
