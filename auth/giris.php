<?php
require_once '../config/baglan.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT id, password_hash, is_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($user['is_verified'] == 1) {
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                header("Location: ../index.php");
                exit;
            } else {
                $hata = "Hatalı şifre.";
            }
        } else {
            $hata = "Lütfen önce e-posta adresinizi onaylayın.";
        }
    } else {
        $hata = "Kullanıcı bulunamadı.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Kayseri Portalı</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold text-center text-blue-600 mb-6">Giriş Yap</h2>
        <?php if(isset($hata)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm"><?= $hata ?></div>
        <?php endif; ?>
        <form method="POST" action="giris.php" class="space-y-4">
            <div>
                <input type="email" name="email" placeholder="E-posta Adresi" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <input type="password" name="password" placeholder="Şifre" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-green-500 text-white font-bold py-2 rounded hover:bg-green-600 transition">Giriş Yap</button>
        </form>
        <p class="text-sm text-center text-gray-500 mt-4">Hesabın yok mu? <a href="kayit.php" class="text-blue-600 hover:underline">Kayıt Ol</a></p>
        <div class="mt-4 text-center">
            <a href="../index.php" class="text-sm text-gray-400 hover:text-gray-600">Ana Sayfaya Dön</a>
        </div>
    </div>
</body>
</html>