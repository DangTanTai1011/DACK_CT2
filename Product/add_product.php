<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $cat_id = $_POST['category_id'];

    // Xử lý upload file ảnh vào thư mục 'image'
    $image_name = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $target_path = "../image/" . basename($image_name); // Sửa ở đây
    move_uploaded_file($image_tmp, $target_path);

    // Lưu tên file ảnh vào DB
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image_url, category_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $desc, $price, $image_name, $cat_id]);

    header("Location: admin_products.php");
    exit;
}
?>

<?php include '../includes/header.php'; ?>
<h2>➕ Thêm sản phẩm</h2>
<form method="POST" enctype="multipart/form-data">
    Tên sản phẩm: <input type="text" name="name" required><br><br>
    Mô tả: <textarea name="description" required></textarea><br><br>
    Giá: <input type="number" step="0.01" name="price" required><br><br>
    Ảnh sản phẩm: <input type="file" name="image" accept="image/*" required><br><br>
    Danh mục:
    <select name="category_id">
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
        <?php endforeach; ?>
    </select><br><br>
    <button type="submit">Lưu</button>
</form>
<?php include '../includes/footer.php'; ?>
