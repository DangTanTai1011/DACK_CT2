<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Bạn cần đăng nhập để xem chi tiết đơn hàng.";
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'] ?? '';

$stmt = $pdo->prepare("SELECT o.*, u.username 
                       FROM orders o 
                       LEFT JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = "Đơn hàng không tồn tại.";
    header("Location: admin_orders.php");
    exit();
}

$stmt = $pdo->prepare("SELECT od.*, p.name AS product_name 
                       FROM order_details od 
                       JOIN products p ON od.product_id = p.id 
                       WHERE od.order_id = ?");
$stmt->execute([$order_id]);
$order_details = $stmt->fetchAll();

$user_id = $order['user_id'];
$stmt = $pdo->prepare("SELECT o.*, u.username 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.user_id = ? AND o.id != ? 
                       ORDER BY o.created_at DESC");
$stmt->execute([$user_id, $order_id]);
$order_history = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?php echo $order['id']; ?></title>
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

        .card-header h4 {
            color: #333;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .card-header h4 i {
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

        .order-info p {
            margin: 0.5rem 0;
            color: #666;
        }

        .order-info p strong {
            color: #333;
            display: inline-block;
            width: 150px;
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

        .btn-details {
            background: #007bff;
            border: none;
        }

        .btn-details:hover {
            background: #0056b3;
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

            .card-header h2, .card-header h4 {
                margin-bottom: 1rem;
            }

            .card-header .btn-back {
                margin-bottom: 1rem;
            }

            .order-info p {
                display: flex;
                flex-wrap: wrap;
            }

            .order-info p strong {
                width: 100%;
                margin-bottom: 0.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <a href="../index.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Quay về</a>
                <h2><i class="fas fa-shopping-cart"></i> Chi tiết đơn hàng #<?php echo $order['id']; ?></h2>
                <div></div>
            </div>
            <div class="card-body">
                <div class="order-info">
                    <p><strong>Khách Hàng:</strong> <?= htmlspecialchars($order['username'] ?? 'Khách vãng lai') ?></p>
                    <p><strong>Tổng Tiền:</strong> <?= number_format($order['total'], 0, ',', '.') ?> VNĐ</p>
                    <p><strong>Trạng Thái:</strong> 
                        <span class="badge <?= $order['status'] === 'pending' ? 'bg-warning' : ($order['status'] === 'confirmed' ? 'bg-success' : 'bg-danger') ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </p>
                    <p><strong>Ngày Tạo:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
                    <p><strong>Họ và Tên:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                    <p><strong>Số Điện Thoại:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                    <p><strong>Địa Chỉ:</strong> <?= htmlspecialchars($order['address']) ?></p>
                    <p><strong>Ghi Chú:</strong> <?= htmlspecialchars($order['note'] ?? 'Không có ghi chú') ?></p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-box-open"></i> Danh sách sản phẩm</h4>
            </div>
            <div class="card-body">
                <?php if (empty($order_details)): ?>
                    <div class="no-data">
                        <p>Không có sản phẩm nào trong đơn hàng này.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Sản Phẩm</th>
                                    <th>Số Lượng</th>
                                    <th>Giá</th>
                                    <th>Tổng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_details as $detail): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($detail['product_name']) ?></td>
                                        <td><?= htmlspecialchars($detail['quantity']) ?></td>
                                        <td><?= number_format($detail['price'], 0, ',', '.') ?> VNĐ</td>
                                        <td><?= number_format($detail['price'] * $detail['quantity'], 0, ',', '.') ?> VNĐ</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-history"></i> Lịch sử mua hàng của khách hàng</h4>
            </div>
            <div class="card-body">
                <?php if (empty($order_history)): ?>
                    <div class="no-data">
                        <p>Khách hàng này chưa có đơn hàng nào khác.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID Đơn Hàng</th>
                                    <th>Tổng Tiền</th>
                                    <th>Trạng Thái</th>
                                    <th>Ngày Tạo</th>
                                    <th>Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_history as $history): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($history['id']) ?></td>
                                        <td><?= number_format($history['total'], 0, ',', '.') ?> VNĐ</td>
                                        <td>
                                            <span class="badge <?= $history['status'] === 'pending' ? 'bg-warning' : ($history['status'] === 'confirmed' ? 'bg-success' : 'bg-danger') ?>">
                                                <?= htmlspecialchars($history['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($history['created_at']) ?></td>
                                        <td>
                                            <a href="order_details.php?order_id=<?= $history['id'] ?>" class="btn btn-details btn-action text-white">
                                                <i class="fas fa-eye"></i> Xem Chi Tiết
                                            </a>
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