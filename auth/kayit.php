<?php
require_once '../config/baglan.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../phpmailer/Exception.php';
require '../phpmailer/PHPMailer.php';
require '../phpmailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $verification_token = bin2hex(random_bytes(32));

    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() == 0) {
        $insert = $db->prepare("INSERT INTO users (full_name, email, password_hash, verification_token) VALUES (?, ?, ?, ?)");
        $result = $insert->execute([$full_name, $email, $password_hash, $verification_token]);
        
        if($result) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'localhost';
                $mail->SMTPAuth   = true;
                $mail->Username   = '...';
                $mail->Password   = '...'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('info@kayserisehirportali.tr', 'Kayseri Şehir Portalı');
                $mail->addAddress($email, $full_name);

                $mail->isHTML(true);
                $mail->Subject = 'Kayseri Şehir Portalı - Hesap Doğrulama';
                $dogrulama_linki = "http://kayserisehirportali.tr/auth/onay.php?token=" . $verification_token;
                $mail->Body    = "Merhaba $full_name,<br><br>Hesabınızı aktifleştirmek için lütfen aşağıdaki linke tıklayın:<br><br><a href='$dogrulama_linki'>Hesabımı Doğrula</a>";

                $mail->send();
                $mesaj = "Kayıt başarılı! Lütfen e-postanıza gelen doğrulama linkine tıklayın.";
            } catch (Exception $e) {
                $hata = "Kayıt yapıldı ancak mail gönderilemedi. Hata: {$mail->ErrorInfo}";
            }
        }
    } else {
        $hata = "Bu e-posta adresi zaten kayıtlı.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Kayseri Şehir Portalı</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold text-center text-blue-600 mb-6">Kayıt Ol</h2>
        <?php if(isset($mesaj)): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm"><?= $mesaj ?></div>
        <?php endif; ?>
        <?php if(isset($hata)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm"><?= $hata ?></div>
        <?php endif; ?>
        <form method="POST" action="kayit.php" class="space-y-4">
            <input type="text" name="full_name" placeholder="Ad Soyad" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="email" name="email" placeholder="E-posta Adresi" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="password" name="password" placeholder="Şifre" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded hover:bg-blue-700 transition">Kayıt Ol</button>
        </form>
        <p class="text-sm text-center text-gray-500 mt-4">Zaten hesabın var mı? <a href="giris.php" class="text-blue-600 hover:underline">Giriş Yap</a></p>
        <div class="mt-4 text-center">
            <a href="../index.php" class="text-sm text-gray-400 hover:text-gray-600">Ana Sayfaya Dön</a>
        </div>
    </div>
</body>
</html>