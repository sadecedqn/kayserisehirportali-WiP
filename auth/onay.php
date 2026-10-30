<?php
require_once '../config/baglan.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $db->prepare("SELECT id FROM users WHERE verification_token = ? AND is_verified = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $update = $db->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
        if ($update->execute([$user['id']])) {
            $mesaj = "Hesabınız başarıyla onaylandı! Artık giriş yapabilirsiniz.";
        } else {
            $hata = "Onay işlemi sırasında bir sorun oluştu.";
        }
    } else {
        $hata = "Geçersiz veya daha önceden kullanılmış onay linki.";
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesap Doğrulama - Kayseri Portalı</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-96 text-center">
        <h2 class="text-2xl font-bold text-blue-600 mb-6">Hesap Doğrulama</h2>
        <?php if(isset($mesaj)): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm"><?= $mesaj ?></div>
            <a href="giris.php" class="bg-blue-600 text-white px-4 py-2 rounded mt-4 inline-block hover:bg-blue-700">Giriş Yap</a>
        <?php endif; ?>
        <?php if(isset($hata)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm"><?= $hata ?></div>
            <a href="../index.php" class="text-gray-500 hover:underline">Ana Sayfaya Dön</a>
        <?php endif; ?>
    </div>
</body>
</html>