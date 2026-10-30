<?php
require_once '../config/baglan.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$endpoints = [
    'nobetci_eczane' => 'https://acikveri.kayseri.bel.tr/api/kbb/eczane',
    'kaski_su_kesinti' => 'https://acikveri.kayseri.bel.tr/api/kaskisukesentisi/get',
    'wifi_noktalari' => 'https://acikveri.kayseri.bel.tr/uploads/data/2024/10/7/ucretsiz_wifi_noktalari-551423.geojson'
];

echo "Veri çekme işlemi başlatıldı...<br><br>";

foreach ($endpoints as $name => $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

    $json_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        echo "<span style='color:red'><b>$name</b> için CURL Hatası: " . curl_error($ch) . "</span><br>";
    } else if ($http_code == 200 && $json_data) {
        try {
            $stmt = $db->prepare("INSERT INTO api_cache (endpoint_name, json_data) VALUES (?, ?) ON DUPLICATE KEY UPDATE json_data = VALUES(json_data)");
            $stmt->execute([$name, $json_data]);
            echo "<span style='color:green'><b>$name</b> verisi başarıyla çekildi ve veritabanına kaydedildi!</span><br>";
        } catch(PDOException $e) {
            echo "<span style='color:red'><b>$name</b> veritabanına yazılamadı. Hata: " . $e->getMessage() . "</span><br>";
        }
    } else {
        echo "<span style='color:red'><b>$name</b> API'den çekilemedi. HTTP Kodu: $http_code</span><br>";
    }
    curl_close($ch);
}
echo "<br>İşlem tamamlandı!";
?>