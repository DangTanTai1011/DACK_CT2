<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    $_SESSION['error'] = 'Bạn không có quyền truy cập trang này.';
    header("Location: ../auth/login.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $cat_id = $_POST['category_id'];

    // Kiểm tra nếu người dùng upload ảnh
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận JPEG, PNG hoặc GIF.';
            header("Location: add_product.php");
            exit;
        }

        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $target_path = "../image/" . $image_name;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $_SESSION['error'] = 'Tải ảnh lên thất bại. Vui lòng thử lại.';
            header("Location: add_product.php");
            exit;
        }
    } else {
        $_SESSION['error'] = 'Vui lòng chọn ảnh sản phẩm.';
        header("Location: add_product.php");
        exit;
    }

    // Lưu sản phẩm vào cơ sở dữ liệu
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image_url, category_id) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $desc, $price, $image_name, $cat_id])) {
        $_SESSION['success'] = 'Thêm sản phẩm thành công!';
    } else {
        $_SESSION['error'] = 'Thêm sản phẩm thất bại. Vui lòng thử lại.';
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
    <title>Thêm sản phẩm</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- CSS tùy chỉnh -->
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

        /* Responsive */
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <a href="admin_products.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Quay về</a>
                <h2><i class="fas fa-plus-circle"></i> Thêm sản phẩm</h2>
                <div></div> <!-- Placeholder để giữ layout -->
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên sản phẩm:</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả:</label>
                        <textarea name="description" id="description" rows="4" class="form-control" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Giá (VNĐ):</label>
                        <input type="number" step="0.01" name="price" id="price" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Ảnh sản phẩm:</label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*" required>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Danh mục:</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-submit"><i class="fas fa-save"></i> Lưu</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        // Hiển thị thông báo lỗi nếu có
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

        // Hiển thị thông báo thành công nếu có
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