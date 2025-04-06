<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Bạn cần đăng nhập với tài khoản admin để truy cập trang này.";
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $review_id = $_POST['review_id'] ?? '';

    if ($review_id) {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        $_SESSION['success'] = "Đánh giá đã được xóa thành công!";
    } else {
        $_SESSION['error'] = "Không tìm thấy đánh giá để xóa.";
    }
    header("Location: admin_reviews.php");
    exit();
}

$stmt = $pdo->query("SELECT r.*, u.username, p.name AS product_name 
                     FROM reviews r 
                     LEFT JOIN users u ON r.user_id = u.id 
                     LEFT JOIN products p ON r.product_id = p.id 
                     ORDER BY r.created_at DESC");
$reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đánh giá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        .no-data {
            text-align: center;
            color: #666;
            padding: 2rem;
        }

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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <a href="../auth/admin_dashboard.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Quay về</a>
                <h2><i class="fas fa-star"></i> Quản lý đánh giá</h2>
                <div></div>
            </div>
            <div class="card-body">
                <?php if (empty($reviews)): ?>
                    <div class="no-data">
                        <p>Chưa có đánh giá nào.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Khách Hàng</th>
                                    <th>Sản Phẩm</th>
                                    <th>Nội Dung</th>
                                    <th>Ngày Tạo</th>
                                    <th>Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reviews as $review): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($review['id']) ?></td>
                                        <td><?= htmlspecialchars($review['username'] ?? 'Khách vãng lai') ?></td>
                                        <td><?= htmlspecialchars($review['product_name'] ?? 'Không xác định') ?></td>
                                        <td><?= htmlspecialchars($review['comment']) ?></td>
                                        <td><?= htmlspecialchars($review['created_at']) ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                <button type="submit" class="btn btn-delete btn-action text-white">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
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
</body>
</html>