<?php
require_once '../config/baglan.php';

header('Content-Type: application/json; charset=utf-8');

function kbbHalFiyatlariniCek() {
    $url = "https://www.kayseri.bel.tr/hal-fiyatlari";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_ENCODING, "");
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: tr-TR,tr;q=0.9,en-US;q=0.8,en;q=0.7'
    ]);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36');
    
    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html) return [];

    $fiyatlar = [];
    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new DOMXPath($dom);

    $rows = $xpath->query("//table//tr");

    foreach ($rows as $row) {
        $cols = $xpath->query("td", $row);
        
        if ($cols->length >= 4) {
            $urunAdi  = trim(preg_replace('/\s+/', ' ', $cols->item(0)->textContent));
            $birim    = trim(preg_replace('/\s+/', ' ', $cols->item(1)->textContent));
            $enYuksek = trim(preg_replace('/\s+/', ' ', $cols->item(2)->textContent));
            $enDusuk  = trim(preg_replace('/\s+/', ' ', $cols->item(3)->textContent));

            if (empty($urunAdi) || mb_stripos($urunAdi, 'CİNSİ') !== false) {
                continue;
            }

            $enDusukTemiz  = trim(str_replace(['₺', 'TL', 'tl'], '', $enDusuk));
            $enYuksekTemiz = trim(str_replace(['₺', 'TL', 'tl'], '', $enYuksek));

            $fiyatMetni = ($enDusukTemiz !== '' && $enYuksekTemiz !== '') 
                ? $enDusukTemiz . ' - ' . $enYuksekTemiz 
                : ($enYuksekTemiz ?: $enDusukTemiz);

            $fiyatlar[] = [
                'UrunAdi' => $urunAdi,
                'Birim'   => $birim,
                'Fiyat'   => $fiyatMetni
            ];
        }
    }

    return $fiyatlar;
}

if (isset($_GET['endpoint'])) {
    $endpoint = $_GET['endpoint'];
    
    if ($endpoint === 'hal') {
        $cacheFile = __DIR__ . '/hal_cache.json';
        $cacheSuresi = 12 * 3600;
        $zorlaGuncelle = isset($_GET['force']);

        if (!$zorlaGuncelle && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheSuresi) {
            $cachedContent = file_get_contents($cacheFile);
            $decoded = json_decode($cachedContent, true);
            if (is_array($decoded) && count($decoded) > 0) {
                echo $cachedContent;
                exit;
            }
        }

        $fiyatlar = kbbHalFiyatlariniCek();

        if (!empty($fiyatlar)) {
            $jsonData = json_encode($fiyatlar, JSON_UNESCAPED_UNICODE);
            @file_put_contents($cacheFile, $jsonData);

            try {
                $stmt = $db->prepare("SELECT endpoint_name FROM api_cache WHERE endpoint_name = ?");
                $stmt->execute(['hal']);
                if ($stmt->fetch()) {
                    $up = $db->prepare("UPDATE api_cache SET json_data = ? WHERE endpoint_name = ?");
                    $up->execute([$jsonData, 'hal']);
                } else {
                    $ins = $db->prepare("INSERT INTO api_cache (endpoint_name, json_data) VALUES (?, ?)");
                    $ins->execute(['hal', $jsonData]);
                }
            } catch (Exception $e) {
            }

            echo $jsonData;
            exit;
        }

        $stmt = $db->prepare("SELECT json_data FROM api_cache WHERE endpoint_name = ?");
        $stmt->execute(['hal']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && !empty($result['json_data']) && $result['json_data'] !== '[]') {
            echo $result['json_data'];
            exit;
        }

        echo json_encode([]);
        exit;
    }

    $stmt = $db->prepare("SELECT json_data FROM api_cache WHERE endpoint_name = ?");
    $stmt->execute([$endpoint]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo $result['json_data'];
    } else {
        echo json_encode(['error' => 'Data not found']);
    }
} else {
    echo json_encode(['error' => 'Endpoint missing']);
}
?>