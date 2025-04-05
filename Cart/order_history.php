<?php
session_start();
include '../config/db.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Bạn cần đăng nhập để xem lịch sử mua hàng.";
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy danh sách đơn hàng của người dùng
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Mua Hàng</title>
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

        .btn-shop-now {
            background: #f7444e;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-shop-now:hover {
            background: #ff6f61;
        }

        .table {
            margin-bottom: 2rem;
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }

        .status-pending {
            color: #f39c12;
            font-weight: 500;
        }

        .status-completed {
            color: #28a745;
            font-weight: 500;
        }

        .status-cancelled {
            color: #dc3545;
            font-weight: 500;
        }

        .btn-details {
            background: #007bff;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-details:hover {
            background: #0056b3;
        }

        .empty-orders {
            text-align: center;
            color: #666;
            padding: 2rem;
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

            .card-header .btn-shop-now {
                margin-bottom: 1rem;
            }

            .table th, .table td {
                font-size: 0.9rem;
            }

            .btn-details {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Lịch Sử Mua Hàng</h2>
                <a href="../index.php" class="btn btn-shop-now"><i class="fas fa-shopping-bag"></i> Mua sắm ngay</a>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <div class="empty-orders">
                        <p>Bạn chưa có đơn hàng nào.</p>
                    </div>
                <?php else: ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID Đơn Hàng</th>
                                <th>Ngày Đặt</th>
                                <th>Tổng Tiền</th>
                                <th>Trạng Thái</th>
                                <th>Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo date('d/m/Y H:i:s', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo number_format($order['total'], 0, ',', '.'); ?> VNĐ</td>
                                    <td>
                                        <span class="status-<?php echo htmlspecialchars($order['status']); ?>">
                                            <?php 
                                                switch ($order['status']) {
                                                    case 'pending':
                                                        echo 'Đang xử lý';
                                                        break;
                                                    case 'completed':
                                                        echo 'Hoàn thành';
                                                        break;
                                                    case 'cancelled':
                                                        echo 'Đã hủy';
                                                        break;
                                                    default:
                                                        echo htmlspecialchars($order['status']);
                                                }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../auth/order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-details"><i class="fas fa-eye"></i> Xem Chi Tiết</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
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