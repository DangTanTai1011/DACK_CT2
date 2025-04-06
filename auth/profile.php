<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Bạn cần đăng nhập để xem hồ sơ cá nhân.";
    header("Location: ../Auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['message'] = "Không tìm thấy thông tin người dùng.";
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';

    if ($username && $email) {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$username, $email, $user_id]);
        $_SESSION['username'] = $username;
        $_SESSION['message'] = "Cập nhật thông tin thành công!";
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['message'] = "Vui lòng điền đầy đủ thông tin.";
        header("Location: profile.php");
        exit();
    }
}

include '../includes/header.php';
?>

<section class="furniture_section layout_padding">
    <div class="container">
        <div class="heading_container">
            <h2>Hồ Sơ Cá Nhân</h2>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="box">
            <div class="detail-box">
                <form method="POST" style="max-width: 600px; margin: 0 auto;">
                    <div style="margin-bottom: 15px;">
                        <label for="username" style="display: block; margin-bottom: 5px;">Tên đăng nhập:</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label for="email" style="display: block; margin-bottom: 5px;">Email:</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                    </div>
                    <div style="text-align: center;">
                        <button type="submit" class="btn btn-success" style="padding: 8px 15px; background-color: #f7444e; border: none; color: white; border-radius: 5px;">Cập Nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>