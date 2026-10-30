<div align="center">

<img src="assets/icons/kbb.png" alt="Kayseri Şehir Portalı" width="110" />

# 🏙️ Kayseri Şehir Portalı

### Kayseri'nin dijital ikizi, tek bir haritada.

Kandilli Rasathanesi, AFAD ve Kayseri Büyükşehir Belediyesi gibi kaynaklardan gelen açık verileri interaktif bir harita üzerinde buluşturan şehir rehberi.

<br />

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Leaflet](https://img.shields.io/badge/Leaflet-199900?style=for-the-badge&logo=leaflet&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![OpenStreetMap](https://img.shields.io/badge/OpenStreetMap-7EBC6F?style=for-the-badge&logo=openstreetmap&logoColor=white)

![Açık Veri](https://img.shields.io/badge/Açık_Veri-Destekliyor-2563eb?style=flat-square)
![Canlı Veri](https://img.shields.io/badge/Canlı_Veri-Kandilli_%7C_Open--Meteo-dc2626?style=flat-square)
![Dil](https://img.shields.io/badge/Dil-Türkçe-e11d48?style=flat-square)
![Durum](https://img.shields.io/badge/Durum-Geliştiriliyor_(WiP)-f59e0b?style=flat-square)
![Stars](https://img.shields.io/github/stars/sadecedqn/kayserisehirportali-WiP?style=flat-square&color=eab308)
![Last Commit](https://img.shields.io/github/last-commit/sadecedqn/kayserisehirportali-WiP?style=flat-square&color=2563eb)

[Özellikler](#-özellikler) •
[Katmanlar](#%EF%B8%8F-harita-katmanları) •
[Mimari](#-mimari) •
[Kurulum](#-kurulum) •
[Veri Kaynakları](#-veri-kaynakları) •
[Yapı](#-proje-yapısı)

</div>

---

## 📸 Ekran Görüntüleri

<!-- Ekran görüntülerini repoya ekleyip yolları güncelle -->
<div align="center">

| Ana Sayfa | Şehir Portalı |
|:---:|:---:|
| ![Ana Sayfa](docs/screenshots/anasayfa.png) | ![Portal](docs/screenshots/portal.png) |

</div>

---

## ✨ Özellikler

- 🗺️ **İnteraktif harita:** Leaflet.js tabanlı, Canvas destekli harita motoru ile yüzlerce nokta akıcı biçimde görüntülenir.
- ⚡ **Dinamik filtreleme:** Sayfayı yenilemeden katmanlar arasında anında geçiş yapılır.
- 🔎 **Akıllı arama:** Türkçe karakterlerden bağımsız arama ve Nominatim ile Türkiye genelinde yer/adres arama.
- 🌋 **Canlı deprem takibi:** Kandilli Rasathanesi verileriyle son depremler.
- 🚨 **Afet hazırlığı:** AFAD toplanma alanlarını haritada görme ve yol tarifi alma.
- 🧭 **Tek tıkla navigasyon:** Seçilen noktaya Google Haritalar üzerinden yol tarifi.
- 🌤️ **Anlık hava durumu:** Open-Meteo ile Kayseri'nin güncel sıcaklığı.
- 🔄 **Otomatik veri senkronizasyonu:** Belediye API'leri cron ile çekilip veritabanında önbelleğe alınır, harita bu önbellekten beslenir.
- 👤 **Üyelik sistemi:** Kayıt, e-posta ile hesap doğrulama, giriş ve çıkış (`password_hash` ile güvenli şifre saklama).
- 📱 **Mobil uyumlu:** Tailwind CSS ile telefon, tablet ve masaüstünde düzgün çalışan arayüz.
- 🔗 **Derin bağlantılar:** `portal.php?hizmet=deprem` gibi adreslerle doğrudan istenen katmanı açma.

---

## 🗺️ Harita Katmanları

### ✅ Aktif Hizmetler

| Katman | Açıklama | Kaynak |
|---|---|---|
| 💊 **Nöbetçi Eczaneler** | Güncel nöbetçi eczane listesi | Kayseri BB Açık Veri |
| 🌋 **Son Depremler** | Canlı deprem akışı | Kandilli Rasathanesi |
| 🏕️ **Toplanma Alanları** | Afet anında güvenli bölgeler | AFAD & Açık Veri Portalı |
| 🅿️ **Şehir Otoparkları** | Otopark konumları | Kayseri BB Açık Veri |
| 📶 **Ücretsiz Wi-Fi** | Belediye Wi-Fi noktaları | Kayseri BB Açık Veri |
| 🛒 **Semt Pazarları** | Pazar yerleri | Kayseri BB Açık Veri |
| 🏛️ **Tarihi Yerler** | Müze ve gezilecek yerler | İl Kültür Turizm Müdürlüğü |

### 🚧 Sınırlı / Veri Bekleyen Hizmetler

| Katman | Durum |
|---|---|
| 📹 **Şehir Kameraları** | Konumlar haritada gösterilir. Canlı yayın KVKK gereği kapalıdır. |
| 🚲 **KAYBİS İstasyonları** | İlgili kurum tarafından aktif API sunulmadığı için veri bekleniyor. |
| 💧 **Su Kesintileri** | İlgili kurum tarafından aktif API sunulmadığı için veri bekleniyor. |
| 🔌 **Şarj Noktaları** | İlgili kurum tarafından aktif API sunulmadığı için veri bekleniyor. |
| ⚽ **Spor Tesisleri** | İlgili kurum tarafından aktif API sunulmadığı için veri bekleniyor. |

---

## 🧩 Mimari

Veriler iki yoldan gelir: belediye verileri cron ile MySQL önbelleğine alınır, diğerleri ise doğrudan istemciden veya yerel veri dosyalarından okunur.

```mermaid
flowchart LR
    subgraph Kaynaklar
        A[Kayseri BB Açık Veri API]
        B[Kandilli Deprem API]
        C[Open-Meteo]
        D[(assets/data<br/>GeoJSON / JSON)]
    end

    A -->|cron_guncelle.php<br/>düzenli çekim| DB[(MySQL<br/>api_cache)]
    DB -->|api_getir.php| JS
    B --> JS[script.js<br/>Leaflet harita]
    C --> JS
    D --> JS
    JS --> U([Kullanıcı])
```

| Veri | Yol |
|---|---|
| Nöbetçi eczaneler, Wi-Fi noktaları | Cron → MySQL `api_cache` → `api/api_getir.php` → harita |
| Depremler | Doğrudan istemciden Kandilli API |
| Otoparklar, pazarlar, toplanma alanları, tarihi yerler | `assets/data/` altındaki yerel GeoJSON/JSON dosyaları |
| Hava durumu | Doğrudan istemciden Open-Meteo |

---

## 🧰 Teknolojiler

| Alan | Teknoloji |
|---|---|
| **Backend** | PHP (PDO) |
| **Veritabanı** | MySQL / MariaDB |
| **E-posta** | [PHPMailer](https://github.com/PHPMailer/PHPMailer) (hesap doğrulama) |
| **Frontend** | HTML5, Vanilla JavaScript, [Tailwind CSS](https://tailwindcss.com/) (CDN) |
| **Harita** | [Leaflet.js](https://leafletjs.com/) 1.9.4, [OpenStreetMap](https://www.openstreetmap.org/) karoları |
| **Arama** | [Nominatim](https://nominatim.openstreetmap.org/) (Geocoding) |
| **İkonlar** | [Font Awesome](https://fontawesome.com/) 6.4 |
| **Veri Formatı** | GeoJSON, JSON |

---

## 🚀 Kurulum

### Gereksinimler

- PHP 7.4 veya üzeri (`pdo_mysql` ve `curl` eklentileri açık)
- MySQL / MariaDB
- Apache / Nginx ya da XAMPP, WAMP, Laragon gibi yerel bir sunucu
- İnternet bağlantısı (harita karoları, CDN'ler ve canlı API'ler için)

### 1. Repoyu klonla

```bash
git clone https://github.com/sadecedqn/kayserisehirportali-WiP.git
cd kayserisehirportali-WiP
```

### 2. PHPMailer'ı ekle

Kayıt sırasında doğrulama e-postası gönderildiği için PHPMailer gerekir. `Exception.php`, `PHPMailer.php` ve `SMTP.php` dosyalarını proje kökündeki `phpmailer/` klasörüne koy.

### 3. Veritabanını oluştur

Aşağıdaki tablolar kodun kullandığı alanlara göre hazırlanmış örnek şemadır:

<details>
<summary><b>📄 Örnek SQL şeması</b></summary>

```sql
CREATE TABLE users (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    full_name          VARCHAR(150) NOT NULL,
    email              VARCHAR(190) NOT NULL UNIQUE,
    password_hash      VARCHAR(255) NOT NULL,
    verification_token VARCHAR(64)  NULL,
    is_verified        TINYINT(1)   NOT NULL DEFAULT 0
) CHARACTER SET utf8mb4;

CREATE TABLE api_cache (
    endpoint_name VARCHAR(100) PRIMARY KEY,
    json_data     LONGTEXT NOT NULL
) CHARACTER SET utf8mb4;
```

</details>

### 4. Bağlantı ayarlarını yap

`config/baglan.php` dosyasında kendi veritabanı bilgilerini gir:

```php
$host     = 'localhost';
$dbname   = 'VERITABANI_ADI';
$username = 'KULLANICI_ADI';
$password = 'SIFRE';
```

E-posta (SMTP) ayarlarını da kendi sunucuna göre `auth/kayit.php` içinde düzenle.


### 5. Verileri ilk kez çek

Tarayıcıdan veya terminalden `cron_guncelle.php` dosyasını bir kez çalıştır:

```bash
php api/cron_guncelle.php
```

### 6. Otomatik güncellemeyi kur

Verilerin 5 dakikada bir yenilenmesi için sunucunda cron tanımla:

```cron
*/5 * * * * php /yol/kayserisehirportali-WiP/api/cron_guncelle.php
```

### 7. Çalıştır

```bash
php -S localhost:8000
```

Tarayıcıda `http://localhost:8000` adresini aç.

> [!NOTE]
> Proje PHP içerdiği için dosyalara çift tıklayarak açılmaz. Bir PHP sunucusu üzerinden çalıştırılmalıdır.

---

## 🔗 Doğrudan Katman Bağlantıları

Portal, `hizmet` parametresiyle istenen katmanı otomatik açar:

| Adres | Açılan Katman |
|---|---|
| `portal.php?hizmet=nobetci_eczane` | Nöbetçi Eczaneler |
| `portal.php?hizmet=deprem` | Son Depremler |
| `portal.php?hizmet=toplanma_alanlari` | Toplanma Alanları |
| `portal.php?hizmet=otoparklar` | Şehir Otoparkları |
| `portal.php?hizmet=wifi_noktalari` | Ücretsiz Wi-Fi |
| `portal.php?hizmet=pazar_yerleri` | Semt Pazarları |
| `portal.php?hizmet=gezilecek_yerler` | Tarihi Yerler |
| `portal.php?hizmet=kameralar` | Şehir Kameraları |

---

## 📂 Proje Yapısı

```
📦 kayserisehirportali-WiP
├── 📄 index.php                # Karşılama sayfası (hero harita, hizmetler, veri kaynakları)
├── 📄 portal.php               # Tam ekran interaktif şehir portalı
│
├── 📁 api/
│   ├── 📄 api_getir.php        # Önbellekteki veriyi JSON olarak sunar
│   └── 📄 cron_guncelle.php    # Belediye API'lerinden veriyi çekip veritabanına yazar
│
├── 📁 auth/
│   ├── 📄 giris.php            # Kullanıcı girişi
│   ├── 📄 kayit.php            # Kayıt + doğrulama e-postası
│   ├── 📄 onay.php             # E-posta doğrulama bağlantısı
│   └── 📄 cikis.php            # Oturumu kapatma
│
├── 📁 config/
│   └── 📄 baglan.php           # Veritabanı bağlantısı ve oturum ayarları
│
├── 📁 phpmailer/               # PHPMailer kütüphanesi (kurulum 2. adım)
│
└── 📁 assets/
    ├── 📄 script.js            # Harita, filtreleme, arama ve katman mantığı
    ├── 📄 style.css            # Özel stiller
    ├── 📁 icons/               # Logolar ve katman ikonları
    └── 📁 data/                # Yerel veri setleri
        ├── 📄 kayseri-gezilecek-yerler.json
        ├── 📄 otoparklar-456371.geojson.txt
        ├── 📄 pazar_yeri-4932711.geojson.txt
        └── 📄 toplanma-alanlari_alan-3829284.geojson.txt
```

---

## 📡 Veri Kaynakları

Bu proje, veriyi kendisi üretmez. Aşağıdaki kaynaklardan gelen açık verileri görselleştirir:

- 🏛️ **Kayseri Büyükşehir Belediyesi Açık Veri Portalı**
- 🌋 **Kandilli Rasathanesi** (deprem verileri)
- 🚨 **AFAD** (toplanma alanları)
- 🎭 **İl Kültür ve Turizm Müdürlüğü** (tarihi yerler)
- 🌤️ **[Open-Meteo](https://open-meteo.com/)** (hava durumu)
- 🗺️ **[OpenStreetMap](https://www.openstreetmap.org/copyright)** katkıcıları (harita ve arama)

> [!IMPORTANT]
> Verilerin güncelliği ve doğruluğu ilgili kurumların API'lerine bağlıdır. Acil durumlarda resmi kurumların kanallarını esas alın.

---

## 🗺️ Yol Haritası

- [ ] KAYBİS istasyonları için veri entegrasyonu
- [ ] Su kesintileri (KASKİ) entegrasyonu
- [ ] Elektrikli araç şarj noktaları
- [ ] Spor tesisleri katmanı
- [ ] Koyu tema desteği

---

## 🤝 Katkıda Bulunma

Katkılar memnuniyetle karşılanır.

1. Repoyu fork'la
2. Yeni bir dal oluştur (`git checkout -b yeni-ozellik`)
3. Değişikliklerini commit'le (`git commit -m "Yeni özellik eklendi"`)
4. Dalını push'la (`git push origin yeni-ozellik`)
5. Pull Request aç

---

## 📜 Lisans

Bu proje [MIT Lisansı](LICENSE) ile lisanslanmıştır.

---

<div align="center">

**Açık veri felsefesini desteklemek amacıyla geliştirilmiştir.** 💙

Beğendiysen repoya ⭐ vermeyi unutma!

</div>
