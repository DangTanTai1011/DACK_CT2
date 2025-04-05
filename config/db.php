<?php
$host = 'localhost';
$db = 'Nhom8_DACK'; // Đã đổi tên
$user = 'root';
$pass = ''; // Mặc định XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối: " . $e->getMessage());
}
?>
