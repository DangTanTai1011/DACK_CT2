<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Bạn không có quyền truy cập trang này.';
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'change_role' && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $new_role = $_GET['role'] === 'admin' ? 'user' : 'admin';

    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = 'Bạn không thể thay đổi vai trò của chính mình.';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        if ($stmt->execute([$new_role, $user_id])) {
            $_SESSION['success'] = 'Cập nhật vai trò thành công!';
        } else {
            $_SESSION['error'] = 'Cập nhật vai trò thất bại.';
        }
    }
    header("Location: manage_users.php");
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $user_id = $_GET['id'];

    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = 'Bạn không thể xóa tài khoản của chính mình.';
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            $_SESSION['success'] = 'Xóa tài khoản thành công!';
        } else {
            $_SESSION['error'] = 'Xóa tài khoản thất bại.';
        }
    }
    header("Location: manage_users.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tài khoản</title>
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
            margin: 0 0.25rem;
        }

        .btn-role {
            background: #007bff;
            border: none;
        }

        .btn-role:hover {
            background: #0056b3;
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

            .btn-action {
                display: block;
                margin: 0.25rem 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <a href="../auth/admin_dashboard.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Quay về</a>
                <h2><i class="fas fa-users"></i> Quản lý tài khoản</h2>
                <div></div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên đăng nhập</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['id']) ?></td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <span class="badge <?= $user['role'] === 'admin' ? 'bg-success' : 'bg-primary' ?>">
                                                <?= htmlspecialchars($user['role']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                                        <td>
                                            <a href="manage_users.php?action=change_role&id=<?= $user['id'] ?>&role=<?= $user['role'] ?>" class="btn btn-role btn-action text-white">
                                                <i class="fas fa-user-shield"></i> Đổi vai trò
                                            </a>
                                            <a href="manage_users.php?action=delete&id=<?= $user['id'] ?>" class="btn btn-delete btn-action text-white" onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?');">
                                                <i class="fas fa-trash"></i> Xóa
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Không có tài khoản nào.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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