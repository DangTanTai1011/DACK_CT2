<?php
session_start();
require '../config/db.php';

// Kiểm tra quyền truy cập: chỉ admin được vào trang này
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Bạn không có quyền truy cập trang này.';
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang quản trị</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- CSS tùy chỉnh -->
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Poppins', sans-serif;
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
            text-align: center;
        }

        .card-header h2 {
            color: #333;
            font-weight: 600;
            margin: 0;
        }

        .card-body {
            padding: 2rem;
        }

        .welcome-text {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 1.5rem;
        }

        .description-text {
            color: #666;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .box {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: left;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .box h5 {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
        }

        .box h5 i {
            margin-right: 0.5rem;
            color: #333;
        }

        .box p {
            color: #666;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: #f7444e;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
            width: 100px;
        }

        .btn-primary:hover {
            background: #ff6f61;
        }

        .btn-logout {
            background: #dc3545;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-logout:hover {
            background: #b02a37;
        }

        .row {
            row-gap: 1.5rem; /* Khoảng cách giữa các hàng */
        }

        .col-md-4 {
            margin-bottom: 1.5rem; /* Khoảng cách giữa các box trong cùng một hàng */
        }

        @media (max-width: 767px) {
            .col-md-4 {
                margin-bottom: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Trang quản trị</h2>
            </div>
            <div class="card-body">
                <h4 class="welcome-text">👋 Chào Admin: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></h4>
                <p class="description-text">Đây là trang quản trị dành riêng cho quản trị viên.</p>

                <div class="row">
                    <!-- Quản lý sản phẩm -->
                    <div class="col-md-4">
                        <div class="box">
                            <div class="detail-box">
                                <h5><i class="fas fa-box-open"></i> Quản Lý Sản Phẩm</h5>
                                <p>Thêm, sửa, xóa sản phẩm trong hệ thống.</p>
                            </div>
                            <a href="../Product/admin_products.php" class="btn btn-primary">Xem</a>
                        </div>
                    </div>

                    <!-- Quản lý danh mục -->
                    <div class="col-md-4">
                        <div class="box">
                            <div class="detail-box">
                                <h5><i class="fas fa-folder-open"></i> Quản Lý Danh Mục</h5>
                                <p>Quản lý các danh mục sản phẩm.</p>
                            </div>
                            <a href="../Categories/admin_categories.php" class="btn btn-primary">Xem</a>
                        </div>
                    </div>

                    <!-- Quản lý đánh giá -->
                    <div class="col-md-4">
                        <div class="box">
                            <div class="detail-box">
                                <h5><i class="fas fa-star"></i> Quản Lý Đánh Giá</h5>
                                <p>Xem và quản lý đánh giá của khách hàng.</p>
                            </div>
                            <a href="admin_reviews.php" class="btn btn-primary">Xem</a>
                        </div>
                    </div>

                    <!-- Quản lý đơn hàng -->
                    <div class="col-md-4">
                        <div class="box">
                            <div class="detail-box">
                                <h5><i class="fas fa-shopping-cart"></i> Quản Lý Đơn Hàng</h5>
                                <p>Xác nhận, hủy đơn hàng và xem chi tiết.</p>
                            </div>
                            <a href="admin_orders.php" class="btn btn-primary">Xem</a>
                        </div>
                    </div>

                    <!-- Thống kê doanh thu -->
                    <div class="col-md-4">
                        <div class="box">
                            <div class="detail-box">
                                <h5><i class="fas fa-chart-line"></i> Thống Kê Doanh Thu</h5>
                                <p>Xem doanh thu, đơn hàng và sản phẩm bán chạy.</p>
                            </div>
                            <a href="admin_statistics.php" class="btn btn-primary">Xem</a>
                        </div>
                    </div>

                    <!-- Quản lý tài khoản -->
                    <div class="col-md-4">
                        <div class="box">
                            <div class="detail-box">
                                <h5><i class="fas fa-users"></i> Quản Lý Tài Khoản</h5>
                                <p>Quản lý người dùng, phân quyền và xóa tài khoản.</p>
                            </div>
                            <a href="manage_users.php" class="btn btn-primary">Xem</a>
                        </div>
                    </div>

                    <!-- Xem sản phẩm -->
                    <div class="col-md-4">
                        <div class="box">
                            <div class="detail-box">
                                <h5><i class="fas fa-eye"></i> Xem Sản Phẩm</h5>
                                <p>Xem danh sách sản phẩm (Admin + User).</p>
                            </div>
                            <a href="../index.php" class="btn btn-primary">Xem</a>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="logout.php" class="btn btn-logout"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
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