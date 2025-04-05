<?php
// Bắt đầu session (nếu chưa bắt đầu)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Định nghĩa BASE_URL nếu chưa có
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Nhom8_DACK');
}
?>

<!DOCTYPE html>
<html>
<head>
    <!-- Basic -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <!-- Site Metas -->
    <link rel="icon" href="<?php echo BASE_URL; ?>/public/images/fevicon.png" type="image/gif" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta name="author" content="" />

    <title>Edgecut</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/public/css/bootstrap.css" />
    <!-- Fonts style -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,600,700&display=swap" rel="stylesheet" />
    <!-- Font Awesome style -->
    <link href="<?php echo BASE_URL; ?>/public/css/font-awesome.min.css" rel="stylesheet" />
    <!-- Custom styles for this template -->
    <link href="<?php echo BASE_URL; ?>/public/css/style.css" rel="stylesheet" />
    <!-- Responsive style -->
    <link href="<?php echo BASE_URL; ?>/public/css/responsive.css" rel="stylesheet" />
    <!-- CSS tùy chỉnh -->
    <style>
        .admin-icon {
            margin-right: 10px;
            color: #f7444e; /* Màu đỏ đồng bộ với giao diện */
            text-decoration: none;
            font-size: 18px;
        }
        .admin-icon:hover {
            color: #ff6f61; /* Màu hover đồng bộ với các nút khác */
        }
        .admin-icon span {
            margin-left: 5px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <!-- Header section starts -->
    <header class="header_section long_section px-0">
        <nav class="navbar navbar-expand-lg custom_nav-container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/index.php">
                <span>Edgecut</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class=""> </span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <div class="d-flex mx-auto flex-column flex-lg-row align-items-center">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php">Home <span class="sr-only">(current)</span></a>
                        </li>
                        <!-- Liên kết Lịch Sử Mua Hàng (đã thêm trước đó) -->
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/Cart/order_history.php">Lịch Sử Mua Hàng</a>
                            </li>
                            <!-- Thêm liên kết Hồ Sơ Cá Nhân -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/Auth/profile.php">Hồ Sơ Cá Nhân</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="quote_btn-container">
                    <?php if (isset($_SESSION['username'])): ?>
                        <!-- Hiển thị tên tài khoản và nút đăng xuất nếu đã đăng nhập -->
                        <span style="color: black; margin-right: 10px;">
                            Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </span>
                        <!-- Thêm icon admin nếu tài khoản là admin -->
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                            <a href="<?php echo BASE_URL; ?>/auth/admin_dashboard.php" class="admin-icon" title="Quản lý Admin">
                                <i class="fa fa-user-shield" aria-hidden="true"></i>
                                <span>Admin</span>
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>/Auth/logout.php" style="margin-right: 10px;">
                            <span>Đăng xuất</span>
                            <i class="fa fa-sign-out" aria-hidden="true"></i>
                        </a>
                    <?php else: ?>
                        <!-- Hiển thị liên kết Login nếu chưa đăng nhập -->
                        <a href="<?php echo BASE_URL; ?>/Auth/login.php" style="margin-right: 10px;">
                            <span>Login</span>
                            <i class="fa fa-user" aria-hidden="true"></i>
                        </a>
                    <?php endif; ?>
                    <!-- Thêm biểu tượng giỏ hàng -->
                    <a href="<?php echo BASE_URL; ?>/Cart/cart.php" style="margin-right: 10px; color: black; text-decoration: none;">
                        <i class="fa fa-shopping-cart" aria-hidden="true" style="font-size: 18px;"></i>
                        <span style="margin-left: 5px;">Giỏ Hàng</span>
                    </a>
                    <!-- Nút tìm kiếm -->
                    <form class="form-inline">
                        <button class="btn my-2 my-sm-0 nav_search-btn" type="submit">
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </div>
        </nav>
    </header>
    <!-- End header section -->