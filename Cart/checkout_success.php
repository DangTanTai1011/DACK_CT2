<?php
session_start();
include '../includes/header.php';
?>

<section class="furniture_section layout_padding">
    <div class="container">
        <div class="heading_container">
            <h2>Đặt Hàng Thành Công</h2>
        </div>
        <div class="box">
            <div class="detail-box">
                <p>Cảm ơn bạn đã đặt hàng! Đơn hàng của bạn đã được ghi nhận.</p>
                <a href="../index.php" class="btn btn-primary" style="padding: 8px 15px; background-color: #f7444e; border: none; color: white; border-radius: 5px;">Tiếp Tục Mua Sắm</a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>