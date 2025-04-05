<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<?php include '../includes/header.php'; ?>

<section class="furniture_section layout_padding">
    <div class="container">
        <div class="heading_container">
            <h2>👋 Chào Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
            <p>Đây là trang quản trị dành riêng cho quản trị viên.</p>
        </div>

        <div class="row">
            <!-- Quản lý sản phẩm -->
            <div class="col-md-4">
                <div class="box">
                    <div class="detail-box">
                        <h5>📦 Quản Lý Sản Phẩm</h5>
                        <p>Thêm, sửa, xóa sản phẩm trong hệ thống.</p>
                        <a href="../Product/admin_products.php" class="btn btn-primary" style="padding: 5px 10px; background-color: #f7444e; border: none; color: white; border-radius: 5px;">Xem</a>
                    </div>
                </div>
            </div>

            <!-- Quản lý danh mục -->
            <div class="col-md-4">
                <div class="box">
                    <div class="detail-box">
                        <h5>📁 Quản Lý Danh Mục</h5>
                        <p>Quản lý các danh mục sản phẩm.</p>
                        <a href="../Categories/admin_categories.php" class="btn btn-primary" style="padding: 5px 10px; background-color: #f7444e; border: none; color: white; border-radius: 5px;">Xem</a>
                    </div>
                </div>
            </div>

            <!-- Quản lý đánh giá -->
            <div class="col-md-4">
                <div class="box">
                    <div class="detail-box">
                        <h5>📊 Quản Lý Đánh Giá</h5>
                        <p>Xem và quản lý đánh giá của khách hàng.</p>
                        <a href="admin_reviews.php" class="btn btn-primary" style="padding: 5px 10px; background-color: #f7444e; border: none; color: white; border-radius: 5px;">Xem</a>
                    </div>
                </div>
            </div>

            <!-- Quản lý đơn hàng -->
            <div class="col-md-4">
                <div class="box">
                    <div class="detail-box">
                        <h5>📋 Quản Lý Đơn Hàng</h5>
                        <p>Xác nhận, hủy đơn hàng và xem chi tiết.</p>
                        <a href="admin_orders.php" class="btn btn-primary" style="padding: 5px 10px; background-color: #f7444e; border: none; color: white; border-radius: 5px;">Xem</a>
                    </div>
                </div>
            </div>

            <!-- Thống kê doanh thu -->
            <div class="col-md-4">
                <div class="box">
                    <div class="detail-box">
                        <h5>📈 Thống Kê Doanh Thu</h5>
                        <p>Xem doanh thu, đơn hàng và sản phẩm bán chạy.</p>
                        <a href="admin_statistics.php" class="btn btn-primary" style="padding: 5px 10px; background-color: #f7444e; border: none; color: white; border-radius: 5px;">Xem</a>
                    </div>
                </div>
            </div>

            <!-- Xem sản phẩm -->
            <div class="col-md-4">
                <div class="box">
                    <div class="detail-box">
                        <h5>👁 Xem Sản Phẩm</h5>
                        <p>Xem danh sách sản phẩm (Admin + User).</p>
                        <a href="../index.php" class="btn btn-primary" style="padding: 5px 10px; background-color: #f7444e; border: none; color: white; border-radius: 5px;">Xem</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php include '../includes/footer.php'; ?>