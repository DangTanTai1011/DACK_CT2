<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Bạn cần đăng nhập để xem giỏ hàng.";
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'][$user_id] ?? [];
$total_price = 0;

$cart_items = [];
if (!empty($cart)) {
    $product_ids = array_keys($cart);
    if (!empty($product_ids)) {
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $stmt = $pdo->prepare("SELECT id, name, price, image_url FROM products WHERE id IN ($placeholders)");
        $stmt->execute($product_ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $product) {
            $cart_items[$product['id']] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'image_url' => $product['image_url'],
                'quantity' => $cart[$product['id']]['quantity']
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
        }

        .container {
            max-width: 1200px;
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

        .btn-continue-shopping {
            background: #6c757d;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-continue-shopping:hover {
            background: #5a6268;
        }

        .table {
            margin-bottom: 2rem;
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }

        .product-image {
            border-radius: 5px;
            width: 60px;
            height: 60px;
            object-fit: cover;
        }

        .btn-decrease, .btn-increase {
            background: #007bff;
            border: none;
            border-radius: 5px;
            padding: 0.25rem 0.5rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-decrease:hover, .btn-increase:hover {
            background: #0056b3;
        }

        .btn-remove {
            background: #dc3545;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-remove:hover {
            background: #c82333;
        }

        .btn-checkout {
            background: #f7444e;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-checkout:hover {
            background: #ff6f61;
        }

        .total-price {
            font-size: 1.5rem;
            font-weight: 600;
            color: #f7444e;
            margin-bottom: 1rem;
            text-align: right;
        }

        .empty-cart {
            text-align: center;
            color: #666;
            padding: 2rem;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-control button {
            min-width: 32px;
            height: 32px;
            padding: 0;
        }

        .quantity-control span {
            min-width: 24px;
            text-align: center;
        }

        @media (max-width: 767px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .card-header h2 {
                margin-bottom: 1rem;
            }

            .card-header .btn-continue-shopping {
                margin-bottom: 1rem;
            }

            .table th, .table td {
                font-size: 0.9rem;
            }

            .product-image {
                width: 40px;
                height: 40px;
            }

            .btn-decrease, .btn-increase {
                padding: 0.2rem 0.4rem;
            }

            .btn-remove, .btn-checkout {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }

            .total-price {
                font-size: 1.2rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-shopping-cart"></i> Giỏ Hàng</h2>
                <a href="../index.php" class="btn btn-continue-shopping"><i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm</a>
            </div>
            <div class="card-body">
                <?php if (empty($cart_items)): ?>
                    <div class="empty-cart">
                        <p>Giỏ hàng của bạn đang trống.</p>
                    </div>
                <?php else: ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Ảnh</th>
                                <th>Sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Tổng</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $id => $item): 
                                $subtotal = $item['price'] * $item['quantity'];
                                $total_price += $subtotal;
                            ?>
                                <tr data-id="<?php echo $id; ?>">
                                    <td>
                                        <img src="../image/<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-image">
                                    </td>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td class="price"><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</td>
                                    <td>
                                        <div class="quantity-control d-inline-flex align-items-center justify-content-center">
                                            <button class="btn btn-decrease update-qty me-2" data-id="<?php echo $id; ?>" data-action="decrease"><i class="fas fa-minus"></i></button>
                                            <span id="qty-<?php echo $id; ?>" class="fw-bold"><?php echo $item['quantity']; ?></span>
                                            <button class="btn btn-increase update-qty ms-2" data-id="<?php echo $id; ?>" data-action="increase"><i class="fas fa-plus"></i></button>
                                        </div>
                                    </td>
                                    <td class="subtotal"><?php echo number_format($subtotal, 0, ',', '.'); ?> VNĐ</td>
                                    <td>
                                        <a href="remove_from_cart.php?id=<?php echo $id; ?>" class="btn btn-remove"><i class="fas fa-trash-alt"></i> Xóa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="total-price">
                        Tổng tiền: <span id="total-price"><?php echo number_format($total_price, 0, ',', '.'); ?></span> VNĐ
                    </div>
                    <div class="text-end">
                        <a href="checkout.php" class="btn btn-checkout"><i class="fas fa-credit-card"></i> Thanh toán</a>
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

        <?php if (isset($_SESSION['cart_success'])): ?>
            Toastify({
                text: "<?= htmlspecialchars($_SESSION['cart_success']) ?>",
                duration: 1000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#f7444e',
            }).showToast();
            <?php unset($_SESSION['cart_success']); ?>
        <?php endif; ?>

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
                        const qtyElement = document.getElementById(`qty-${id}`);
                        qtyElement.textContent = data.new_quantity;

                        if (data.new_quantity === 0) {
                            const row = document.querySelector(`tr[data-id="${id}"]`);
                            row.remove();
                        }

                        const row = document.querySelector(`tr[data-id="${id}"]`);
                        if (row) {
                            const priceText = row.querySelector('.price').textContent.replace(/[^0-9]/g, '');
                            const price = parseInt(priceText);
                            const newSubtotal = price * data.new_quantity;
                            row.querySelector('.subtotal').textContent = newSubtotal.toLocaleString('vi-VN') + ' VNĐ';
                        }

                        let totalPrice = 0;
                        document.querySelectorAll('tbody tr').forEach(row => {
                            const subtotalText = row.querySelector('.subtotal').textContent.replace(/[^0-9]/g, '');
                            totalPrice += parseInt(subtotalText) || 0;
                        });
                        document.getElementById('total-price').textContent = totalPrice.toLocaleString('vi-VN');

                        if (document.querySelectorAll('tbody tr').length === 0) {
                            window.location.reload();
                        }
                    } else {
                        Toastify({
                            text: data.message,
                            duration: 1500,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: '#dc3545',
                        }).showToast();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Toastify({
                        text: 'Đã có lỗi xảy ra. Vui lòng thử lại.',
                        duration: 1500,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: '#dc3545',
                    }).showToast();
                });
            });
        });
    </script>

<?php include '../includes/footer.php'; ?>
</body>
</html>