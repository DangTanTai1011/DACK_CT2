<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'];
$product = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$product->execute([$id]);
$product = $product->fetch();

// Lấy danh mục
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $cat_id = $_POST['category_id'];

    // Kiểm tra nếu người dùng upload ảnh mới
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $target_path = "../image/" . basename($image_name);
        move_uploaded_file($image_tmp, $target_path);
    } else {
        // Giữ nguyên ảnh cũ nếu không upload mới
        $image_name = $product['image_url'];
    }

    // Cập nhật sản phẩm
    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, image_url=?, category_id=? WHERE id=?");
    $stmt->execute([$name, $desc, $price, $image_name, $cat_id, $id]);

    header("Location: admin_products.php");
    exit;
}
?>

<?php include '../includes/header.php'; ?>
<h2>✏️ Chỉnh sửa sản phẩm</h2>

<form method="POST" enctype="multipart/form-data">
    Tên sản phẩm: <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required><br><br>
    Mô tả: <textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea><br><br>
    Giá: <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required><br><br>

    Ảnh hiện tại: <br>
    <img src="../files/<?= htmlspecialchars($product['image_url']) ?>" width="120"><br><br>

    Tải ảnh mới (nếu muốn thay): <input type="file" name="image" accept="image/*"><br><br>

    Danh mục:
    <select name="category_id">
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                <?= $cat['name'] ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Cập nhật</button>
</form>
<?php include '../includes/footer.php'; ?>
