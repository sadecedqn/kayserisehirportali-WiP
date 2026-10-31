<?php
require_once '../config/baglan.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

$endpoints = [
    'nobetci_eczane'   => 'https://acikveri.info@kayserisehirportali.tr/api/kbb/eczane',
    'kaski_su_kesinti' => 'https://acikveri.info@kayserisehirportali.tr/api/kaskisukesentisi/get',
    'wifi_noktalari'   => 'https://acikveri.info@kayserisehirportali.tr/uploads/data/2024/10/7/ucretsiz_wifi_noktalari-551423.geojson',
    'vefat'            => 'https://acikveri.info@kayserisehirportali.tr/api/kbb/vefat',
    'muhtar'           => 'https://acikveri.info@kayserisehirportali.tr/api/kbb/muhtarlar/9',
    'hal'              => 'https://acikveri.info@kayserisehirportali.tr/api/kbb/hal-fiyat-listesi'
];

function taziyeAdresiniKoordinataCevir($adresMetni, $index = 0) {
    $hamAdres = trim((string)$adresMetni);
    
    if ($hamAdres === '' || mb_stripos($hamAdres, 'Belirtilmemiş', 0, 'UTF-8') !== false || $hamAdres === '-') {
        return null;
    }

    $ilceMerkezleri = [
        'MELİKGAZİ'  => [38.7180, 35.5000], 'MELIKGAZI'  => [38.7180, 35.5000],
        'KOCASİNAN'  => [38.7450, 35.4750], 'KOCASINAN'  => [38.7450, 35.4750],
        'TALAS'      => [38.6908, 35.5539],
        'DEVELİ'     => [38.3892, 35.4922], 'DEVELI'     => [38.3892, 35.4922],
        'YAHYALI'    => [38.1025, 35.3578],
        'BÜNYAN'     => [38.8464, 35.8603], 'BUNYAN'     => [38.8464, 35.8603],
        'İNCESU'     => [38.6250, 35.1908], 'INCESU'     => [38.6250, 35.1908],
        'PINARBAŞI'  => [38.7219, 36.3919], 'PINARBASI'  => [38.7219, 36.3919],
        'TOMARZA'    => [38.4458, 35.7989],
        'YEŞİLHİSAR' => [38.3569, 35.0889], 'YESILHISAR' => [38.3569, 35.0889],
        'SARIOĞLAN'  => [39.0767, 36.0669], 'SARIOGLAN'  => [39.0767, 36.0669],
        'HACILAR'    => [38.6439, 35.4500],
        'SARIZ'      => [38.4792, 36.4986],
        'AKKIŞLA'    => [39.0044, 36.1756], 'AKKISLA'    => [39.0044, 36.1756],
        'FELAHİYE'   => [39.0906, 35.5672], 'FELAHIYE'   => [39.0906, 35.5672],
        'ÖZVATAN'    => [39.1128, 35.7100], 'OZVATAN'    => [39.1128, 35.7100]
    ];

    $sorguListesi = [];

    $temiz = preg_replace('/\b(No|NO|no)\s*[:.]?\s*\d+[A-Za-z\/]*/u', '', $hamAdres);
    $temiz = str_ireplace(['Mah.', 'MAH.', 'Sokak/Cadde', 'Cad./Sok.', 'Apt.', 'Kat:', 'Daire:'], ['Mahallesi', 'Mahallesi', 'Sokak', 'Caddesi', '', '', ''], $temiz);
    $temiz = trim(preg_replace('/\s+/', ' ', $temiz));

    $sorguListesi[] = $temiz . ', Kayseri, Türkiye';

    if (preg_match('/([A-Za-zĞÜŞÖÇİIğüşöçı\s]+?)\s+(Mahallesi|Mah)/ui', $temiz, $m)) {
        $mahalleAdi = trim($m[1]) . ' Mahallesi';
        $bulunanIlce = '';
        $upperAdres = mb_strtoupper($hamAdres, 'UTF-8');
        foreach ($ilceMerkezleri as $ilceAd => $koord) {
            if (mb_strpos($upperAdres, $ilceAd) !== false) {
                $bulunanIlce = $ilceAd;
                break;
            }
        }
        $sorguListesi[] = $mahalleAdi . ($bulunanIlce ? ', ' . $bulunanIlce : '') . ', Kayseri, Türkiye';
    }

    foreach (array_unique($sorguListesi) as $sorgu) {
        $geoUrl = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=tr&q=' . urlencode($sorgu);
        $chGeo = curl_init();
        curl_setopt($chGeo, CURLOPT_URL, $geoUrl);
        curl_setopt($chGeo, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chGeo, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($chGeo, CURLOPT_TIMEOUT, 5);
        curl_setopt($chGeo, CURLOPT_USERAGENT, 'KayseriSehirPortali/1.0 (iletisim@info@kayserisehirportali.tr)');
        $geoRes = curl_exec($chGeo);
        curl_close($chGeo);

        if ($geoRes) {
            $geoArr = json_decode($geoRes, true);
            if (is_array($geoArr) && count($geoArr) > 0 && isset($geoArr[0]['lat'], $geoArr[0]['lon'])) {
                usleep(350000);
                $offsetLat = (($index % 5) - 2) * 0.00025;
                $offsetLng = ((intval($index / 5) % 5) - 2) * 0.00025;
                return [
                    'lat' => round(floatval($geoArr[0]['lat']) + $offsetLat, 6),
                    'lng' => round(floatval($geoArr[0]['lon']) + $offsetLng, 6)
                ];
            }
        }
        usleep(250000);
    }

    $upperAdres = mb_strtoupper($hamAdres, 'UTF-8');
    foreach ($ilceMerkezleri as $ilceKey => $koord) {
        if (mb_strpos($upperAdres, $ilceKey) !== false) {
            $offsetLat = (($index % 7) - 3) * 0.0008;
            $offsetLng = ((intval($index / 7) % 7) - 3) * 0.0008;
            return [
                'lat' => round($koord[0] + $offsetLat, 6),
                'lng' => round($koord[1] + $offsetLng, 6)
            ];
        }
    }

    return null;
}

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
        if ($name === 'vefat') {
            $vefatListesi = json_decode($json_data, true);
            if (is_array($vefatListesi)) {
                foreach ($vefatListesi as $idx => &$ilan) {
                    $koord = taziyeAdresiniKoordinataCevir(
                        $ilan['taziyeAdresi'] ?? '',
                        $idx
                    );
                    if ($koord !== null) {
                        $ilan['latitude']  = $koord['lat'];
                        $ilan['longitude'] = $koord['lng'];
                    } else {
                        $ilan['latitude']  = null;
                        $ilan['longitude'] = null;
                    }
                }
                unset($ilan);
                $json_data = json_encode($vefatListesi, JSON_UNESCAPED_UNICODE);
            }
        }

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