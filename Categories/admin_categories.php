<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Thêm danh mục
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute([$name]);
    header("Location: admin_categories.php");
    exit;
}

// Xoá danh mục
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin_categories.php");
    exit;
}

// Lấy danh sách danh mục
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<h2>📁 Quản lý danh mục</h2>

<form method="POST">
    Thêm danh mục: <input type="text" name="name" required>
    <button type="submit" name="add">➕ Thêm</button>
</form>

<br>
<table border="1" cellpadding="10">
    <tr><th>ID</th><th>Tên danh mục</th><th>Hành động</th></tr>
    <?php foreach ($categories as $cat): ?>
        <tr>
            <td><?= $cat['id'] ?></td>
            <td><?= $cat['name'] ?></td>
            <td><a href="?delete=<?= $cat['id'] ?>" onclick="return confirm('Xóa danh mục?')">Xoá</a></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php include '../includes/footer.php'; ?>
