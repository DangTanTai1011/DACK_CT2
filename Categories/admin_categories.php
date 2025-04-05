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
    $_SESSION['success'] = 'Thêm danh mục thành công!';
    header("Location: admin_categories.php");
    exit;
}

// Xoá danh mục
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['success'] = 'Xóa danh mục thành công!';
    } else {
        $_SESSION['error'] = 'Xóa danh mục thất bại.';
    }
    header("Location: admin_categories.php");
    exit;
}

// Lấy danh sách danh mục
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục</title>
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
            min-height: 100vh;
            margin: 0;
            padding: 2rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            background: #fff;
            border: none;
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
            background: #f7444e;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-back:hover {
            background: #ff6f61;
        }

        .form-add {
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .form-add input {
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 0.5rem;
            width: 300px;
        }

        .form-add input:focus {
            outline: none;
            border-color: #f7444e;
            box-shadow: 0 0 5px rgba(247, 68, 78, 0.3);
        }

        .btn-add {
            background: #f7444e;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-add:hover {
            background: #ff6f61;
        }

        .table {
            margin-bottom: 0;
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }

        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.9rem;
        }

        .btn-delete {
            background: #dc3545;
            border: none;
        }

        .btn-delete:hover {
            background: #b02a37;
        }

        /* Responsive */
        @media (max-width: 767px) {
            .table-responsive {
                overflow-x: auto;
            }

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

            .form-add {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-add input {
                width: 100%;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <a href="../auth/admin_dashboard.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Quay về</a>
                <h2><i class="fas fa-folder-open"></i> Quản lý danh mục</h2>
                <div></div> <!-- Placeholder để giữ layout -->
            </div>
            <div class="card-body">
                <!-- Form thêm danh mục -->
                <form method="POST" class="form-add">
                    <input type="text" name="name" placeholder="Nhập tên danh mục" required>
                    <button type="submit" name="add" class="btn btn-add"><i class="fas fa-plus"></i> Thêm</button>
                </form>

                <!-- Bảng danh sách danh mục -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên danh mục</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($categories): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cat['id']) ?></td>
                                        <td><?= htmlspecialchars($cat['name']) ?></td>
                                        <td>
                                            <a href="?delete=<?= $cat['id'] ?>" class="btn btn-delete btn-action text-white" onclick="return confirm('Bạn có chắc muốn xóa danh mục này?')">
                                                <i class="fas fa-trash"></i> Xóa
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">Không có danh mục nào.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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
</body>
</html>