<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Bạn cần đăng nhập để xem giỏ hàng.";
    header("Location: ../Auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'][$user_id] ?? [];
$total_price = 0;
?>

<section class="furniture_section layout_padding">
    <div class="container">
        <div class="heading_container">
            <h2>Giỏ Hàng</h2>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <?php if (empty($cart)): ?>
            <div class="box">
                <div class="detail-box">
                    <p>Giỏ hàng của bạn đang trống.</p>
                    <a href="<?php echo BASE_URL; ?>/furniture.php" class="btn btn-primary" style="background-color: #f7444e; border: none;">Tiếp tục mua sắm</a>
                </div>
            </div>
        <?php else: ?>
            <div class="box">
                <div class="detail-box">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Tổng</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart as $id => $item): 
                                $subtotal = $item['price'] * $item['quantity'];
                                $total_price += $subtotal;
                            ?>
                                <tr data-id="<?php echo $id; ?>">
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td class="price"><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary update-qty" data-id="<?php echo $id; ?>" data-action="decrease">-</button>
                                        <span id="qty-<?php echo $id; ?>"><?php echo $item['quantity']; ?></span>
                                        <button class="btn btn-sm btn-outline-primary update-qty" data-id="<?php echo $id; ?>" data-action="increase">+</button>
                                    </td>
                                    <td class="subtotal"><?php echo number_format($subtotal, 0, ',', '.'); ?> VNĐ</td>
                                    <td>
                                        <a href="remove_from_cart.php?id=<?php echo $id; ?>" class="btn btn-sm btn-danger">Xóa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <h4>Tổng tiền: <span id="total-price"><?php echo number_format($total_price, 0, ',', '.'); ?></span> VNĐ</h4>
                    <a href="checkout.php" class="btn btn-success" style="background-color: #f7444e; border: none;">Thanh toán</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.querySelectorAll('.update-qty').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const action = this.getAttribute('data-action');

        fetch('update_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Cập nhật số lượng
                const qtyElement = document.getElementById(`qty-${id}`);
                qtyElement.textContent = data.new_quantity;

                // Nếu số lượng = 0, xóa hàng khỏi bảng
                if (data.new_quantity === 0) {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    row.remove();
                }

                // Cập nhật tổng tiền của hàng
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    const priceText = row.querySelector('.price').textContent.replace(/[^0-9]/g, '');
                    const price = parseInt(priceText);
                    const newSubtotal = price * data.new_quantity;
                    row.querySelector('.subtotal').textContent = newSubtotal.toLocaleString('vi-VN') + ' VNĐ';
                }

                // Cập nhật tổng tiền toàn bộ giỏ hàng
                let totalPrice = 0;
                document.querySelectorAll('tbody tr').forEach(row => {
                    const subtotalText = row.querySelector('.subtotal').textContent.replace(/[^0-9]/g, '');
                    totalPrice += parseInt(subtotalText) || 0;
                });
                document.getElementById('total-price').textContent = totalPrice.toLocaleString('vi-VN');

                // Nếu giỏ hàng trống, tải lại trang
                if (document.querySelectorAll('tbody tr').length === 0) {
                    window.location.reload();
                }
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã có lỗi xảy ra. Vui lòng thử lại.');
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>