<?php
$file = 'assets/data/Mahalleler.xls';

if (!file_exists($file)) {
    die("Hata: Mahalleler.xls dosyası assets/data/ klasöründe bulunamadı.");
}

$html = file_get_contents($file);
$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'windows-1254');

$dom = new DOMDocument();
@$dom->loadHTML($html);

$rows = $dom->getElementsByTagName('tr');
$data = [];
$is_header = true;

foreach ($rows as $row) {
    if ($is_header) {
        $is_header = false;
        continue;
    }
    
    $cols = $row->getElementsByTagName('td');
    if ($cols->length > 0) {
        $ilce = trim($cols->item(0)->textContent);
        $kimlikNo = trim($cols->item(1)->textContent);
        $mahalle = trim($cols->item(2)->textContent);
        $alan = trim($cols->item(3)->textContent);
        $nufus = trim($cols->item(5)->textContent);
        $muhtar = trim($cols->item(6)->textContent);
        $cep = trim($cols->item(7)->textContent);
        $tel = trim($cols->item(8)->textContent);
        
        $cep = trim(str_replace(["&nbsp;", "\xc2\xa0"], "", $cep));
        $tel = trim(str_replace(["&nbsp;", "\xc2\xa0"], "", $tel));
        $muhtar = trim(str_replace(["&nbsp;", "\xc2\xa0"], "", $muhtar));
        
        if (empty($muhtar) || $muhtar == 'nan') {
            $muhtar = 'Kurum Tarafından Yayınlanmadı';
        }
        if (empty($cep) || $cep == 'nan') {
            $cep = !empty($tel) && $tel != 'nan' ? $tel : 'Yayınlanmadı';
        }
        
        $data[] = [
            'ilce' => $ilce,
            'kimlikNo' => $kimlikNo,
            'neighborhoodName' => $mahalle,
            'alan' => $alan,
            'name' => $muhtar,
            'population' => $nufus,
            'gsm' => $cep
        ];
    }
}

file_put_contents('assets/data/muhtarlar.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Harika! muhtarlar.json dosyası tüm detaylarıyla güncellendi. Toplam " . count($data) . " mahalle kaydedildi.";
?>