<?php
include '../config/db.php';
session_start();

// Kiểm tra đăng nhập & phân quyền admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Truy vấn danh sách sản phẩm + tên danh mục
$stmt = $pdo->query("SELECT products.*, categories.name AS category_name 
                     FROM products 
                     LEFT JOIN categories ON products.category_id = categories.id");
$products = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2>📦 Quản lý sản phẩm</h2>
<a href="add_product.php">➕ Thêm sản phẩm</a>
<br><br>

<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Tên sản phẩm</th>
        <th>Giá</th>
        <th>Danh mục</th>
        <th>Ảnh</th>
        <th>Hành động</th>
    </tr>
    <?php foreach ($products as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= number_format($p['price'], 0, ',', '.') ?> VNĐ</td>
            <td><?= htmlspecialchars($p['category_name']) ?></td>
            <td><img src="../image/<?= htmlspecialchars($p['image_url']) ?>" width="60"></td>
            <td>
                <a href="edit_product.php?id=<?= $p['id'] ?>">Sửa</a> |
                <a href="delete_product.php?id=<?= $p['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php include '../includes/footer.php'; ?>
