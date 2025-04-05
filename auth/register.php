<?php
session_start();
require '../config/db.php';

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Kiểm tra đầu vào
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Email không hợp lệ.';
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } else {
        // Kiểm tra xem username hoặc email đã tồn tại chưa
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['error'] = 'Tên đăng nhập hoặc email đã tồn tại.';
        } else {
            // Mã hóa mật khẩu và thêm người dùng mới với vai trò mặc định là 'user'
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user'; // Vai trò mặc định
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            if ($stmt->execute([$username, $email, $passwordHash, $role])) {
                $_SESSION['success'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
                header("Location: login.php");
                exit();
            } else {
                $_SESSION['error'] = 'Đăng ký thất bại. Vui lòng thử lại.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Font Awesome để thêm biểu tượng -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- CSS tùy chỉnh -->
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Poppins', sans-serif;
        }

        .register-container {
            background: #fff;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, #f7444e, #ff6f61);
        }

        .register-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-group i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #888;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            border-color: #f7444e;
            outline: none;
            box-shadow: 0 0 5px rgba(247, 68, 78, 0.2);
        }

        .form-group input::placeholder {
            color: #aaa;
        }

        .btn-register {
            width: 100%;
            padding: 0.75rem;
            background: #f7444e;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.1s ease;
        }

        .btn-register:hover {
            background: #ff6f61;
            transform: translateY(-2px);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 1rem;
        }

        .login-link a {
            color: #f7444e;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Đăng ký</h2>
        <form method="post">
            <div class="form-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Tên đăng nhập" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Mật khẩu" required>
            </div>
            <button type="submit" class="btn-register">Đăng ký</button>
            <div class="login-link">
                <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
            </div>
        </form>
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