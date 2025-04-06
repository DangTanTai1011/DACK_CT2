<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    $_SESSION['error'] = 'Bạn không có quyền truy cập trang này.';
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    $_SESSION['error'] = 'Sản phẩm không tồn tại.';
    header("Location: admin_products.php");
    exit;
}

$product = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$product->execute([$id]);
$product = $product->fetch();

if (!$product) {
    $_SESSION['error'] = 'Sản phẩm không tồn tại.';
    header("Location: admin_products.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $cat_id = $_POST['category_id'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận JPEG, PNG hoặc GIF.';
            header("Location: edit_product.php?id=" . $id);
            exit;
        }

        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $target_path = "../image/" . $image_name;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $_SESSION['error'] = 'Tải ảnh lên thất bại. Vui lòng thử lại.';
            header("Location: edit_product.php?id=" . $id);
            exit;
        }
    } else {
        $image_name = $product['image_url'];
    }

    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, image_url=?, category_id=? WHERE id=?");
    if ($stmt->execute([$name, $desc, $price, $image_name, $cat_id, $id])) {
        $_SESSION['success'] = 'Cập nhật sản phẩm thành công!';
    } else {
        $_SESSION['error'] = 'Cập nhật sản phẩm thất bại. Vui lòng thử lại.';
    }

    header("Location: admin_products.php");
    exit;
}
?>

<?php include '../includes/header.php'; ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 0;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            background: #fff;
            border: none;
            margin-bottom: 2rem;
        }

        .card-header {
            background: #fff;
            border-bottom: none;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            color: #333;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .card-header h2 i {
            margin-right: 0.5rem;
            color: #f7444e;
        }

        .card-body {
            padding: 2rem;
        }

        .btn-back {
            background: #6c757d;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-back:hover {
            background: #5a6268;
        }

        .form-label {
            font-weight: 500;
            color: #333;
        }

        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 0.5rem;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #f7444e;
            box-shadow: 0 0 5px rgba(247, 68, 78, 0.3);
        }

        .current-image {
            border-radius: 10px;
            width: 100%;
            max-width: 150px;
            height: auto;
            object-fit: cover;
            margin-bottom: 1rem;
        }

        .btn-submit {
            background: #f7444e;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-submit:hover {
            background: #ff6f61;
        }

        @media (max-width: 767px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .card-header h2 {
                margin-bottom: 1rem;
            }

            .card-header .btn-back {
                margin-bottom: 1rem;
            }

            .current-image {
                max-width: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <a href="admin_products.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Quay về</a>
                <h2><i class="fas fa-edit"></i> Chỉnh sửa sản phẩm</h2>
                <div></div>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên sản phẩm:</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả:</label>
                        <textarea name="description" id="description" rows="4" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Giá (VNĐ):</label>
                        <input type="number" step="0.01" name="price" id="price" class="form-control" value="<?= htmlspecialchars($product['price']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ảnh hiện tại:</label><br>
                        <img src="../image/<?= htmlspecialchars($product['image_url']) ?>" alt="Current Image" class="current-image">
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Tải ảnh mới (nếu muốn thay):</label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Danh mục:</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-submit"><i class="fas fa-save"></i> Cập nhật</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        <?php if (isset($_SESSION['error'])): ?>
            Toastify({
                text: "<?= htmlspecialchars($_SESSION['error']) ?>",
                duration: 1500,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#dc3545',
            }).showToast();
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            Toastify({
                text: "<?= htmlspecialchars($_SESSION['success']) ?>",
                duration: 1500,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#f7444e',
            }).showToast();
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
    </script>

<?php include '../includes/footer.php'; ?>
</body>
</html>