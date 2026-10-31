<?php
require_once 'config/baglan.php';
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kayseri Şehir Portalı</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="assets/style.css" />
<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    
    .marker-anim {
        transition: opacity 0.2s ease-out;
    }

    .custom-marker-icon, .quake-marker-icon {
        box-shadow: none !important;
        will-change: auto !important;
    }
    .leaflet-marker-icon {
        transition: none !important;
    }

    .minimal-popup .leaflet-popup-content-wrapper {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 14px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.16);
        border: 1px solid rgba(226,232,240,0.9);
        padding: 0;
        overflow: hidden;
    }
    .minimal-popup .leaflet-popup-tip { background: rgba(255, 255, 255, 0.98); }
    .minimal-popup .leaflet-popup-content { margin: 8px 10px; line-height: 1.4; }

    html, body {
        background: transparent !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        overflow: hidden !important;
    }

    #map {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        margin: 0 !important;
        padding: 0 !important;
        z-index: 0 !important;
    }

    #main-sidebar, .glass-panel {
        background: transparent !important;
        background-color: transparent !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        border: none !important;
        box-shadow: none !important;
    }
</style>
</head>
<body>

<div class="fixed top-0 left-0 h-full z-20 flex pointer-events-none bg-transparent">
    
    <div id="main-sidebar" class="w-80 h-full flex flex-col justify-between p-4 z-30 pointer-events-none shrink-0 transition-transform duration-300">
        
        <div class="space-y-2.5 pointer-events-auto">
            
            <div class="h-16 bg-blue-600/95 text-white flex items-center px-4 rounded-2xl shadow-lg relative overflow-hidden border border-white/20">
                <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('assets/icons/kbb.png');"></div>
                <div class="flex items-center relative z-10 w-full">
                    <a href="index.php" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shrink-0 shadow-sm p-1 hover:scale-105 transition-transform" title="Ana Sayfaya Dön">
                        <img src="assets/icons/kbb.png" alt="KBB" class="w-full h-full object-contain">
                    </a>
                    <h1 class="text-[16px] font-bold ml-3 drop-shadow-md truncate">Kayseri Şehir Portalı</h1>
                </div>
            </div>

            <div class="relative">
                <div class="relative">
                    <input type="text" id="yer-arama" placeholder="Türkiye'de yer veya adres ara..." autocomplete="off" class="w-full bg-white border border-gray-200 text-sm rounded-full focus:ring-2 focus:ring-blue-500 focus:border-transparent block pl-10 p-2.5 shadow-md outline-none transition-all font-medium text-gray-700">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-blue-500"></i>
                    </div>
                </div>
                <ul id="arama-sonuclari" class="absolute z-50 w-full bg-white border border-gray-200 rounded-2xl shadow-2xl mt-2 hidden max-h-60 overflow-y-auto divide-y divide-gray-100"></ul>
            </div>

            <button onclick="veriCekVeGoster('tumu')" id="btn-hizmet-tumu" class="portal-filter-btn relative w-full px-4 py-2.5 bg-white rounded-full shadow-md border-2 border-transparent hover:bg-blue-50 transition-all flex items-center justify-center cursor-pointer">
                <div class="absolute left-3.5 w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-earth-americas text-xs"></i>
                </div>
                <span class="font-bold text-[13px] text-gray-800 text-center">Tüm Hizmetler</span>
            </button>

            <div class="flex gap-2">
                <button id="tab-aktif" onclick="gosterKullanilabilirHizmetler()" class="flex-1 py-2 px-3 bg-white rounded-full shadow-md border-2 border-blue-500 font-bold text-[12px] text-gray-800 flex items-center justify-center gap-1.5 transition-all hover:bg-blue-50 cursor-pointer">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> Aktif Hizmetler
                </button>
                <button id="tab-pasif" onclick="gosterKullanilamayanHizmetler()" class="flex-1 py-2 px-3 bg-white rounded-full shadow-md border-2 border-transparent font-bold text-[12px] text-gray-800 flex items-center justify-center gap-1.5 transition-all hover:bg-red-50 cursor-pointer">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Deaktif
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto scrollbar-hide my-3 space-y-2 pointer-events-auto pr-1">
            
            <button onclick="veriCekVeGoster('deprem')" id="btn-hizmet-deprem" class="portal-filter-btn active-service w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-purple-50 transition-all flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <img src="assets/icons/kandilli.jpg" onerror="this.src='assets/icons/kandilli.png'" class="w-7 h-7 object-contain rounded-full shrink-0">
                    <span class="font-bold text-[13px] text-gray-800">Son Depremler</span>
                </div>
                <div class="w-5 h-5 bg-purple-600 rounded-full border border-white shadow-sm shrink-0 flex items-center justify-center text-[10px] text-white font-bold">M</div>
            </button>

            <button onclick="veriCekVeGoster('toplanma_alanlari')" id="btn-hizmet-toplanma_alanlari" class="portal-filter-btn active-service w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-emerald-50 transition-all flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <img src="assets/icons/afad2.png" class="w-7 h-7 object-contain shrink-0">
                    <span class="font-bold text-[13px] text-gray-800">Afet Toplanma Alanları</span>
                </div>
                <div class="w-5 h-5 bg-emerald-600 rounded-full border border-white shadow-sm shrink-0 flex items-center justify-center text-white"><i class="fa-solid fa-shield-halved text-[9px]"></i></div>
            </button>

            <button onclick="veriCekVeGoster('gezilecek_yerler')" id="btn-hizmet-gezilecek_yerler" class="portal-filter-btn active-service w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-rose-50 transition-all flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-rose-600 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                        <i class="fa-solid fa-building-columns text-xs"></i>
                    </div>
                    <span class="font-bold text-[13px] text-gray-800">Tarihi Yerler</span>
                </div>
                <div class="w-5 h-5 bg-rose-600 rounded-full border border-white shadow-sm shrink-0 flex items-center justify-center text-white"><i class="fa-solid fa-building-columns text-[9px]"></i></div>
            </button>

            <button onclick="veriCekVeGoster('otoparklar')" id="btn-hizmet-otoparklar" class="portal-filter-btn active-service w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-gray-100 transition-all flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-gray-800 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                        <i class="fa-solid fa-square-parking text-xs"></i>
                    </div>
                    <span class="font-bold text-[13px] text-gray-800">Şehir Otoparkları</span>
                </div>
                <div class="w-5 h-5 bg-gray-800 rounded-full border border-white shadow-sm shrink-0 flex items-center justify-center text-white font-bold text-[9px]">P</div>
            </button>

            <button onclick="veriCekVeGoster('pazar_yerleri')" id="btn-hizmet-pazar_yerleri" class="portal-filter-btn active-service w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-sky-50 transition-all flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-sky-500 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                        <i class="fa-solid fa-basket-shopping text-xs"></i>
                    </div>
                    <span class="font-bold text-[13px] text-gray-800">Pazarlar</span>
                </div>
                <div class="w-5 h-5 bg-sky-500 rounded-full border border-white shadow-sm shrink-0 flex items-center justify-center text-white"><i class="fa-solid fa-basket-shopping text-[9px]"></i></div>
            </button>

            <button onclick="veriCekVeGoster('nobetci_eczane')" id="btn-hizmet-nobetci_eczane" class="portal-filter-btn active-service w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-red-50 transition-all flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <img src="assets/icons/eczane.png" class="w-7 h-7 object-contain shrink-0">
                    <span class="font-bold text-[13px] text-gray-800">Nöbetçi Eczaneler</span>
                </div>
                <div class="w-5 h-5 bg-white rounded-full border border-red-500 shadow-sm shrink-0 flex items-center justify-center"><img src="assets/icons/eczane.png" class="w-3 h-3 object-contain"></div>
            </button>

            <button onclick="veriCekVeGoster('wifi_noktalari')" id="btn-hizmet-wifi_noktalari" class="portal-filter-btn active-service w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-green-50 transition-all flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-green-500 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                        <i class="fa-solid fa-wifi text-xs"></i>
                    </div>
                    <span class="font-bold text-[13px] text-gray-800">Ücretsiz Wi-Fi</span>
                </div>
                <div class="w-5 h-5 bg-green-500 rounded-full border border-white shadow-sm shrink-0 flex items-center justify-center text-white"><i class="fa-solid fa-wifi text-[9px]"></i></div>
            </button>

            <button onclick="veriCekVeGoster('vefat')" id="btn-hizmet-vefat" class="portal-filter-btn active-service w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-zinc-100 transition-all flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-zinc-800 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                        <i class="fa-solid fa-ribbon text-xs"></i>
                    </div>
                    <span class="font-bold text-[13px] text-gray-800">Vefat İlanları</span>
                </div>
                <div class="w-5 h-5 bg-zinc-800 rounded-full border border-white shadow-sm shrink-0 flex items-center justify-center text-white"><i class="fa-solid fa-ribbon text-[9px]"></i></div>
            </button>

            <button onclick="veriCekVeGoster('muhtar')" id="btn-hizmet-muhtar" class="portal-filter-btn active-service w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-teal-50 transition-all flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-teal-600 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                        <i class="fa-solid fa-users text-xs"></i>
                    </div>
                    <span class="font-bold text-[13px] text-gray-800">Mahalle/Muhtarlık Bilgileri</span>
                </div>
                <div class="w-5 h-5 bg-teal-600 rounded-full border border-white shadow-sm shrink-0 flex items-center justify-center text-white" title="Mahalle Sınırını Gösterir"><i class="fa-solid fa-draw-polygon text-[9px]"></i></div>
            </button>

            <button onclick="veriCekVeGoster('cadde_sokak')" id="btn-hizmet-cadde_sokak" class="portal-filter-btn active-service w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-indigo-50 transition-all flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                        <i class="fa-solid fa-road text-xs"></i>
                    </div>
                    <span class="font-bold text-[13px] text-gray-800">İlçe İstatistikleri</span>
                </div>
                <div class="w-5 h-5 bg-indigo-600 rounded-full border border-white shadow-sm shrink-0 flex items-center justify-center text-white" title="İlçe Sınırını Gösterir"><i class="fa-solid fa-draw-polygon text-[9px]"></i></div>
            </button>

            <button onclick="veriCekVeGoster('hal')" id="btn-hizmet-hal" class="portal-filter-btn active-service w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-orange-50 transition-all flex items-center gap-3 cursor-pointer">
                <img src="assets/icons/hal.png" onerror="this.onerror=null; this.src='assets/icons/kbb.png';" class="w-7 h-7 object-contain rounded-full shrink-0">
                <span class="font-bold text-[13px] text-gray-800">Hal Fiyatları</span>
            </button>

            <button onclick="veriCekVeGoster('kameralar')" id="btn-hizmet-kameralar" class="portal-filter-btn inactive-service hidden w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-slate-100 transition-all flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-slate-700 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                        <i class="fa-solid fa-video text-xs"></i>
                    </div>
                    <span class="font-bold text-[13px] text-gray-800">Şehir Kameraları</span>
                </div>
                <div class="w-5 h-5 bg-slate-700 rounded-full border border-white shadow-sm shrink-0 flex items-center justify-center text-white"><i class="fa-solid fa-video text-[9px]"></i></div>
            </button>

            <button onclick="veriCekVeGoster('kaybis')" id="btn-hizmet-kaybis" class="portal-filter-btn inactive-service hidden w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-blue-50 transition-all flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <img src="assets/icons/kaybis.png" class="w-7 h-7 object-contain shrink-0">
                    <span class="font-bold text-[13px] text-gray-800">KAYBİS İstasyonları</span>
                </div>
                <div class="w-5 h-5 bg-white rounded-full border border-blue-500 shadow-sm shrink-0 flex items-center justify-center"><img src="assets/icons/kaybis.png" class="w-3.5 h-3.5 object-contain"></div>
            </button>

            <button onclick="veriCekVeGoster('kaski_su_kesinti')" id="btn-hizmet-kaski_su_kesinti" class="portal-filter-btn inactive-service hidden w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-cyan-50 transition-all flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <img src="assets/icons/kaski.png" class="w-7 h-7 object-contain shrink-0">
                    <span class="font-bold text-[13px] text-gray-800">Su Kesintileri</span>
                </div>
                <div class="w-5 h-5 bg-white rounded-full border border-cyan-500 shadow-sm shrink-0 flex items-center justify-center"><img src="assets/icons/kaski.png" class="w-3.5 h-3.5 object-contain"></div>
            </button>

            <button onclick="veriCekVeGoster('sarj_istasyonlari')" id="btn-hizmet-sarj_istasyonlari" class="portal-filter-btn inactive-service hidden w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-amber-50 transition-all flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-amber-500 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                        <i class="fa-solid fa-charging-station text-xs"></i>
                    </div>
                    <span class="font-bold text-[13px] text-gray-800">Elektrikli Şarj Noktaları</span>
                </div>
                <div class="w-5 h-5 bg-amber-500 rounded-full border border-white shadow-sm shrink-0 flex items-center justify-center text-white"><i class="fa-solid fa-charging-station text-[9px]"></i></div>
            </button>

            <button onclick="veriCekVeGoster('spor_tesisleri')" id="btn-hizmet-spor_tesisleri" class="portal-filter-btn inactive-service hidden w-full px-4 py-2 bg-white rounded-full shadow-sm border-2 border-transparent hover:bg-indigo-50 transition-all flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-indigo-500 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                        <i class="fa-solid fa-volleyball text-xs"></i>
                    </div>
                    <span class="font-bold text-[13px] text-gray-800">Spor Tesisleri ve Parklar</span>
                </div>
                <div class="w-5 h-5 bg-indigo-500 rounded-full border border-white shadow-sm shrink-0 flex items-center justify-center text-white"><i class="fa-solid fa-volleyball text-[9px]"></i></div>
            </button>
        </div>

        <div class="p-3.5 bg-white rounded-2xl shadow-lg border border-gray-200 pointer-events-auto shrink-0">
            <div class="flex items-center justify-between text-xs font-bold text-gray-700 mb-1">
                <span id="weather-widget"><i class="fa-solid fa-spinner fa-spin text-gray-400 mr-1"></i> Yükleniyor...</span>
                <span id="marker-counter-badge" class="hidden items-center gap-1 bg-blue-100 text-blue-700 px-2 py-0.5 rounded shadow-sm text-[10px]"><i class="fa-solid fa-map-location-dot"></i> <span id="marker-count">0</span> Konum</span>
            </div>
            <div class="flex justify-between items-end mt-1.5">
                <div class="text-[10px] text-gray-400 italic" id="weather-source"></div>
                <div class="text-[10px] text-gray-500 font-medium text-right" id="active-source"></div>
            </div>
        </div>

    </div>
    
    <div class="relative h-full z-10 pointer-events-none py-4">
        
        <div id="vefat-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-300 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-zinc-800 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold"><i class="fa-solid fa-ribbon mr-2"></i>Vefat İlanları</h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-gray-300 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-zinc-100 border-b border-zinc-200 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-vefat" oninput="listeAra('ara-vefat', 'vefat-list')" placeholder="İsim soyisim ara..." class="w-full bg-white border border-zinc-300 text-sm rounded-xl focus:ring-zinc-500 focus:border-zinc-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-zinc-500"></i></div>
                </div>
            </div>
            <div id="vefat-list" class="flex-1 overflow-y-auto p-0 scrollbar-hide"></div>
        </div>

        <div id="muhtar-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-300 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-teal-600 text-white shrink-0">
                <div class="flex justify-between items-center">
                    <h2 class="text-base font-bold"><i class="fa-solid fa-users mr-2"></i>Mahalle/Muhtarlık Bilgileri</h2>
                    <button onclick="kapatTumPaneller()" class="text-white hover:text-teal-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
                </div>
                <p class="text-[10px] text-teal-100 mt-1.5 leading-snug bg-teal-700/60 px-2.5 py-1.5 rounded-lg border border-teal-500/40">
                    <i class="fa-solid fa-circle-info mr-1"></i>Mevcut olmayan muhtarlık bilgileri ilgili kurumun veriyi yayınlamamasından kaynaklıdır.
                </p>
            </div>
            <div class="p-3 bg-teal-50 border-b border-teal-100 shrink-0 space-y-2">
                <select id="sec-muhtar-ilce" onchange="muhtarFiltrele()" class="w-full bg-white border border-teal-200 text-xs font-bold text-gray-700 rounded-xl p-2 outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer">
                    <option value="">Tüm İlçeler (711 Mahalle)</option>
                    <option value="MELİKGAZİ">Melikgazi İlçesi</option>
                    <option value="KOCASİNAN">Kocasinan İlçesi</option>
                    <option value="TALAS">Talas İlçesi</option>
                    <option value="DEVELİ">Develi İlçesi</option>
                    <option value="YAHYALI">Yahyalı İlçesi</option>
                    <option value="BÜNYAN">Bünyan İlçesi</option>
                    <option value="İNCESU">İncesu İlçesi</option>
                    <option value="PINARBAŞI">Pınarbaşı İlçesi</option>
                    <option value="TOMARZA">Tomarza İlçesi</option>
                    <option value="YEŞİLHİSAR">Yeşilhisar İlçesi</option>
                    <option value="SARIOĞLAN">Sarıoğlan İlçesi</option>
                    <option value="HACILAR">Hacılar İlçesi</option>
                    <option value="SARIZ">Sarız İlçesi</option>
                    <option value="AKKIŞLA">Akkışla İlçesi</option>
                    <option value="FELAHİYE">Felahiye İlçesi</option>
                    <option value="ÖZVATAN">Özvatan İlçesi</option>
                </select>
                <div class="relative">
                    <input type="text" id="ara-muhtar" oninput="muhtarFiltrele()" placeholder="Mahalle veya muhtar ara..." class="w-full bg-white border border-teal-200 text-sm rounded-xl focus:ring-teal-500 focus:border-teal-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-teal-400"></i></div>
                </div>
            </div>
            <div id="muhtar-list" class="flex-1 overflow-y-auto p-0 scrollbar-hide bg-gray-50/50"></div>
        </div>

        <div id="cadde-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-300 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-indigo-600 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold"><i class="fa-solid fa-road mr-2"></i>İlçe İstatistikleri</h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-indigo-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-indigo-50 border-b border-indigo-100 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-cadde" oninput="listeAra('ara-cadde', 'cadde-list')" placeholder="İlçe adı ara (Örn: Melikgazi)..." class="w-full bg-white border border-indigo-200 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-indigo-400"></i></div>
                </div>
            </div>
            <div id="cadde-list" class="flex-1 overflow-y-auto p-0 scrollbar-hide bg-gray-50/50"></div>
        </div>

        <div id="hal-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-300 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-orange-500 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold flex items-center">
                    <div class="w-7 h-7 bg-white rounded-full p-0.5 mr-2 flex items-center justify-center shrink-0 shadow-sm">
                        <img src="assets/icons/hal.png" onerror="this.onerror=null; this.src='assets/icons/kbb.png';" class="w-full h-full object-contain rounded-full">
                    </div>
                    Hal Fiyatları
                </h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-orange-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-orange-50 border-b border-orange-100 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-hal" oninput="listeAra('ara-hal', 'hal-list')" placeholder="Ürün adı ara..." class="w-full bg-white border border-orange-200 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-orange-400"></i></div>
                </div>
            </div>
            <div id="hal-list" class="flex-1 overflow-y-auto p-0 scrollbar-hide"></div>
        </div>

        <div id="quake-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-300 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-purple-600 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold"><i class="fa-solid fa-house-crack mr-2"></i>Deprem Listesi</h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-gray-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-purple-50 border-b border-purple-100 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-deprem" oninput="listeAra('ara-deprem', 'quake-list')" placeholder="İl veya bölge ara (Örn: Muğla)..." class="w-full bg-white border border-purple-200 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-purple-400"></i></div>
                </div>
            </div>
            <div id="quake-list" class="flex-1 overflow-y-auto p-0 scrollbar-hide"></div>
        </div>
        
        <div id="afet-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-300 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-emerald-600 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold flex items-center">
                    <div class="w-7 h-7 bg-white rounded-full p-0.5 mr-2 flex items-center justify-center shrink-0 shadow-sm">
                        <img src="assets/icons/afad2.png" class="w-full h-full object-contain">
                    </div>
                    Toplanma Alanları
                </h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-gray-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-emerald-50 border-b border-emerald-100 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-afet" oninput="listeAra('ara-afet', 'afet-list')" placeholder="Mahalle veya alan ara..." class="w-full bg-white border border-emerald-200 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-emerald-400"></i></div>
                </div>
            </div>
            <div id="afet-list" class="flex-1 overflow-y-auto p-0 scrollbar-hide"></div>
        </div>
        
        <div id="muze-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-300 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-rose-600 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold"><i class="fa-solid fa-building-columns mr-2"></i>Tarihi Yerler</h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-gray-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-rose-50 border-b border-rose-100 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-muze" oninput="listeAra('ara-muze', 'muze-list')" placeholder="Müze veya eser ara..." class="w-full bg-white border border-rose-200 text-sm rounded-xl focus:ring-rose-500 focus:border-rose-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-rose-400"></i></div>
                </div>
            </div>
            <div id="muze-list" class="flex-1 overflow-y-auto p-0 bg-gray-50/50 scrollbar-hide"></div>
        </div>

        <div id="otopark-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-300 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-gray-800 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold"><i class="fa-solid fa-square-parking mr-2"></i>Otoparklar</h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-gray-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-gray-100 border-b border-gray-200 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-otopark" oninput="listeAra('ara-otopark', 'otopark-list')" placeholder="Otopark ara..." class="w-full bg-white border border-gray-300 text-sm rounded-xl focus:ring-gray-500 focus:border-gray-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-gray-500"></i></div>
                </div>
            </div>
            <div id="otopark-list" class="flex-1 overflow-y-auto p-0 bg-gray-50/50 scrollbar-hide"></div>
        </div>

        <div id="pazar-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-300 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-sky-500 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold"><i class="fa-solid fa-basket-shopping mr-2"></i>Semt Pazarları</h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-gray-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-sky-50 border-b border-sky-100 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-pazar" oninput="listeAra('ara-pazar', 'pazar-list')" placeholder="Pazar yeri veya gün ara..." class="w-full bg-white border border-sky-200 text-sm rounded-xl focus:ring-sky-500 focus:border-sky-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-sky-400"></i></div>
                </div>
            </div>
            <div id="pazar-list" class="flex-1 overflow-y-auto p-0 bg-gray-50/50 scrollbar-hide"></div>
        </div>
        
        <div id="eczane-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-300 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-red-600 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold flex items-center">
                    <div class="w-7 h-7 bg-white rounded-full p-0.5 mr-2 flex items-center justify-center shrink-0 shadow-sm">
                        <img src="assets/icons/eczane.png" class="w-full h-full object-contain">
                    </div>
                    Eczaneler
                </h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-gray-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-red-50 border-b border-red-100 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-eczane" oninput="listeAra('ara-eczane', 'eczane-list')" placeholder="Eczane ara..." class="w-full bg-white border border-red-200 text-sm rounded-xl focus:ring-red-500 focus:border-red-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-red-400"></i></div>
                </div>
            </div>
            <div id="eczane-list" class="flex-1 overflow-y-auto p-0 bg-gray-50/50 scrollbar-hide"></div>
        </div>

        <div id="wifi-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-300 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-green-500 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold"><i class="fa-solid fa-wifi mr-2"></i>Ücretsiz Wi-Fi</h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-gray-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-green-50 border-b border-green-100 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-wifi" oninput="listeAra('ara-wifi', 'wifi-list')" placeholder="Wi-Fi noktası ara..." class="w-full bg-white border border-green-200 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-green-400"></i></div>
                </div>
            </div>
            <div id="wifi-list" class="flex-1 overflow-y-auto p-0 bg-gray-50/50 scrollbar-hide"></div>
        </div>

        <div id="kamera-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-300 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-slate-700 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold"><i class="fa-solid fa-video mr-2"></i>Şehir Kameraları</h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-gray-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-slate-100 border-b border-slate-200 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-kamera" oninput="listeAra('ara-kamera', 'kamera-list')" placeholder="Kamera ara..." class="w-full bg-white border border-slate-300 text-sm rounded-xl focus:ring-slate-500 focus:border-slate-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-slate-500"></i></div>
                </div>
            </div>
            <div id="kamera-list" class="flex-1 overflow-y-auto p-0 bg-gray-50/50 scrollbar-hide"></div>
        </div>

    </div>
</div>

<div id="toast-panel" class="fixed bottom-5 right-5 transform translate-y-20 opacity-0 transition-all duration-500 z-50 pointer-events-none"></div>
<div id="map"></div>

<button onclick="document.getElementById('main-sidebar').classList.toggle('-translate-x-full')" class="absolute top-4 right-4 z-30 bg-white p-3 rounded-full shadow-lg lg:hidden pointer-events-auto">
    <i class="fa-solid fa-bars text-gray-800"></i>
</button>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var canvasRenderer = L.canvas({ padding: 0.5 });
var map = L.map('map', {
    zoomControl: false,
    preferCanvas: true,
    renderer: canvasRenderer,
    wheelDebounceTime: 15,
    wheelPxPerZoomLevel: 35,
    zoomSnap: 1,
    zoomDelta: 1
}).setView([38.7205, 35.4826], 12);

L.control.zoom({position: 'bottomright'}).addTo(map);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    keepBuffer: 6,
    updateWhenZooming: false,
    updateWhenIdle: false,
    attribution: '© OpenStreetMap'
}).addTo(map);

setTimeout(() => { map.invalidateSize(); }, 100);
window.addEventListener('resize', () => { map.invalidateSize(); });

var markerGroup = L.layerGroup().addTo(map);
var aramaMarker = null;
var ilceSinirKatmani = null;
var mahalleMarker = null;
var sinirCache = {};
var currentBoundaryReqId = 0;
var currentRequestId = 0;

const ilceMerkezKoordinatlari = {
    'AKKIŞLA':    [39.0044, 36.1756],
    'BÜNYAN':     [38.8464, 35.8603],
    'DEVELİ':     [38.3892, 35.4922],
    'FELAHİYE':   [39.0906, 35.5672],
    'HACILAR':    [38.6439, 35.4500],
    'İNCESU':     [38.6250, 35.1908],
    'KOCASİNAN':  [38.7450, 35.4750],
    'MELİKGAZİ':  [38.7180, 35.5000],
    'ÖZVATAN':    [39.1128, 35.7100],
    'PINARBAŞI':  [38.7219, 36.3919],
    'SARIOĞLAN':  [39.0767, 36.0669],
    'SARIZ':      [38.4792, 36.4986],
    'TALAS':      [38.6908, 35.5539],
    'TOMARZA':    [38.4458, 35.7989],
    'YAHYALI':    [38.1025, 35.3578],
    'YEŞİLHİSAR': [38.3569, 35.0889]
};

const ilceDetayBilgileri = {
    'AKKIŞLA':    { alan_m2: '414,764,645',   alan_km2: '414.76',  mahalle_sayisi: 15,  nufus_2024: '5,738',   nufus_2025: '5,440',   degisim: '-5.33' },
    'BÜNYAN':     { alan_m2: '1,220,668,566', alan_km2: '1220.67', mahalle_sayisi: 46,  nufus_2024: '30,486',  nufus_2025: '30,362',  degisim: '-0.41' },
    'DEVELİ':     { alan_m2: '1,920,380,208', alan_km2: '1920.38', mahalle_sayisi: 77,  nufus_2024: '67,676',  nufus_2025: '67,818',  degisim: '0.21' },
    'FELAHİYE':   { alan_m2: '442,636,925',   alan_km2: '442.64',  mahalle_sayisi: 17,  nufus_2024: '5,781',   nufus_2025: '5,529',   degisim: '-4.46' },
    'HACILAR':    { alan_m2: '164,503,316',   alan_km2: '164.50',  mahalle_sayisi: 12,  nufus_2024: '13,270',  nufus_2025: '13,701',  degisim: '3.20' },
    'İNCESU':     { alan_m2: '808,093,412',   alan_km2: '808.09',  mahalle_sayisi: 30,  nufus_2024: '30,181',  nufus_2025: '29,780',  degisim: '-1.34' },
    'KOCASİNAN':  { alan_m2: '1,569,790,866', alan_km2: '1556.39', mahalle_sayisi: 88,  nufus_2024: '416,124', nufus_2025: '421,472', degisim: '1.28' },
    'MELİKGAZİ':  { alan_m2: '599,007,401',   alan_km2: '599.01',  mahalle_sayisi: 58,  nufus_2024: '589,989', nufus_2025: '593,262', degisim: '0.55' },
    'ÖZVATAN':    { alan_m2: '236,965,343',   alan_km2: '236.97',  mahalle_sayisi: 13,  nufus_2024: '4,067',   nufus_2025: '3,877',   degisim: '-4.78' },
    'PINARBAŞI':  { alan_m2: '3,415,153,836', alan_km2: '3415.15', mahalle_sayisi: 122, nufus_2024: '21,567',  nufus_2025: '20,598',  degisim: '-4.60' },
    'SARIOĞLAN':  { alan_m2: '703,412,124',   alan_km2: '703.41',  mahalle_sayisi: 28,  nufus_2024: '13,943',  nufus_2025: '13,510',  degisim: '-3.15' },
    'SARIZ':      { alan_m2: '1,203,987,952', alan_km2: '1203.99', mahalle_sayisi: 44,  nufus_2024: '9,853',   nufus_2025: '9,213',   degisim: '-6.72' },
    'TALAS':      { alan_m2: '445,132,449',   alan_km2: '445.13',  mahalle_sayisi: 30,  nufus_2024: '171,689', nufus_2025: '173,657', degisim: '1.14' },
    'TOMARZA':    { alan_m2: '1,392,958,623', alan_km2: '1392.96', mahalle_sayisi: 54,  nufus_2024: '20,832',  nufus_2025: '20,156',  degisim: '-3.30' },
    'YAHYALI':    { alan_m2: '1,798,168,264', alan_km2: '1798.17', mahalle_sayisi: 42,  nufus_2024: '35,588',  nufus_2025: '35,207',  degisim: '-1.08' },
    'YEŞİLHİSAR': { alan_m2: '877,764,742',   alan_km2: '877.76',  mahalle_sayisi: 35,  nufus_2024: '15,674',  nufus_2025: '15,409',  degisim: '-1.71' },
    'TOPLAM':     { alan_m2: '17,213,388,672',alan_km2: '17213.00',mahalle_sayisi: 711, nufus_2024: '1,452,458',nufus_2025: '1,458,991',degisim: '0.45' }
};

const ilceCaddeSokakBilgileri = {
    'AKKIŞLA':    { cadde: '23',    sokak: '315',    bulvar: '0',  meydan: '0',  kume: '15',  toplam: '353' },
    'BÜNYAN':     { cadde: '87',    sokak: '600',    bulvar: '4',  meydan: '3',  kume: '70',  toplam: '764' },
    'DEVELİ':     { cadde: '139',   sokak: '1,235',  bulvar: '2',  meydan: '4',  kume: '83',  toplam: '1,463' },
    'FELAHİYE':   { cadde: '44',    sokak: '248',    bulvar: '1',  meydan: '1',  kume: '18',  toplam: '312' },
    'HACILAR':    { cadde: '73',    sokak: '427',    bulvar: '0',  meydan: '1',  kume: '4',   toplam: '505' },
    'İNCESU':     { cadde: '79',    sokak: '979',    bulvar: '9',  meydan: '0',  kume: '51',  toplam: '1,118' },
    'KOCASİNAN':  { cadde: '360',   sokak: '4,011',  bulvar: '15', meydan: '2',  kume: '100', toplam: '4,488' },
    'MELİKGAZİ':  { cadde: '462',   sokak: '3,843',  bulvar: '33', meydan: '3',  kume: '27',  toplam: '4,368' },
    'ÖZVATAN':    { cadde: '57',    sokak: '165',    bulvar: '0',  meydan: '3',  kume: '19',  toplam: '244' },
    'PINARBAŞI':  { cadde: '41',    sokak: '447',    bulvar: '6',  meydan: '0',  kume: '151', toplam: '645' },
    'SARIOĞLAN':  { cadde: '68',    sokak: '569',    bulvar: '0',  meydan: '1',  kume: '45',  toplam: '683' },
    'SARIZ':      { cadde: '20',    sokak: '136',    bulvar: '0',  meydan: '0',  kume: '51',  toplam: '207' },
    'TALAS':      { cadde: '165',   sokak: '1,271',  bulvar: '6',  meydan: '2',  kume: '32',  toplam: '1,476' },
    'TOMARZA':    { cadde: '23',    sokak: '407',    bulvar: '2',  meydan: '0',  kume: '64',  toplam: '496' },
    'YAHYALI':    { cadde: '47',    sokak: '672',    bulvar: '1',  meydan: '3',  kume: '62',  toplam: '785' },
    'YEŞİLHİSAR': { cadde: '26',    sokak: '258',    bulvar: '0',  meydan: '1',  kume: '30',  toplam: '315' },
    'TOPLAM':     { cadde: '1,714', sokak: '15,583', bulvar: '79', meydan: '24', kume: '822', toplam: '18,222' }
};

function ilceSiniriniTemizle() {
    currentBoundaryReqId++;
    if (ilceSinirKatmani) {
        map.removeLayer(ilceSinirKatmani);
        ilceSinirKatmani = null;
    }
    if (mahalleMarker) {
        map.removeLayer(mahalleMarker);
        mahalleMarker = null;
    }
}

function bolgeSiniriniGoster(ilceKey, mahalleRaw = null, renk = '#4f46e5') {
    let normIlce = normalizeIlceKey(ilceKey);
    if (!normIlce || normIlce === 'TOPLAM') return;

    ilceSiniriniTemizle();
    const bReqId = currentBoundaryReqId;

    let ilceTitle = toTitleCase(normIlce);
    let mahalleTitle = mahalleRaw ? toTitleCase(mahalleRaw) : null;
    let det = ilceDetayBilgileri[normIlce] || {};
    let cs = ilceCaddeSokakBilgileri[normIlce] || {};

    let popupHtml = `
        <div style="width: 220px;" class="p-2.5 text-center box-border">
            <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full text-white mb-1" style="background-color: ${renk};">
                <i class="fa-solid fa-draw-polygon mr-1"></i>${mahalleTitle ? 'Mahalle / İlçe Konumu' : 'İlçe İdari Sınırı'}
            </span>
            <h3 class="font-extrabold text-[15px] text-gray-800 leading-tight">${mahalleTitle ? mahalleTitle + ' Mah.' : ilceTitle + ' İlçesi'}</h3>
            ${mahalleTitle ? `<div class="text-[11px] font-bold text-teal-600 mt-0.5">${ilceTitle} İlçesi</div>` : ''}
            ${det.alan_km2 ? `
            <div class="mt-2 pt-2 border-t border-gray-100 grid grid-cols-2 gap-1 text-[10px] text-gray-600">
                <div class="bg-gray-50 p-1 rounded">İlçe Alanı: <b class="text-gray-800">${det.alan_km2} km²</b></div>
                <div class="bg-gray-50 p-1 rounded">İlçe Nüfusu: <b class="text-gray-800">${det.nufus_2025}</b></div>
                <div class="bg-gray-50 p-1 rounded">Mahalle: <b class="text-gray-800">${det.mahalle_sayisi}</b></div>
                <div class="bg-gray-50 p-1 rounded">Cadde/Sokak: <b class="text-gray-800">${cs.toplam || '-'}</b></div>
            </div>` : ''}
        </div>
    `;

    let paddingOpts = window.innerWidth >= 1024 
        ? { paddingTopLeft: [650, 50], paddingBottomRight: [50, 50], maxZoom: mahalleTitle ? 14 : 12, duration: 0.85 }
        : { padding: [30, 30], maxZoom: mahalleTitle ? 14 : 12, duration: 0.85 };

    let ucurVeCiz = (itemData) => {
        if (bReqId !== currentBoundaryReqId) return;
        map.stop();

        if (itemData.type === 'polygon') {
            let tempLayer = L.geoJSON(itemData.geojson);
            let bounds = tempLayer.getBounds();
            if (bounds.isValid()) {
                let cizimYap = () => {
                    if (bReqId !== currentBoundaryReqId) return;
                    if (ilceSinirKatmani) map.removeLayer(ilceSinirKatmani);
                    ilceSinirKatmani = L.geoJSON(itemData.geojson, {
                        renderer: canvasRenderer,
                        style: {
                            color: renk,
                            weight: 3,
                            opacity: 0.9,
                            fillColor: renk,
                            fillOpacity: 0.15,
                            smoothFactor: 2.5
                        }
                    }).addTo(map);
                    ilceSinirKatmani.bindPopup(popupHtml, { className: 'minimal-popup', closeButton: false }).openPopup(bounds.getCenter());
                };
                map.once('moveend', cizimYap);
                map.flyToBounds(bounds, paddingOpts);
            }
        } else if (itemData.type === 'point') {
            let latlng = [itemData.lat, itemData.lon];
            let cizimYap = () => {
                if (bReqId !== currentBoundaryReqId) return;
                if (ilceSinirKatmani) map.removeLayer(ilceSinirKatmani);
                ilceSinirKatmani = L.circle(latlng, {
                    renderer: canvasRenderer,
                    radius: itemData.radius || 1200,
                    color: renk,
                    weight: 3,
                    fillColor: renk,
                    fillOpacity: 0.18
                }).addTo(map);
                ilceSinirKatmani.bindPopup(popupHtml, { className: 'minimal-popup', closeButton: false }).openPopup();
            };
            map.once('moveend', cizimYap);
            map.flyTo(latlng, mahalleTitle ? 14 : 11, { duration: 0.85 });
        }
    };

    let cacheKey = mahalleTitle ? `${normIlce}_${mahalleTitle}` : `ILCE_${normIlce}`;
    if (sinirCache[cacheKey]) {
        ucurVeCiz(sinirCache[cacheKey]);
        return;
    }

    let sorgu = mahalleTitle 
        ? `${mahalleTitle}, ${ilceTitle}, Kayseri, Türkiye`
        : `${ilceTitle}, Kayseri, Türkiye`;

    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(sorgu)}&polygon_geojson=1&limit=3`, {
        headers: { 'Accept-Language': 'tr' }
    })
    .then(r => r.json())
    .then(results => {
        if (bReqId !== currentBoundaryReqId) return;
        let polyItem = results.find(item => item.geojson && (item.geojson.type === 'Polygon' || item.geojson.type === 'MultiPolygon'));
        
        if (polyItem && polyItem.geojson) {
            sinirCache[cacheKey] = { type: 'polygon', geojson: polyItem.geojson };
            ucurVeCiz(sinirCache[cacheKey]);
        } else if (results.length > 0 && results[0].lat && results[0].lon) {
            sinirCache[cacheKey] = { type: 'point', lat: parseFloat(results[0].lat), lon: parseFloat(results[0].lon), radius: mahalleTitle ? 900 : 5000 };
            ucurVeCiz(sinirCache[cacheKey]);
        } else if (mahalleTitle) {
            bolgeSiniriniGoster(normIlce, null, renk);
        } else {
            let merkez = ilceMerkezKoordinatlari[normIlce];
            if (merkez) {
                sinirCache[cacheKey] = { type: 'point', lat: merkez[0], lon: merkez[1], radius: 6000 };
                ucurVeCiz(sinirCache[cacheKey]);
            }
        }
    })
    .catch(() => {
        let merkez = ilceMerkezKoordinatlari[normIlce];
        if (merkez) {
            ucurVeCiz({ type: 'point', lat: merkez[0], lon: merkez[1], radius: 5000 });
        }
    });
}

function normalizeIlceKey(str) {
    if (!str) return "";
    let s = str.toString().trim().toLocaleUpperCase('tr-TR');
    return turkceKarakterDuzelt(s);
}

function normalizeMetin(str) {
    if (!str) return "";
    return str.toLocaleLowerCase('tr-TR')
        .replace(/ğ/g, 'g').replace(/ü/g, 'u').replace(/ş/g, 's')
        .replace(/ı/g, 'i').replace(/ö/g, 'o').replace(/ç/g, 'c')
        .replace(/â/g, 'a').replace(/î/g, 'i').replace(/û/g, 'u');
}

function turkceKarakterDuzelt(str) {
    if (!str) return "";
    const sozluk = {
        'MUGLA': 'MUĞLA', 'KUTAHYA': 'KÜTAHYA', 'CANAKKALE': 'ÇANAKKALE',
        'KAHRAMANMARAS': 'KAHRAMANMARAŞ', 'ELAZIG': 'ELAZIĞ', 'ADIYAMAN': 'ADIYAMAN',
        'BALIKESIR': 'BALIKESİR', 'IZMIR': 'İZMİR', 'ISTANBUL': 'İSTANBUL',
        'DIYARBAKIR': 'DİYARBAKIR', 'GAZIANTEP': 'GAZİANTEP', 'SANLIURFA': 'ŞANLIURFA',
        'SIRNAK': 'ŞIRNAK', 'HAKKARI': 'HAKKARİ', 'BINGOL': 'BİNGÖL', 'MUS': 'MUŞ',
        'AGRI': 'AĞRI', 'IGDIR': 'IĞDIR', 'GUMUSHANE': 'GÜMÜŞHANE', ' DUZCE': ' DÜZCE',
        'TEKIRDAG': 'TEKİRDAĞ', 'KIRKLARELI': 'KIRKLARELİ', 'ESKISEHIR': 'ESKİŞEHİR',
        ' CANKIRI': ' ÇANKIRI', 'CORUM': 'ÇORUM', 'KARABUK': 'KARABÜK', 'BARTIN': 'BARTIN',
        'ZONGULDAK': 'ZONGULDAK', 'KOCAELI': 'KOCAELİ', 'YALOVA': 'YALOVA',
        'BILECIK': 'BİLECİK', 'USAK': 'UŞAK', 'AYDIN': 'AYDIN', 'DENİZLİ': 'DENİZLİ',
        'ISPARTA': 'ISPARTA', 'ANTALYA': 'ANTALYA', 'ICEL': 'İÇEL', 'MERSIN': 'MERSİN',
        'NIGDE': 'NİĞDE', 'NEVSEHIR': 'NEVŞEHİR', 'KIRSEHIR': 'KIRŞEHİR',
        'KIRIKKALE': 'KIRIKKALE', 'KAYSERI': 'KAYSERİ', 'SIVAS': 'SİVAS',
        'ERZINCAN': 'ERZİNCAN', 'TUNCELI': 'TUNCELİ', 'MALATYA': 'MALATYA',
        'OSMANIYE': 'OSMANİYE', 'KILIS': 'KİLİS', 'MARDIN': 'MARDİN', 'SIIRT': 'SİİRT',
        'BATMAN': 'BATMAN', 'BITLIS': 'BİTLİS', 'GIRESUN': 'GİRESUN', 'RIZE': 'RİZE',
        'ARTVIN': 'ARTVİN', 'GOZTEPE': 'GÖZTEPE', 'GOLBASI': 'GÖLBAŞI', 'KOYCEGIZ': 'KÖYCEĞİZ',
        'PUTURGE': 'PÜTÜRGE', 'GOKSUN': 'GÖKSUN', 'DOGANSEHIR': 'DOĞANŞEHİR',
        'YESILYURT': 'YEŞİLYURT', 'AKCADAG': 'AKÇADAĞ', 'CELIKHAN': 'ÇELİKHAN',
        'SINDIRGI': 'SINDIRGI', 'KIRKAGAC': 'KIRKAĞAÇ', 'GORDES': 'GÖRDES',
        'AKHISAR': 'AKHİSAR', 'SARIGOL': 'SARIGÖL', 'ALASEHIR': 'ALAŞEHİR',
        'KUSADASI': 'KUŞADASI', 'DIDIM': 'DİDİM', 'BODRUM': 'BODRUM', 'DATCA': 'DATÇA',
        'MARMARIS': 'MARMARİS', 'FETHIYE': 'FETHİYE', 'KEMER': 'KEMER', 'KAS': 'KAŞ',
        'ALANYA': 'ALANYA', 'GAZIPASA': 'GAZİPAŞA', 'ANAMUR': 'ANAMUR', 'SILIFKE': 'SİLİFKE',
        'ISKENDERUN': 'İSKENDERUN', 'ANTAKYA': 'ANTAKYA', 'SAMANDAG': 'SAMANDAĞ',
        'KIRIKHAN': 'KIRIKHAN', 'ISLAHIYE': 'İSLAHİYE', 'NURDAGI': 'NURDAĞI',
        'PAZARCIK': 'PAZARCIK', 'ELBISTAN': 'ELBİSTAN', 'AFSIN': 'AFŞİN',
        'ANDIRIN': 'ANDIRIN', 'TURKOGLU': 'TÜRKOĞLU', 'GOKCEADA': 'GÖKÇEADA',
        'MARMARA DENIZI': 'MARMARA DENİZİ', 'EGE DENIZI': 'EGE DENİZİ',
        'AKDENIZ': 'AKDENİZ', 'KARADENIZ': 'KARADENİZ', 'ACIKLARI': 'AÇIKLARI',
        'KORFEZI': 'KÖRFEZİ', 'ADASI': 'ADASI', 'ADALARI': 'ADALARI',
        'AKKISLA': 'AKKIŞLA', 'BUNYAN': 'BÜNYAN', 'DEVELI': 'DEVELİ',
        'FELAHIYE': 'FELAHİYE', 'INCESU': 'İNCESU', 'KOCASINAN': 'KOCASİNAN',
        'MELIKGAZI': 'MELİKGAZİ', 'OZVATAN': 'ÖZVATAN', 'PINARBASI': 'PINARBAŞI',
        'SARIOGLAN': 'SARIOĞLAN', 'YESILHISAR': 'YEŞİLHİSAR'
    };
    let s = str.toUpperCase();
    for (let k in sozluk) {
        s = s.split(k).join(sozluk[k]);
    }
    return s;
}

function toTitleCase(str) {
    if (!str) return "";
    let duzeltilmis = turkceKarakterDuzelt(str);
    return duzeltilmis.toLocaleLowerCase('tr-TR').replace(/(?:^|\s|-|\/|\(|\[)\S/g, function(match) {
        return match.toLocaleUpperCase('tr-TR');
    });
}

function duzenleMetin(metin) {
    if (!metin) return "";
    let formatli = metin.replace(/([a-zğüşöçı])([A-ZĞÜŞÖÇİ])/g, "$1 $2"); 
    formatli = formatli.replace(/([.:;,!])([A-Za-zĞÜŞÖÇİğüşöçı])/g, "$1 $2"); 
    return formatli.replace(/\s+/g, ' ').trim();
}

function updateMarkerCount() {
    let count = markerGroup.getLayers().length;
    let badge = document.getElementById('marker-counter-badge');
    let countSpan = document.getElementById('marker-count');
    if (badge && countSpan) {
        if (count > 0) {
            countSpan.innerText = count;
            badge.classList.remove('hidden');
            badge.classList.add('flex');
        } else {
            badge.classList.add('hidden');
            badge.classList.remove('flex');
        }
    }
}

function animasyonluMarkerEkle(lat, lng, iconHtml, customId, popupIcerik) {
    let animIcon = L.divIcon({ html: iconHtml, className: '', iconSize: [36, 36], iconAnchor: [18, 18] });
    let marker = L.marker([lat, lng], {icon: animIcon, customId: customId});
    if (popupIcerik) {
        marker.bindPopup(popupIcerik, {className: 'minimal-popup', closeButton: false, offset: [0, -5], maxWidth: 280});
    }
    marker.addTo(markerGroup);
    updateMarkerCount();
    return marker;
}

function akiciGecisYap(hedefKoordinat, hedefZoom, callback) {
    map.stop();
    let mevcutZoom = map.getZoom();
    let mesafe = map.getCenter().distanceTo(L.latLng(hedefKoordinat[0], hedefKoordinat[1]));

    if (Math.abs(mevcutZoom - hedefZoom) > 2 || mesafe > 35000) {
        let tamamlandi = false;
        let bitir = () => {
            if (!tamamlandi) {
                tamamlandi = true;
                map.off('moveend', bitir);
                callback();
            }
        };
        map.once('moveend', bitir);
        setTimeout(bitir, 950);
        map.flyTo(hedefKoordinat, hedefZoom, { duration: 0.85, easeLinearity: 0.25 });
    } else {
        map.flyTo(hedefKoordinat, hedefZoom, { duration: 0.5 });
        callback();
    }
}

function kaynakGuncelle(endpoint) {
    let isListOnly = ['hal'].includes(endpoint);
    let kaynak = "";
    
    if (!isListOnly) {
        if(endpoint === 'tumu') kaynak = "Tüm Açık Veri Kaynakları";
        else if(endpoint === 'muhtar' || endpoint === 'cadde_sokak') kaynak = "Kayseri BB CBS & OSM";
        else if(endpoint === 'kaybis' || endpoint === 'kaski_su_kesinti' || endpoint === 'sarj_istasyonlari' || endpoint === 'spor_tesisleri') kaynak = "Veri Sağlanamıyor";
        else if(endpoint === 'kameralar') kaynak = "Erciyes A.Ş. & Kayseri BB";
        else if(endpoint === 'deprem') kaynak = "Kandilli Rasathanesi API";
        else if(endpoint === 'toplanma_alanlari') kaynak = "AFAD & Açık Veri Portalı";
        else if(endpoint === 'gezilecek_yerler') kaynak = "İl Kültür Turizm Müd.";
        else if(endpoint === 'pazar_yerleri' || endpoint === 'otoparklar' || endpoint === 'nobetci_eczane' || endpoint === 'wifi_noktalari' || endpoint === 'vefat') kaynak = "Kayseri BB Açık Veri";
        
        document.getElementById('active-source').innerText = "Harita: " + kaynak;
    }

    document.querySelectorAll('.portal-filter-btn').forEach(btn => {
        btn.classList.remove('border-blue-500', 'bg-blue-50/95', 'ring-2', 'ring-blue-500/30');
        btn.classList.add('border-transparent');
    });
    let aktifBtn = document.getElementById('btn-hizmet-' + endpoint);
    if (aktifBtn) {
        aktifBtn.classList.remove('border-transparent');
        aktifBtn.classList.add('border-blue-500', 'bg-blue-50/95', 'ring-2', 'ring-blue-500/30');
    }
}

function secimleriSifirla() {
    document.querySelectorAll('.custom-marker-icon, .quake-marker-icon').forEach(icon => {
        icon.classList.remove('border-yellow-500', 'border-4', 'scale-125', 'z-50');
        if (icon.dataset.defaultBorder) {
            icon.classList.add(icon.dataset.defaultBorder, 'border-2');
        } else {
            icon.classList.add('border-white', 'border-2');
        }
    });
    document.querySelectorAll('.list-item').forEach(item => {
        if (!item.classList.contains('muhtar-item') && !item.classList.contains('cadde-item')) {
            item.classList.remove('bg-yellow-100', 'border-l-4', 'border-yellow-500');
            item.classList.add('border-b', 'border-gray-200', 'bg-white');
        }
    });
}
map.on('popupclose', secimleriSifirla);
map.on('click', secimleriSifirla);

window.aktifListeOgesi = function(divId, markerLat, markerLng, zoomLvl) {
    secimleriSifirla();
    let div = document.getElementById(divId);
    if(div) {
        div.classList.remove('border-b', 'border-gray-200', 'bg-white'); 
        div.classList.add('bg-yellow-100', 'border-l-4', 'border-yellow-500');
        div.scrollIntoView({ behavior: 'auto', block: 'nearest' });
    }
    markerGroup.eachLayer(function(marker) {
        let latlng = marker.getLatLng();
        if(latlng.lat === markerLat && latlng.lng === markerLng) {
            map.flyTo([markerLat, markerLng], zoomLvl, { duration: 0.75 });
            setTimeout(() => { marker.openPopup(); }, 200);
            setTimeout(() => {
                if (marker.getElement()) {
                    let iconEl = marker.getElement().querySelector('.custom-marker-icon, .quake-marker-icon');
                    if (iconEl) {
                        iconEl.classList.remove('border-white', 'border-red-500', 'border-2');
                        iconEl.classList.add('border-yellow-500', 'border-4', 'scale-125', 'z-50');
                    }
                }
            }, 250);
        }
    });
};

map.on('popupopen', function(e) {
    e.popup.update(); setTimeout(() => { e.popup.update(); }, 50);
    let marker = e.popup._source;
    if (marker && marker.options && marker.options.customId) {
        secimleriSifirla();
        setTimeout(() => {
            if (marker.getElement()) {
                let iconEl = marker.getElement().querySelector('.custom-marker-icon, .quake-marker-icon');
                if (iconEl) {
                    iconEl.classList.remove('border-white', 'border-red-500', 'border-2');
                    iconEl.classList.add('border-yellow-500', 'border-4', 'scale-125', 'z-50');
                }
            }
        }, 100);
        let div = document.getElementById(marker.options.customId);
        if (div) {
            div.classList.remove('border-b', 'border-gray-200', 'bg-white', 'border-gray-100'); 
            div.classList.add('bg-yellow-100', 'border-l-4', 'border-yellow-500');
            div.scrollIntoView({ behavior: 'auto', block: 'nearest' });
        }
    }
});

window.listeAra = function(inputId, listId) {
    let input = document.getElementById(inputId);
    if (!input) return;
    let filter = normalizeMetin(input.value);
    let container = document.getElementById(listId);
    if (!container) return;
    let items = container.getElementsByClassName('list-item');
    for (let i = 0; i < items.length; i++) {
        let txtValue = normalizeMetin(items[i].textContent || items[i].innerText);
        if (txtValue.indexOf(filter) > -1) items[i].style.display = "";
        else items[i].style.display = "none";
    }
};

window.muhtarFiltrele = function() {
    let secilenIlce = document.getElementById('sec-muhtar-ilce')?.value || "";
    let aramaMetni = normalizeMetin(document.getElementById('ara-muhtar')?.value || "");
    let container = document.getElementById('muhtar-list');
    if (!container) return;

    let items = container.getElementsByClassName('muhtar-item');
    for (let i = 0; i < items.length; i++) {
        let itemIlce = items[i].getAttribute('data-ilce') || "";
        let txtValue = normalizeMetin(items[i].textContent || items[i].innerText);
        
        let ilceUygun = (!secilenIlce || itemIlce === secilenIlce);
        let aramaUygun = (!aramaMetni || txtValue.indexOf(aramaMetni) > -1);

        items[i].style.display = (ilceUygun && aramaUygun) ? "" : "none";
    }

    if (secilenIlce) {
        bolgeSiniriniGoster(secilenIlce, null, '#0d9488');
    } else {
        ilceSiniriniTemizle();
    }
};

window.genisletMetin = function(event, btn) {
    event.stopPropagation();
    event.preventDefault();
    let ozetDiv = btn.previousElementSibling.previousElementSibling;
    let tamMetinDiv = btn.previousElementSibling;
    if(ozetDiv && tamMetinDiv) {
        ozetDiv.classList.add('hidden');
        tamMetinDiv.classList.remove('hidden');
        btn.remove();
    }
};

var aktifMuhtarIdx = null;
window.toggleMuhtarAcordion = function(idx, btn, ilceKey, mahalleRaw) {
    let detayDiv = document.getElementById('muhtar-detay-' + idx);
    if (!detayDiv) return;
    let isCurrentlyHidden = detayDiv.classList.contains('hidden');

    if (aktifMuhtarIdx !== null && aktifMuhtarIdx !== idx) {
        let eskiDetay = document.getElementById('muhtar-detay-' + aktifMuhtarIdx);
        let eskiItem = document.getElementById('muhtar-item-' + aktifMuhtarIdx);
        if (eskiDetay) eskiDetay.classList.add('hidden');
        if (eskiItem) {
            eskiItem.classList.remove('bg-yellow-100', 'border-l-4', 'border-yellow-500');
            eskiItem.classList.add('bg-white');
            let eskiIcon = eskiItem.querySelector('.muhtar-icon-box');
            if (eskiIcon) eskiIcon.className = "muhtar-icon-box w-10 h-10 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center shrink-0";
        }
    }

    let parentDiv = btn.parentElement;
    let iconBox = btn.querySelector('.muhtar-icon-box');

    if (isCurrentlyHidden) {
        aktifMuhtarIdx = idx;
        detayDiv.classList.remove('hidden');
        parentDiv.classList.remove('bg-white');
        parentDiv.classList.add('bg-yellow-100', 'border-l-4', 'border-yellow-500');
        if (iconBox) {
            iconBox.className = "muhtar-icon-box w-10 h-10 bg-yellow-500 text-white rounded-full flex items-center justify-center shrink-0 shadow-sm";
        }
        parentDiv.scrollIntoView({ behavior: 'auto', block: 'nearest' });
        
        if (ilceKey) {
            bolgeSiniriniGoster(ilceKey, mahalleRaw, '#0d9488');
        }
    } else {
        aktifMuhtarIdx = null;
        detayDiv.classList.add('hidden');
        parentDiv.classList.remove('bg-yellow-100', 'border-l-4', 'border-yellow-500');
        parentDiv.classList.add('bg-white');
        if (iconBox) {
            iconBox.className = "muhtar-icon-box w-10 h-10 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center shrink-0";
        }
        ilceSiniriniTemizle();
    }
};

var aktifCaddeIdx = null;
window.toggleCaddeAcordion = function(idx, btn, ilceKey) {
    let detayDiv = document.getElementById('cadde-detay-' + idx);
    if (!detayDiv) return;
    let isCurrentlyHidden = detayDiv.classList.contains('hidden');

    if (aktifCaddeIdx !== null && aktifCaddeIdx !== idx) {
        let eskiDetay = document.getElementById('cadde-detay-' + aktifCaddeIdx);
        let eskiItem = document.getElementById('cadde-item-' + aktifCaddeIdx);
        if (eskiDetay) eskiDetay.classList.add('hidden');
        if (eskiItem) {
            eskiItem.classList.remove('bg-yellow-100', 'border-l-4', 'border-yellow-500');
            eskiItem.classList.add('bg-white');
            let eskiIcon = eskiItem.querySelector('.cadde-icon-box');
            if (eskiIcon) eskiIcon.className = "cadde-icon-box w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center shrink-0";
        }
    }

    let parentDiv = btn.parentElement;
    let iconBox = btn.querySelector('.cadde-icon-box');

    if (isCurrentlyHidden) {
        aktifCaddeIdx = idx;
        detayDiv.classList.remove('hidden');
        parentDiv.classList.remove('bg-white');
        parentDiv.classList.add('bg-yellow-100', 'border-l-4', 'border-yellow-500');
        if (iconBox) {
            iconBox.className = "cadde-icon-box w-10 h-10 bg-yellow-500 text-white rounded-full flex items-center justify-center shrink-0 shadow-sm";
        }
        parentDiv.scrollIntoView({ behavior: 'auto', block: 'nearest' });

        if (ilceKey) {
            bolgeSiniriniGoster(ilceKey, null, '#4f46e5');
        }
    } else {
        aktifCaddeIdx = null;
        detayDiv.classList.add('hidden');
        parentDiv.classList.remove('bg-yellow-100', 'border-l-4', 'border-yellow-500');
        parentDiv.classList.add('bg-white');
        if (iconBox) {
            iconBox.className = "cadde-icon-box w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center shrink-0";
        }
        ilceSiniriniTemizle();
    }
};

let searchTimeout;
const aramaInput = document.getElementById('yer-arama');
if (aramaInput) {
    aramaInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        let query = e.target.value.trim();
        let resultsContainer = document.getElementById('arama-sonuclari');
        if(!resultsContainer) return;
        if(query.length < 3) { resultsContainer.classList.add('hidden'); return; }

        searchTimeout = setTimeout(() => {
            resultsContainer.innerHTML = '<li class="px-4 py-3 text-sm text-gray-500 text-center"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Aranıyor...</li>';
            resultsContainer.classList.remove('hidden');
            
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=8&countrycodes=tr`, {
                headers: { 'Accept-Language': 'tr' }
            })
            .then(res => res.json())
            .then(data => {
                resultsContainer.innerHTML = '';
                if(data.length > 0) {
                    let uniq = new Set();
                    data.forEach(place => {
                        let isim = place.display_name;
                        if(uniq.has(isim)) return;
                        uniq.add(isim);
                        
                        let li = document.createElement('li');
                        li.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer text-sm text-gray-700 transition-colors flex items-start gap-3';
                        li.innerHTML = '<i class="fa-solid fa-location-dot text-blue-500 mt-0.5"></i><span class="line-clamp-2 leading-tight">' + isim + '</span>';
                        li.onclick = () => {
                            resultsContainer.classList.add('hidden');
                            document.getElementById('yer-arama').value = isim.split(',')[0];
                            haritadaArananYeriGoster(place);
                        };
                        resultsContainer.appendChild(li);
                    });
                } else { 
                    resultsContainer.innerHTML = '<li class="px-4 py-3 text-sm text-gray-500 text-center">Sonuç bulunamadı</li>'; 
                }
            }).catch(err => {
                resultsContainer.innerHTML = '<li class="px-4 py-3 text-sm text-red-500 text-center">Bağlantı hatası</li>';
            });
        }, 600);
    });
}

document.addEventListener('click', function(e) {
    let aramaInp = document.getElementById('yer-arama');
    let aramaSonuc = document.getElementById('arama-sonuclari');
    if (aramaInp && aramaSonuc && !aramaInp.contains(e.target) && !aramaSonuc.contains(e.target)) {
        aramaSonuc.classList.add('hidden');
    }
});

function haritadaArananYeriGoster(place) {
    let lat = parseFloat(place.lat);
    let lon = parseFloat(place.lon);
    if(aramaMarker) map.removeLayer(aramaMarker);
    var searchIcon = L.divIcon({
        html: '<div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center border-4 border-white shadow-xl scale-110 z-50"><i class="fa-solid fa-location-crosshairs text-lg animate-pulse"></i></div>',
        className: '', iconSize: [40, 40], iconAnchor: [20, 20]
    });
    aramaMarker = L.marker([lat, lon], {icon: searchIcon}).addTo(map);
    let popupIcerik = '<div style="width: 240px;" class="p-3 w-64 box-border"><h3 class="font-bold text-[15px] text-blue-600 border-b border-gray-100 pb-2 mb-2"><i class="fa-solid fa-map-location-dot mr-1"></i> Arama Sonucu</h3><div class="text-xs text-gray-600 mb-2"><p class="mb-1">' + place.display_name + '</p></div><a href="https://www.google.com/maps/dir/?api=1&destination=' + lat + ',' + lon + '" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-3 py-2 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>';
    aramaMarker.bindPopup(popupIcerik, {className: 'minimal-popup', closeButton: false, offset: [0, -5], maxWidth: 280}).openPopup();
    map.flyTo([lat, lon], 15, { duration: 0.85 });
}

function kapatTumPaneller() {
    ilceSiniriniTemizle();
    ['quake-panel', 'afet-panel', 'muze-panel', 'otopark-panel', 'pazar-panel', 'eczane-panel', 'wifi-panel', 'kamera-panel', 'vefat-panel', 'muhtar-panel', 'hal-panel', 'cadde-panel'].forEach(id => {
        let el = document.getElementById(id);
        if(el) el.classList.add('-translate-x-[300%]');
    });
}

function acPanel(id) {
    let el = document.getElementById(id);
    if(el) el.classList.remove('-translate-x-[300%]');
}

function gosterKullanilabilirHizmetler() {
    document.querySelectorAll('.active-service').forEach(el => el.classList.remove('hidden'));
    document.querySelectorAll('.inactive-service').forEach(el => el.classList.add('hidden'));
    document.getElementById('tab-aktif').classList.add('border-blue-500');
    document.getElementById('tab-aktif').classList.remove('border-transparent');
    document.getElementById('tab-pasif').classList.remove('border-blue-500');
    document.getElementById('tab-pasif').classList.add('border-transparent');
}

function gosterKullanilamayanHizmetler() {
    document.querySelectorAll('.active-service').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.inactive-service').forEach(el => el.classList.remove('hidden'));
    document.getElementById('tab-pasif').classList.add('border-blue-500');
    document.getElementById('tab-pasif').classList.remove('border-transparent');
    document.getElementById('tab-aktif').classList.remove('border-blue-500');
    document.getElementById('tab-aktif').classList.add('border-transparent');
}

function toastGoster(mesaj, altMesaj) {
    let toast = document.getElementById('toast-panel');
    toast.innerHTML = '<div class="bg-gray-800 text-white px-6 py-4 rounded-2xl shadow-xl flex items-center gap-4"><i class="fa-solid fa-circle-info text-2xl text-blue-400"></i><div><h4 class="font-bold text-[15px]">' + mesaj + '</h4><p class="text-[12px] text-gray-300 mt-1">' + altMesaj + '</p></div></div>';
    toast.classList.remove('translate-y-20', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');
    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 4000);
}

function caddeSokakListesiGoster() {
    acPanel('cadde-panel');
    aktifCaddeIdx = null;
    let listDOM = document.getElementById('cadde-list');
    if (!listDOM) return;
    listDOM.innerHTML = '';
    let fragment = document.createDocumentFragment();

    let genel = ilceCaddeSokakBilgileri['TOPLAM'];
    let genelDetay = ilceDetayBilgileri['TOPLAM'];
    let ozetDiv = document.createElement('div');
    ozetDiv.className = "p-3.5 bg-indigo-900 text-white border-b border-indigo-800";
    ozetDiv.innerHTML = `
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-200"><i class="fa-solid fa-chart-pie mr-1"></i>Kayseri Genel Toplamı</span>
            <span class="text-xs font-extrabold bg-indigo-600 px-2 py-0.5 rounded-full">${genel.toplam} Yol</span>
        </div>
        <div class="grid grid-cols-3 gap-1.5 text-center text-[10px]">
            <div class="bg-indigo-800/80 p-1.5 rounded-lg"><span class="text-indigo-300 block">Cadde</span><b class="text-xs text-white">${genel.cadde}</b></div>
            <div class="bg-indigo-800/80 p-1.5 rounded-lg"><span class="text-indigo-300 block">Sokak</span><b class="text-xs text-white">${genel.sokak}</b></div>
            <div class="bg-indigo-800/80 p-1.5 rounded-lg"><span class="text-indigo-300 block">Bulvar</span><b class="text-xs text-white">${genel.bulvar}</b></div>
            <div class="bg-indigo-800/80 p-1.5 rounded-lg"><span class="text-indigo-300 block">Meydan</span><b class="text-xs text-white">${genel.meydan}</b></div>
            <div class="bg-indigo-800/80 p-1.5 rounded-lg"><span class="text-indigo-300 block">Küme Evler</span><b class="text-xs text-white">${genel.kume}</b></div>
            <div class="bg-indigo-800/80 p-1.5 rounded-lg"><span class="text-indigo-300 block">Mahalle</span><b class="text-xs text-white">${genelDetay.mahalle_sayisi}</b></div>
        </div>
    `;
    fragment.appendChild(ozetDiv);

    const ilceler = Object.keys(ilceCaddeSokakBilgileri).filter(k => k !== 'TOPLAM');
    ilceler.forEach((ilceKey, idx) => {
        let cs = ilceCaddeSokakBilgileri[ilceKey];
        let det = ilceDetayBilgileri[ilceKey] || {};
        let ilceAd = toTitleCase(ilceKey) + " İlçesi";
        let degisimRenk = (det.degisim && det.degisim.startsWith('-')) ? 'text-red-600' : 'text-emerald-600';
        let degisimOk = (det.degisim && det.degisim.startsWith('-')) ? 'fa-arrow-trend-down' : 'fa-arrow-trend-up';

        let div = document.createElement('div');
        div.id = "cadde-item-" + idx;
        div.className = "list-item border-b border-gray-200 bg-white cadde-item";
        div.innerHTML = `
            <button onclick="toggleCaddeAcordion(${idx}, this, '${ilceKey}')" class="w-full p-4 flex items-center justify-between hover:bg-indigo-50/60 transition-colors text-left focus:outline-none">
                <div class="flex items-center gap-3">
                    <div class="cadde-icon-box w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-road text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 leading-tight">${ilceAd}</h3>
                        <span class="text-[10px] text-indigo-600 font-medium"><i class="fa-solid fa-draw-polygon mr-1"></i>Sınırı Gör & İstatistikler <i class="fa-solid fa-chevron-down ml-0.5"></i></span>
                    </div>
                </div>
                <span class="bg-indigo-50 text-indigo-700 font-bold text-[11px] px-2.5 py-1 rounded-full border border-indigo-100">${cs.toplam}</span>
            </button>
            <div id="cadde-detay-${idx}" class="hidden px-4 pb-4 pt-1 bg-transparent">
                <div class="bg-white p-3 rounded-xl border border-yellow-300 shadow-sm space-y-2.5">
                    <div class="text-[11px] font-bold text-indigo-700 border-b border-gray-100 pb-1.5 flex items-center justify-between">
                        <span><i class="fa-solid fa-signs-post mr-1"></i>Yol ve Yerleşim Dağılımı</span>
                        <span class="text-gray-500 font-normal">Toplam: <b>${cs.toplam}</b></span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-gray-50 p-2 rounded-lg border border-gray-100 flex justify-between items-center">
                            <span class="text-gray-500">Cadde:</span>
                            <span class="font-bold text-gray-800">${cs.cadde}</span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg border border-gray-100 flex justify-between items-center">
                            <span class="text-gray-500">Sokak:</span>
                            <span class="font-bold text-gray-800">${cs.sokak}</span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg border border-gray-100 flex justify-between items-center">
                            <span class="text-gray-500">Bulvar:</span>
                            <span class="font-bold text-gray-800">${cs.bulvar}</span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg border border-gray-100 flex justify-between items-center">
                            <span class="text-gray-500">Meydan:</span>
                            <span class="font-bold text-gray-800">${cs.meydan}</span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg border border-gray-100 flex justify-between items-center col-span-2">
                            <span class="text-gray-500">Küme Evler:</span>
                            <span class="font-bold text-gray-800">${cs.kume}</span>
                        </div>
                    </div>
                    ${det.alan_km2 ? `
                    <div class="pt-2 border-t border-gray-100 space-y-1 text-[11px] text-gray-600">
                        <div class="text-[11px] font-bold text-teal-700 mb-1"><i class="fa-solid fa-map mr-1"></i>İlçe Genel Bilgileri</div>
                        <div class="flex justify-between"><span>Yüzölçümü:</span><span class="font-semibold text-gray-800">${det.alan_km2} km² (${det.alan_m2} m²)</span></div>
                        <div class="flex justify-between"><span>Mahalle Sayısı:</span><span class="font-semibold text-gray-800">${det.mahalle_sayisi} Mahalle</span></div>
                        <div class="flex justify-between"><span>2024 / 2025 Nüfus:</span><span class="font-semibold text-gray-800">${det.nufus_2024} / ${det.nufus_2025}</span></div>
                        <div class="flex justify-between"><span>Nüfus Değişim Oranı:</span><span class="font-bold ${degisimRenk}"><i class="fa-solid ${degisimOk} mr-1"></i>%${det.degisim}</span></div>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
        fragment.appendChild(div);
    });

    listDOM.appendChild(fragment);

    if (typeof hedefIlce !== 'undefined' && hedefIlce) {
        let ilceIndex = ilceler.indexOf(hedefIlce);
        if (ilceIndex !== -1) {
            setTimeout(() => { 
                let btn = document.querySelector(`#cadde-item-${ilceIndex} button`);
                if (btn) btn.click(); 
            }, 600);
        }
        hedefIlce = null;
    }
    
}

function tumHizmetleriGoster(reqId) {
    akiciGecisYap([38.7205, 35.4826], 12, () => {
        if (reqId !== currentRequestId) return;

        fetch('api/api_getir.php?endpoint=nobetci_eczane').then(r=>r.json()).then(data => {
            if (reqId !== currentRequestId) return;
            let eczaneler = Array.isArray(data) ? data : (data.features || []);
            eczaneler.forEach((eczane, index) => {
                let lat = parseFloat(eczane.latitude), lng = parseFloat(eczane.longitude);
                if(!isNaN(lat) && !isNaN(lng)) {
                    let ad = toTitleCase(eczane.name || 'Eczane');
                    let tel = eczane.phone || 'Telefon yok';
                    let adres = toTitleCase(eczane.address || 'Adres yok');
                    
                    let nobet_turu = eczane.nobet_turu || eczane.NobetTuru || eczane.description || eczane.aciklama || eczane.type || "";
                    let badgeHTML = "";
                    if (nobet_turu.toLowerCase().includes('24') || nobet_turu.toLowerCase().includes('tam')) {
                        badgeHTML = `<span class="inline-block bg-red-500 text-white text-[10px] px-2 py-0.5 rounded font-bold mb-2">24 Saat Açık</span>`;
                    } else if (nobet_turu.toLowerCase().includes('01:00') || nobet_turu.toLowerCase().includes('01.00') || nobet_turu.toLowerCase().includes('gece')) {
                        badgeHTML = `<span class="inline-block bg-blue-500 text-white text-[10px] px-2 py-0.5 rounded font-bold mb-2">Gece 01:00'a Kadar Açık</span>`;
                    } else {
                        badgeHTML = `<span class="inline-block bg-red-500 text-white text-[10px] px-2 py-0.5 rounded font-bold mb-2">Nöbetçi Eczane</span>`;
                    }

                    let popup = `<div style="width: 240px;" class="p-3 w-64 box-border">${badgeHTML}<h3 class="font-bold text-[15px] text-red-600 border-b border-gray-100 pb-2 mb-2"><i class="fa-solid fa-staff-snake mr-1 text-red-400"></i> ${ad}</h3><div class="text-xs text-gray-600 mb-2"><p class="mb-1"><i class="fa-solid fa-location-dot w-4 text-gray-400"></i> ${adres}</p><p><i class="fa-solid fa-phone w-4 text-gray-400"></i> ${tel}</p></div><a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-3 py-2 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                    animasyonluMarkerEkle(lat, lng, '<div data-default-border="border-red-500" class="custom-marker-icon w-9 h-9 bg-white rounded-full flex items-center justify-center border-2 border-red-500 shadow-md"><img src="assets/icons/eczane.png" class="w-6 h-6 object-contain"></div>', 'all_ecz_' + index, popup);
                }
            });
        }).catch(e=>{});

        fetch('api/api_getir.php?endpoint=vefat').then(r=>r.json()).then(data => {
            if (reqId !== currentRequestId) return;
            if (Array.isArray(data)) {
                data.forEach((item, index) => {
                    let rawTaziye = (item.taziyeAdresi || '').trim();
                    let hasTaziyeText = (rawTaziye !== '' && rawTaziye.toLocaleLowerCase('tr-TR') !== 'belirtilmemiş' && rawTaziye !== '-');
                    let lat = parseFloat(item.latitude), lng = parseFloat(item.longitude);
                    
                    if (hasTaziyeText && !isNaN(lat) && !isNaN(lng)) {
                        let ad = toTitleCase(item.adiSoyadi || 'Vefat İlanı');
                        let taziye = toTitleCase(rawTaziye);
                        let rawCami = (item.mezarlikYeri || '').trim();
                        let mezarlik = rawCami ? toTitleCase(rawCami) : '-';
                        let vakit = toTitleCase(item.vakit || '-');
                        let hasCami = (rawCami !== '' && rawCami.toLocaleLowerCase('tr-TR') !== 'belirtilmemiş' && rawCami !== '-');
                        let camiPopupBtn = hasCami
                            ? `<a href="https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(rawCami + ', Kayseri')}" target="_blank" class="block w-full bg-zinc-700 !text-white border border-zinc-800 text-center px-3 py-1.5 rounded-md text-xs font-bold hover:bg-zinc-800 hover:!text-white transition-colors mt-1.5 shadow-sm"><i class="fa-solid fa-mosque mr-1"></i> Camiye Yol Tarifi Al</a>`
                            : '';

                        let popup = `<div style="width: 240px;" class="p-3 w-64 box-border"><span class="inline-block bg-zinc-800 text-white text-[10px] px-2 py-0.5 rounded font-bold mb-2"><i class="fa-solid fa-ribbon mr-1"></i>Taziye Noktası</span><h3 class="font-bold text-[15px] text-zinc-800 border-b border-gray-100 pb-2 mb-2">${ad}</h3><div class="text-xs text-gray-600 mb-2"><p class="mb-1"><i class="fa-solid fa-mosque w-4 text-gray-400"></i> ${vakit} / ${mezarlik}</p><p><i class="fa-solid fa-location-dot w-4 text-gray-400"></i> ${taziye}</p></div><a href="https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(rawTaziye)}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-3 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-2 shadow-sm"><i class="fa-solid fa-location-arrow mr-1"></i> Taziye Adresine Yol Tarifi Al</a>${camiPopupBtn}</div>`;
                        animasyonluMarkerEkle(lat, lng, '<div class="custom-marker-icon w-8 h-8 bg-zinc-800 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-ribbon text-sm"></i></div>', 'all_vefat_' + index, popup);
                    }
                });
            }
        }).catch(e=>{});

        fetch('api/api_getir.php?endpoint=wifi_noktalari').then(r=>r.json()).then(data => {
            if (reqId !== currentRequestId) return;
            if(data && data.features) {
                data.features.forEach((item, index) => {
                    if (item.geometry && item.geometry.coordinates) {
                        let c = item.geometry.coordinates;
                        let props = item.properties || {};
                        let ad = toTitleCase(props.ADI || 'Wi-Fi');
                        let popup = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-wifi text-xl text-green-600"></i></div><h3 class="font-bold text-[14px] text-gray-800 leading-tight mb-1">${ad}</h3><span class="text-[10px] bg-green-500 text-white px-2 py-0.5 rounded-full font-bold">KBB ÜCRETSİZ İNTERNET</span><a href="https://www.google.com/maps/dir/?api=1&destination=${c[1]},${c[0]}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                        animasyonluMarkerEkle(c[1], c[0], '<div class="custom-marker-icon w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-wifi text-sm"></i></div>', 'all_wifi_' + index, popup);
                    }
                });
            }
        }).catch(e=>{});

        fetch('assets/data/otoparklar-456371.geojson.txt').then(r=>r.json()).then(data => {
            if (reqId !== currentRequestId) return;
            if(data && data.features) {
                data.features.forEach((item, index) => {
                    if (item.geometry && item.geometry.coordinates) {
                        let c = item.geometry.coordinates;
                        let props = item.properties || {};
                        let ad = toTitleCase(props.ADI || 'Şehir Otoparkı');
                        let popup = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-square-parking text-2xl text-gray-800"></i></div><h3 class="font-bold text-[13px] text-gray-800 leading-tight mb-3">${ad}</h3><a href="https://www.google.com/maps/dir/?api=1&destination=${c[1]},${c[0]}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                        animasyonluMarkerEkle(c[1], c[0], '<div class="custom-marker-icon w-8 h-8 bg-gray-800 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md font-bold font-sans">P</div>', 'all_park_' + index, popup);
                    }
                });
            }
        }).catch(e=>{});

        fetch('assets/data/pazar_yeri-4932711.geojson.txt').then(r=>r.json()).then(data => {
            if (reqId !== currentRequestId) return;
            if(data && data.features) {
                data.features.forEach((item, index) => {
                    if (item.geometry && item.geometry.coordinates) {
                        let c = item.geometry.coordinates;
                        let props = item.properties || {};
                        let ad = toTitleCase(props.ADI || 'Pazar Yeri');
                        let gun = toTitleCase(props.PAZARGUNAD || 'Belirsiz');
                        let aciklama = props.ACIKLAMA ? duzenleMetin(toTitleCase(props.ACIKLAMA)) : '';
                        let popup = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-basket-shopping text-xl text-sky-600"></i></div><h3 class="font-bold text-[14px] text-gray-800 leading-tight mb-1">${ad}</h3><span class="text-[11px] bg-sky-500 text-white px-2 py-1 rounded-full font-bold block mt-1 mb-2">${gun}</span><span class="text-[10px] text-gray-500 block mb-3">${aciklama}</span><a href="https://www.google.com/maps/dir/?api=1&destination=${c[1]},${c[0]}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold mt-2 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                        animasyonluMarkerEkle(c[1], c[0], '<div class="custom-marker-icon w-8 h-8 bg-sky-500 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-basket-shopping text-sm"></i></div>', 'all_pazar_' + index, popup);
                    }
                });
            }
        }).catch(e=>{});

        fetch('assets/data/kayseri-gezilecek-yerler.json').then(r=>r.json()).then(data => {
            if (reqId !== currentRequestId) return;
            if(Array.isArray(data)) {
                data.forEach((item, index) => {
                    let lat = parseFloat(item.latitude), lng = parseFloat(item.longitude);
                    if(!isNaN(lat) && !isNaN(lng)) {
                        let title = duzenleMetin(toTitleCase(item.title));
                        let fullContent = item.content ? duzenleMetin(item.content) : 'Kayseri tarihi alanı.';
                        let shortContent = fullContent.length > 100 ? fullContent.substring(0, 100) + '...' : fullContent;
                        let btnGeniSlet = fullContent.length > 100 ? `<button onclick="window.genisletMetin(event, this);" class="text-[11px] text-rose-600 font-bold mt-1 mb-2 block w-full text-center bg-rose-50 rounded py-1.5 hover:bg-rose-100 transition-colors">Açıklamayı Genişlet <i class="fa-solid fa-chevron-down"></i></button>` : '';
                        let resimHTML = item.bannerImage ? `<img src="${item.bannerImage}" style="height: 144px; width: 100%; object-fit: cover;" class="w-full h-36 object-cover rounded-md mb-2 shadow-sm border border-gray-200">` : '';
                        
                        let popupIcerik = document.createElement('div');
                        popupIcerik.className = "p-1 w-64 box-border";
                        popupIcerik.style.width = "240px";
                        popupIcerik.innerHTML = `
                            ${resimHTML}
                            <h3 class="font-bold text-[14px] text-rose-700 leading-tight mb-2">${title}</h3>
                            <div class="text-[11px] text-gray-600 mb-2 leading-tight">${shortContent}</div>
                            <div class="text-[11px] text-gray-600 mb-2 leading-tight hidden max-h-48 overflow-y-auto pr-1 scrollbar-hide">${fullContent}</div>
                            ${btnGeniSlet}
                            <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-2 rounded text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-2 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a>
                        `;
                        animasyonluMarkerEkle(lat, lng, '<div class="custom-marker-icon w-8 h-8 bg-rose-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-building-columns text-sm"></i></div>', 'all_muze_' + index, popupIcerik);
                    }
                });
            }
        }).catch(e=>{});

        fetch('assets/data/toplanma-alanlari_alan-3829284.geojson.txt').then(r=>r.json()).then(data => {
            if (reqId !== currentRequestId) return;
            if(data && data.features) {
                data.features.forEach((item, index) => {
                    let p = item.properties || {}, lat = parseFloat(p.ENLEM), lng = parseFloat(p.BOYLAM);
                    if(!isNaN(lat) && !isNaN(lng)) {
                        let ad = toTitleCase(p.ADI);
                        let ilce = p.ILCEAD ? `<span class="text-[10px] text-gray-600 bg-gray-100 px-2 py-0.5 rounded block mb-1">İlçe: ${toTitleCase(p.ILCEAD)}</span>` : '';
                        let popup = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-shield-halved text-xl text-emerald-600"></i></div><h3 class="font-bold text-[14px] text-gray-800 leading-tight mb-2">${ad}</h3>${ilce}<span class="text-[10px] text-emerald-700 font-bold bg-emerald-100 px-2 py-1 rounded block mb-3">Kapasite: ${p.ALAN} m²</span><a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                        animasyonluMarkerEkle(lat, lng, '<div class="custom-marker-icon w-8 h-8 bg-emerald-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-shield-halved text-sm"></i></div>', 'all_afet_' + index, popup);
                    }
                });
            }
        }).catch(e=>{});

        fetch('https://api.orhanaydogdu.com.tr/deprem/kandilli/live').then(r=>r.json()).then(data => {
            if (reqId !== currentRequestId) return;
            if(data && data.result) {
                data.result.slice(0, 100).forEach((deprem, index) => {
                    let c = deprem.geojson.coordinates, mag = parseFloat(deprem.mag).toFixed(1);
                    let ad = toTitleCase(deprem.title);
                    let rawDate = deprem.date || deprem.date_time || deprem.timestamp || "";
                    let date = "Bilinmiyor";
                    if (rawDate) { let p = rawDate.split(" "); if (p.length > 0) { let y = p[0].split(/[-.]/); if (y.length === 3) date = y[2] + "/" + y[1] + "/" + y[0] + (p[1] ? " " + p[1] : ""); } }
                    let popup = `<div style="width: 190px;" class="p-2 w-48 text-center box-border"><h3 class="font-bold text-[14px] text-purple-700 leading-tight mb-1">${ad}</h3><p class="text-[11px] text-gray-600 mb-1">Şiddet: <b class="text-purple-600">${mag}</b> | Derinlik: <b>${deprem.depth} km</b></p><p class="text-[10px] text-gray-500"><i class="fa-regular fa-clock mr-1"></i>${date}</p></div>`;
                    animasyonluMarkerEkle(c[1], c[0], `<div class="quake-marker-icon w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md font-bold text-sm">${mag}</div>`, 'all_dep_' + index, popup);
                });
            }
        }).catch(e=>{});

        const kameralar = [
            { id: "all_cam1", ad: "Erciyes Tekir Kapı", lat: 38.5414, lng: 35.5312 },
            { id: "all_cam2", ad: "Cumhuriyet Meydanı", lat: 38.7205, lng: 35.4826 },
            { id: "all_cam3", ad: "Sivas Caddesi", lat: 38.7250, lng: 35.4900 },
            { id: "all_cam4", ad: "Kartal Kavşağı", lat: 38.7115, lng: 35.4930 },
            { id: "all_cam5", ad: "Talas Anayurt", lat: 38.6850, lng: 35.5350 }
        ];
        kameralar.forEach(k => {
            let popupIcerik = `<div style="width: 240px;" class="p-2 w-64 text-center box-border"><h3 class="font-bold text-[14px] text-slate-800 border-b border-gray-100 pb-2 mb-2"><i class="fa-solid fa-video-slash text-red-500"></i> ${k.ad}</h3><div class="text-xs text-gray-600 mb-2 leading-tight">KVKK Kararı gereğince anlık yayın kapalıdır.</div></div>`;
            animasyonluMarkerEkle(k.lat, k.lng, '<div class="custom-marker-icon w-8 h-8 bg-slate-700 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-video text-sm"></i></div>', k.id, popupIcerik);
        });
    });
}

function veriCekVeGoster(endpoint) {
    currentRequestId++;
    const reqId = currentRequestId;

    let keepExistingMarkers = ['hal'].includes(endpoint);
    let isListOrBoundaryMode = ['hal', 'muhtar', 'cadde_sokak'].includes(endpoint);

    if (!keepExistingMarkers) {
        markerGroup.clearLayers();
        if(aramaMarker) map.removeLayer(aramaMarker);
        updateMarkerCount();
    }

    kapatTumPaneller();
    kaynakGuncelle(endpoint);

    if (endpoint === 'tumu') {
        tumHizmetleriGoster(reqId);
        return;
    }

    if (endpoint === 'cadde_sokak') {
        caddeSokakListesiGoster();
        return;
    }

    if (endpoint === 'kaybis' || endpoint === 'kaski_su_kesinti' || endpoint === 'sarj_istasyonlari' || endpoint === 'spor_tesisleri') {
        toastGoster('Veri Sağlanamıyor', 'İlgili kurum tarafından bu hizmet için aktif API bağlantısı sunulmamaktadır.');
        return; 
    }

    let url = '';
    let listId = '';

    if (endpoint === 'vefat') {
        url = 'api/api_getir.php?endpoint=vefat';
        listId = 'vefat-list';
        acPanel('vefat-panel');
    }
    else if (endpoint === 'muhtar') {
        url = 'assets/data/muhtarlar.json'; 
        listId = 'muhtar-list';
        acPanel('muhtar-panel');
    }
    else if (endpoint === 'hal') {
        url = 'api/api_getir.php?endpoint=hal';
        listId = 'hal-list';
        acPanel('hal-panel');
    }
    else if (endpoint === 'deprem') {
        url = 'https://api.orhanaydogdu.com.tr/deprem/kandilli/live';
        listId = 'quake-list';
        acPanel('quake-panel');
    } 
    else if (endpoint === 'toplanma_alanlari') {
        url = 'assets/data/toplanma-alanlari_alan-3829284.geojson.txt';
        listId = 'afet-list';
        acPanel('afet-panel');
    } 
    else if (endpoint === 'gezilecek_yerler') {
        url = 'assets/data/kayseri-gezilecek-yerler.json';
        listId = 'muze-list';
        acPanel('muze-panel');
    } 
    else if (endpoint === 'pazar_yerleri') {
        url = 'assets/data/pazar_yeri-4932711.geojson.txt';
        listId = 'pazar-list';
        acPanel('pazar-panel');
    } 
    else if (endpoint === 'otoparklar') {
        url = 'assets/data/otoparklar-456371.geojson.txt';
        listId = 'otopark-list';
        acPanel('otopark-panel');
    }
    else if (endpoint === 'nobetci_eczane') {
        url = 'api/api_getir.php?endpoint=nobetci_eczane';
        listId = 'eczane-list';
        acPanel('eczane-panel');
    }
    else if (endpoint === 'wifi_noktalari') {
        url = 'api/api_getir.php?endpoint=wifi_noktalari';
        listId = 'wifi-list';
        acPanel('wifi-panel');
    }
    
    if (listId) {
        let lDOM = document.getElementById(listId);
        if(lDOM) lDOM.innerHTML = '<div class="p-8 text-center text-gray-500"><i class="fa-solid fa-spinner fa-spin text-3xl mb-3"></i><br>Yükleniyor...</div>';
    }

    let hedefKoordinat = (endpoint === 'deprem') ? [39.2, 35.0] : [38.7205, 35.4826];
    let hedefZoom = (endpoint === 'deprem') ? 6 : 12;

    if (endpoint === 'kameralar') {
        acPanel('kamera-panel');
        const list = document.getElementById('kamera-list');
        list.innerHTML = '';
        toastGoster('Canlı Yayın Kapalı', 'KVKK gereği Şehir Kameraları yayını durdurulmuştur, ancak konumları haritada gösterilmektedir.');
        
        akiciGecisYap(hedefKoordinat, hedefZoom, () => {
            if (reqId !== currentRequestId) return;
            const kameralar = [
                { id: "cam1", ad: "Erciyes Tekir Kapı", lat: 38.5414, lng: 35.5312 },
                { id: "cam2", ad: "Cumhuriyet Meydanı", lat: 38.7205, lng: 35.4826 },
                { id: "cam3", ad: "Sivas Caddesi", lat: 38.7250, lng: 35.4900 },
                { id: "cam4", ad: "Kartal Kavşağı", lat: 38.7115, lng: 35.4930 },
                { id: "cam5", ad: "Talas Anayurt", lat: 38.6850, lng: 35.5350 }
            ];

            kameralar.forEach(k => {
                let popupIcerik = `<div style="width: 240px;" class="p-2 w-64 text-center box-border"><h3 class="font-bold text-[14px] text-slate-800 border-b border-gray-100 pb-2 mb-2"><i class="fa-solid fa-video-slash text-red-500"></i> ${k.ad}</h3><div class="text-xs text-gray-600 mb-2 leading-tight">KVKK Kararı gereğince anlık yayın kapalıdır.</div></div>`;
                animasyonluMarkerEkle(k.lat, k.lng, '<div class="custom-marker-icon w-8 h-8 bg-slate-700 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-video text-sm"></i></div>', k.id, popupIcerik);
                
                let div = document.createElement('div');
                div.id = k.id;
                div.className = "list-item p-4 border-b border-gray-200 hover:bg-slate-50 cursor-pointer transition-colors bg-white";
                div.innerHTML = `<div class="flex items-center gap-3"><div class="w-10 h-10 bg-slate-100 rounded flex items-center justify-center shrink-0"><i class="fa-solid fa-video-slash text-slate-600 text-lg"></i></div><div><h3 class="text-sm font-bold text-gray-800 leading-tight">${k.ad}</h3><span class="text-[10px] text-red-500 mt-1 block font-medium">Yayın Kapalı</span></div></div>`;
                list.appendChild(div);
                
                div.onclick = () => { window.aktifListeOgesi(k.id, k.lat, k.lng, 15); };
            });
        });
        return;
    }

    if (!url) return;

    let veriPromise = fetch(url).then(response => response.json());

    if (isListOrBoundaryMode) {
        veriPromise.then(data => {
            if (reqId !== currentRequestId) return;
            let listDOM = document.getElementById(listId);
            if(listDOM) listDOM.innerHTML = '';
            let fragment = document.createDocumentFragment();

            if (endpoint === 'hal') {
                if (data.length === 0 && listDOM) {
                    listDOM.innerHTML = '<div class="p-8 text-center"><i class="fa-solid fa-box-open text-3xl text-gray-300 mb-3 block"></i><p class="text-sm text-gray-500 font-medium leading-relaxed">Resmi hal fiyatları yayınlanmadı.</p></div>';
                    return;
                }
                data.forEach((item, index) => {
                    let ad = toTitleCase(item.UrunAdi || item.urunAdi || item.adi || item.name || 'Ürün Bilgisi Yok');
                    let birim = item.Birim || item.birim || item.unit || '';
                    let fiyat = item.Fiyat || item.fiyat || item.price || item.EnDusukFiyat || '-';
                    
                    let div = document.createElement('div');
                    div.className = "list-item p-4 border-b border-gray-200 hover:bg-orange-50 transition-colors bg-white flex justify-between items-center";
                    div.innerHTML = `
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                                <img src="assets/icons/hal.png" class="w-6 h-6 object-contain">
                            </div>
                            <h3 class="text-sm font-bold text-gray-800 leading-tight">${ad}</h3>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-bold text-green-600">${fiyat} ₺</div>
                            <div class="text-[10px] text-gray-400">${birim}</div>
                        </div>
                    `;
                    fragment.appendChild(div);
                });
            }
            else if (endpoint === 'muhtar') {
                aktifMuhtarIdx = null;
                let ilceSelect = document.getElementById('sec-muhtar-ilce');
                if (ilceSelect) ilceSelect.value = "";
                if (data.length === 0 && listDOM) { listDOM.innerHTML = '<div class="p-6 text-center text-gray-500">Muhtar verisi bulunamadı.</div>'; return; }
                
                data.forEach((item, idx) => {
                    let rawIlceKey = normalizeIlceKey(item.ilce);
                    let ilceBilgi = ilceDetayBilgileri[rawIlceKey] || null;
                    let csBilgi = ilceCaddeSokakBilgileri[rawIlceKey] || null;

                    let ilce = toTitleCase(item.ilce || 'Merkez') + " İlçesi";
                    let rawMahalle = (item.neighborhoodName || '').trim();
                    let mahalle = toTitleCase(rawMahalle) + " Mahallesi";
                    let safeMahalleRaw = rawMahalle.replace(/'/g, "\\'");
                    let rawName = (item.name || '').trim();
                    let ad = (!rawName || rawName === 'nan' || rawName === 'Kurum Tarafından Yayınlanmadı') ? 'Kurum Tarafından Yayınlanmadı' : toTitleCase(rawName);
                    let pop = item.population || 'Bilinmiyor';
                    let rawTel = (item.gsm || item.phone || '').trim();
                    let tel = (!rawTel || rawTel === 'nan' || rawTel === 'Telefon Yok') ? 'Yayınlanmadı' : rawTel;
                    let mahalleAlan = item.alan ? `${item.alan} ha` : '';

                    let degisimRenk = (ilceBilgi && ilceBilgi.degisim.startsWith('-')) ? 'text-red-600' : 'text-emerald-600';
                    let degisimOk = (ilceBilgi && ilceBilgi.degisim.startsWith('-')) ? 'fa-arrow-trend-down' : 'fa-arrow-trend-up';

                    let div = document.createElement('div');
                    div.id = "muhtar-item-" + idx;
                    div.setAttribute('data-ilce', rawIlceKey);
                    div.className = "list-item border-b border-gray-200 bg-white muhtar-item";
                    div.innerHTML = `
                        <button onclick="toggleMuhtarAcordion(${idx}, this, '${rawIlceKey}', '${safeMahalleRaw}')" class="w-full p-4 flex items-center justify-between hover:bg-teal-50/60 transition-colors text-left focus:outline-none">
                            <div class="flex items-center gap-3">
                                <div class="muhtar-icon-box w-10 h-10 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-users text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-gray-800 leading-tight">${mahalle}</h3>
                                    <span class="text-[10px] text-teal-600 font-medium"><i class="fa-solid fa-draw-polygon mr-1"></i>${ilce} <i class="fa-solid fa-chevron-down ml-0.5"></i></span>
                                </div>
                            </div>
                        </button>
                        <div id="muhtar-detay-${idx}" class="hidden px-4 pb-4 pt-1 bg-transparent">
                            <div class="bg-white p-3 rounded-xl border border-yellow-300 shadow-sm space-y-2.5">
                                <div class="space-y-1.5">
                                    <div class="text-[11px] font-bold text-teal-700 border-b border-gray-100 pb-1"><i class="fa-solid fa-house-user mr-1"></i>Mahalle & Muhtar Bilgisi</div>
                                    <div class="flex items-center gap-2 text-xs text-gray-700">
                                        <i class="fa-solid fa-user-tie text-teal-600 w-4"></i> <span class="text-gray-500">Muhtar:</span> <span class="font-bold text-gray-800">${ad}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-700">
                                        <i class="fa-solid fa-phone text-teal-600 w-4"></i> <span class="text-gray-500">İletişim:</span> <span class="font-semibold">${tel}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-700">
                                        <i class="fa-solid fa-users-viewfinder text-teal-600 w-4"></i> <span class="text-gray-500">Mahalle Nüfusu:</span> <span class="font-semibold">${pop}</span>
                                    </div>
                                    ${mahalleAlan ? `
                                    <div class="flex items-center gap-2 text-xs text-gray-700">
                                        <i class="fa-solid fa-vector-square text-teal-600 w-4"></i> <span class="text-gray-500">Mahalle Alanı:</span> <span class="font-semibold">${mahalleAlan}</span>
                                    </div>` : ''}
                                </div>

                                ${ilceBilgi ? `
                                <div class="pt-2 border-t border-gray-100 space-y-1 text-[11px] text-gray-600">
                                    <div class="text-[11px] font-bold text-indigo-700 mb-1"><i class="fa-solid fa-city mr-1"></i>${ilce} Genel İstatistikleri</div>
                                    <div class="flex justify-between"><span>İlçe Alanı (km²):</span><span class="font-bold text-gray-800">${ilceBilgi.alan_km2} km²</span></div>
                                    <div class="flex justify-between"><span>İlçe Alanı (m²):</span><span class="font-semibold text-gray-700">${ilceBilgi.alan_m2} m²</span></div>
                                    <div class="flex justify-between"><span>Toplam Mahalle Sayısı:</span><span class="font-bold text-gray-800">${ilceBilgi.mahalle_sayisi} Mahalle</span></div>
                                    <div class="flex justify-between"><span>İlçe Nüfusu (2024):</span><span class="font-semibold text-gray-700">${ilceBilgi.nufus_2024}</span></div>
                                    <div class="flex justify-between"><span>İlçe Nüfusu (2025):</span><span class="font-bold text-gray-800">${ilceBilgi.nufus_2025}</span></div>
                                    <div class="flex justify-between"><span>Nüfus Değişim Oranı:</span><span class="font-bold ${degisimRenk}"><i class="fa-solid ${degisimOk} mr-1"></i>%${ilceBilgi.degisim}</span></div>
                                </div>
                                ` : ''}

                                ${csBilgi ? `
                                <div class="pt-2 border-t border-gray-100">
                                    <div class="text-[11px] font-bold text-amber-700 mb-1.5 flex justify-between">
                                        <span><i class="fa-solid fa-road mr-1"></i>İlçe Cadde/Sokak Sayıları</span>
                                        <span>Toplam: <b>${csBilgi.toplam}</b></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-1 text-center text-[10px]">
                                        <div class="bg-gray-50 p-1 rounded border border-gray-100"><span class="text-gray-400 block">Cadde</span><b class="text-gray-700">${csBilgi.cadde}</b></div>
                                        <div class="bg-gray-50 p-1 rounded border border-gray-100"><span class="text-gray-400 block">Sokak</span><b class="text-gray-700">${csBilgi.sokak}</b></div>
                                        <div class="bg-gray-50 p-1 rounded border border-gray-100"><span class="text-gray-400 block">Bulvar</span><b class="text-gray-700">${csBilgi.bulvar}</b></div>
                                        <div class="bg-gray-50 p-1 rounded border border-gray-100"><span class="text-gray-400 block">Meydan</span><b class="text-gray-700">${csBilgi.meydan}</b></div>
                                        <div class="bg-gray-50 p-1 rounded border border-gray-100 col-span-2"><span class="text-gray-400 block">Küme Evler</span><b class="text-gray-700">${csBilgi.kume}</b></div>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                    fragment.appendChild(div);
            });
            }
            if(listDOM) listDOM.appendChild(fragment);

            if (typeof hedefIlce !== 'undefined' && hedefIlce) {
                let selectBox = document.getElementById('sec-muhtar-ilce');
                if (selectBox) {
                    selectBox.value = hedefIlce;
                    setTimeout(() => { muhtarFiltrele(); }, 600);
                }
                hedefIlce = null;
            }

        }).catch(err => {
            console.error(err);
            let listDOM = listId ? document.getElementById(listId) : null;
            if(listDOM) listDOM.innerHTML = '<div class="p-6 text-center text-red-500 font-medium">Veri çekilirken hata oluştu.</div>';
        });
        return; 
    }

    akiciGecisYap(hedefKoordinat, hedefZoom, () => {
        if (reqId !== currentRequestId) return;

        veriPromise.then(data => {
            if (reqId !== currentRequestId) return;

            let listDOM = listId ? document.getElementById(listId) : null;
            if(listDOM) listDOM.innerHTML = '';
            let fragment = document.createDocumentFragment();
            
            if (endpoint === 'vefat') {
                if (!Array.isArray(data) || data.length === 0) {
                    if (listDOM) listDOM.innerHTML = '<div class="p-6 text-center text-gray-500">Güncel ilan bulunamadı.</div>';
                    return;
                }
                data.forEach((item, index) => {
                    let elementId = "vefat_" + index;
                    let ad = toTitleCase(item.adiSoyadi || 'Vefat İlanı');
                    let ebeveyn = toTitleCase(item.anneBabaAdi || '-');
                    let dogum = toTitleCase(item.dogumYeri || '-') + " - " + (item.dogumYili || '');
                    
                    let rawTaziye = (item.taziyeAdresi || '').trim();
                    let hasTaziyeText = (rawTaziye !== '' && rawTaziye.toLocaleLowerCase('tr-TR') !== 'belirtilmemiş' && rawTaziye !== '-');
                    let taziye = hasTaziyeText ? toTitleCase(rawTaziye) : 'Belirtilmemiş';
                    
                    let rawCami = (item.mezarlikYeri || '').trim();
                    let hasCami = (rawCami !== '' && rawCami.toLocaleLowerCase('tr-TR') !== 'belirtilmemiş' && rawCami !== '-');
                    let mezarlik = hasCami ? toTitleCase(rawCami) : 'Belirtilmemiş';
                    let vakit = toTitleCase(item.vakit || '-');
                    
                    let lat = parseFloat(item.latitude);
                    let lng = parseFloat(item.longitude);
                    let hasValidTaziyeMarker = (hasTaziyeText && !isNaN(lat) && !isNaN(lng));

                    let taziyeYolTarifiBtn = hasTaziyeText 
                        ? `<a href="https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(rawTaziye)}" target="_blank" onclick="event.stopPropagation();" class="mt-2 block w-full bg-blue-600 hover:bg-blue-700 !text-white text-center px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition-colors shadow-sm"><i class="fa-solid fa-location-arrow mr-1"></i> Taziye Adresine Yol Tarifi Al</a>`
                        : '';

                    let camiYolTarifiBtn = hasCami
                        ? `<a href="https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(rawCami + ', Kayseri')}" target="_blank" onclick="event.stopPropagation();" class="mt-1.5 block w-full bg-zinc-700 hover:bg-zinc-800 !text-white text-center px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition-colors shadow-sm"><i class="fa-solid fa-mosque mr-1"></i> Camiye Yol Tarifi Al</a>`
                        : '';

                    if (hasValidTaziyeMarker) {
                        let camiPopupBtn = hasCami
                            ? `<a href="https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(rawCami + ', Kayseri')}" target="_blank" class="block w-full bg-zinc-700 !text-white border border-zinc-800 text-center px-3 py-1.5 rounded-md text-xs font-bold hover:bg-zinc-800 hover:!text-white transition-colors mt-1.5 shadow-sm"><i class="fa-solid fa-mosque mr-1"></i> Camiye Yol Tarifi Al</a>`
                            : '';

                        let popupIcerik = `<div style="width: 240px;" class="p-3 w-64 box-border"><span class="inline-block bg-zinc-800 text-white text-[10px] px-2 py-0.5 rounded font-bold mb-2"><i class="fa-solid fa-ribbon mr-1"></i>Taziye Noktası</span><h3 class="font-bold text-[15px] text-zinc-800 border-b border-gray-100 pb-2 mb-2">${ad}</h3><div class="text-xs text-gray-600 mb-2"><p class="mb-1"><i class="fa-solid fa-mosque w-4 text-gray-400"></i> ${vakit} / ${mezarlik}</p><p><i class="fa-solid fa-location-dot w-4 text-gray-400"></i> ${taziye}</p></div><a href="https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(rawTaziye)}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-3 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-2 shadow-sm"><i class="fa-solid fa-location-arrow mr-1"></i> Taziye Adresine Yol Tarifi Al</a>${camiPopupBtn}</div>`;
                        animasyonluMarkerEkle(lat, lng, '<div class="custom-marker-icon w-8 h-8 bg-zinc-800 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-ribbon text-sm"></i></div>', elementId, popupIcerik);
                    }

                    if (listDOM) {
                        let div = document.createElement('div');
                        div.id = elementId;
                        div.className = "list-item p-4 border-b border-gray-200 hover:bg-zinc-50 " + (hasValidTaziyeMarker ? "cursor-pointer " : "") + "transition-colors bg-white";
                        div.innerHTML = `
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-zinc-100 rounded-full flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-ribbon text-zinc-600 text-lg"></i>
                                </div>
                                <div class="w-full">
                                    <h3 class="text-sm font-bold text-gray-800 leading-tight">${ad}</h3>
                                    <div class="text-[11px] text-gray-500 mt-1">D. Yeri/Yılı: <span class="font-medium">${dogum}</span></div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">Anne/Baba: <span class="font-medium">${ebeveyn}</span></div>
                                    <div class="mt-2 bg-zinc-50 p-2 rounded border border-zinc-100">
                                        <div class="text-[10px] text-zinc-600 font-bold mb-1"><i class="fa-solid fa-mosque mr-1"></i> ${vakit} / ${mezarlik}</div>
                                        <div class="text-[10px] text-zinc-500 leading-tight"><i class="fa-solid fa-location-dot mr-1"></i> Taziye: ${taziye}</div>
                                        ${taziyeYolTarifiBtn}
                                        ${camiYolTarifiBtn}
                                    </div>
                                </div>
                            </div>
                        `;
                        if (hasValidTaziyeMarker) {
                            div.onclick = () => { window.aktifListeOgesi(elementId, lat, lng, 15); };
                        }
                        fragment.appendChild(div);
                    }
                });
                if (listDOM) listDOM.appendChild(fragment);
            }

            else if(endpoint === 'deprem') {
                if(data && data.result) {
                    const tumDepremler = data.result.slice(0, 100);
                    if(tumDepremler.length === 0 && listDOM) { listDOM.innerHTML = '<div class="p-6 text-center text-gray-500 font-medium">Sistemde güncel deprem verisi bulunamadı.</div>'; return; }
                    
                    tumDepremler.forEach((deprem, index) => {
                        let coords = deprem.geojson.coordinates;
                        let magDisplay = parseFloat(deprem.mag).toFixed(1);
                        let ad = toTitleCase(deprem.title);
                        let elementId = "deprem_" + index;
                        
                        let rawDate = deprem.date || deprem.date_time || deprem.timestamp || "";
                        let date = "Bilinmiyor";
                        if (rawDate) { let p = rawDate.split(" "); if (p.length > 0) { let y = p[0].split(/[-.]/); if (y.length === 3) date = y[2] + "/" + y[1] + "/" + y[0] + (p[1] ? " " + p[1] : ""); } }

                        let popupIcerik = `<div style="width: 190px;" class="p-2 w-48 text-center box-border"><h3 class="font-bold text-[14px] text-purple-700 leading-tight mb-1">${ad}</h3><p class="text-[11px] text-gray-600 mb-1">Şiddet: <b class="text-purple-600">${magDisplay}</b> | Derinlik: <b>${deprem.depth} km</b></p><p class="text-[10px] text-gray-500"><i class="fa-regular fa-clock mr-1"></i>${date}</p></div>`;
                        
                        animasyonluMarkerEkle(coords[1], coords[0], `<div class="quake-marker-icon w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md font-bold text-sm">${magDisplay}</div>`, elementId, popupIcerik);
                        
                        if(listDOM) {
                            let div = document.createElement('div');
                            div.id = elementId;
                            div.className = "list-item p-4 border-b border-gray-100 hover:bg-purple-50 cursor-pointer transition-colors bg-white";
                            div.innerHTML = `<div class="flex items-start justify-between mb-2"><h3 class="text-sm font-bold text-gray-800 leading-tight pr-2">${ad}</h3><span class="text-[10px] text-gray-500 whitespace-nowrap mt-0.5"><i class="fa-regular fa-clock mr-1"></i>${date}</span></div><div class="flex items-center gap-4 text-xs font-medium text-gray-600"><span class="font-bold text-gray-600">Şiddet: ${magDisplay}</span><span class="font-bold text-gray-600"><i class="fa-solid fa-arrow-down mr-1"></i>Derinlik: ${deprem.depth} km</span></div>`;
                            div.onclick = () => { window.aktifListeOgesi(elementId, coords[1], coords[0], 11); };
                            fragment.appendChild(div);
                        }
                    });
                    if(listDOM) listDOM.appendChild(fragment);
                }
            }

            else if(endpoint === 'nobetci_eczane') {
                let eczaneler = Array.isArray(data) ? data : (data.features || []);
                eczaneler.forEach((eczane, index) => {
                    let lat = parseFloat(eczane.latitude); let lng = parseFloat(eczane.longitude);
                    if(!isNaN(lat) && !isNaN(lng)) {
                        let elementId = "eczane_" + index;
                        let ad = toTitleCase(eczane.name || 'Eczane');
                        let tel = eczane.phone || 'Telefon yok';
                        let adres = toTitleCase(eczane.address || 'Adres yok');

                        let nobet_turu = eczane.nobet_turu || eczane.NobetTuru || eczane.description || eczane.aciklama || eczane.type || "";
                        let popupBadgeHTML = "";
                        let listBadgeHTML = "";
                        
                        if (nobet_turu.toLowerCase().includes('24') || nobet_turu.toLowerCase().includes('tam')) {
                            popupBadgeHTML = `<span class="inline-block bg-red-500 text-white text-[10px] px-2 py-0.5 rounded font-bold mb-2">24 Saat Açık</span>`;
                            listBadgeHTML = `<span class="bg-red-50 text-red-600 border border-red-200 text-[9px] px-1.5 py-0.5 rounded font-bold mt-1.5 inline-block">24 Saat Açık</span>`;
                        } else if (nobet_turu.toLowerCase().includes('01:00') || nobet_turu.toLowerCase().includes('01.00') || nobet_turu.toLowerCase().includes('gece')) {
                            popupBadgeHTML = `<span class="inline-block bg-blue-500 text-white text-[10px] px-2 py-0.5 rounded font-bold mb-2">Gece 01:00'a Kadar Açık</span>`;
                            listBadgeHTML = `<span class="bg-blue-50 text-blue-600 border border-blue-200 text-[9px] px-1.5 py-0.5 rounded font-bold mt-1.5 inline-block">Gece 01:00'a Kadar Açık</span>`;
                        } else {
                            popupBadgeHTML = `<span class="inline-block bg-red-500 text-white text-[10px] px-2 py-0.5 rounded font-bold mb-2">Nöbetçi Eczane</span>`;
                            listBadgeHTML = `<span class="bg-red-50 text-red-600 border border-red-200 text-[9px] px-1.5 py-0.5 rounded font-bold mt-1.5 inline-block">Nöbetçi Eczane</span>`;
                        }
                        
                        let popupIcerik = `<div style="width: 240px;" class="p-3 w-64 box-border">${popupBadgeHTML}<h3 class="font-bold text-[15px] text-red-600 border-b border-gray-100 pb-2 mb-2"><i class="fa-solid fa-staff-snake mr-1 text-red-400"></i> ${ad}</h3><div class="text-xs text-gray-600 mb-2"><p class="mb-1"><i class="fa-solid fa-location-dot w-4 text-gray-400"></i> ${adres}</p><p><i class="fa-solid fa-phone w-4 text-gray-400"></i> ${tel}</p></div><a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-3 py-2 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                        
                        animasyonluMarkerEkle(lat, lng, '<div data-default-border="border-red-500" class="custom-marker-icon w-9 h-9 bg-white rounded-full flex items-center justify-center border-2 border-red-500 shadow-md"><img src="assets/icons/eczane.png" class="w-6 h-6 object-contain"></div>', elementId, popupIcerik);
                        
                        if(listDOM) {
                            let div = document.createElement('div');
                            div.id = elementId;
                            div.className = "list-item p-4 border-b border-gray-200 hover:bg-red-50 cursor-pointer transition-colors bg-white";
                            div.innerHTML = `
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-red-100 rounded flex items-center justify-center shrink-0 mt-0.5">
                                        <i class="fa-solid fa-staff-snake text-red-600 text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-800 leading-tight">${ad}</h3>
                                        <span class="text-[10px] text-gray-500 mt-1 block line-clamp-1">${adres}</span>
                                        <span class="text-[10px] text-gray-600 mt-0.5 block font-medium"><i class="fa-solid fa-phone mr-1"></i>${tel}</span>
                                        ${listBadgeHTML}
                                    </div>
                                </div>`;
                            div.onclick = () => { window.aktifListeOgesi(elementId, lat, lng, 15); };
                            fragment.appendChild(div);
                        }
                    }
                });
                if(listDOM) listDOM.appendChild(fragment);
            }
            
            else if(endpoint === 'wifi_noktalari') {
                if(data && data.features && data.features.length > 0) {
                    data.features.forEach((item, index) => {
                        if (item.geometry && item.geometry.coordinates) {
                            let elementId = "wifi_" + index;
                            let coords = item.geometry.coordinates; let props = item.properties || {};
                            let ad = toTitleCase(props.ADI || 'Wi-Fi');
                            
                            let popupIcerik = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-wifi text-xl text-green-600"></i></div><h3 class="font-bold text-[14px] text-gray-800 leading-tight mb-1">${ad}</h3><span class="text-[10px] bg-green-500 text-white px-2 py-0.5 rounded-full font-bold">KBB ÜCRETSİZ İNTERNET</span><a href="https://www.google.com/maps/dir/?api=1&destination=${coords[1]},${coords[0]}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                            
                            animasyonluMarkerEkle(coords[1], coords[0], '<div class="custom-marker-icon w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-wifi text-sm"></i></div>', elementId, popupIcerik);
                            
                            if(listDOM) {
                                let div = document.createElement('div');
                                div.id = elementId;
                                div.className = "list-item p-4 border-b border-gray-200 hover:bg-green-50 cursor-pointer transition-colors bg-white";
                                div.innerHTML = `<div class="flex items-center gap-3"><div class="w-10 h-10 bg-green-100 rounded flex items-center justify-center shrink-0"><i class="fa-solid fa-wifi text-green-600 text-lg"></i></div><div><h3 class="text-sm font-bold text-gray-800 leading-tight">${ad}</h3><span class="text-[10px] text-gray-500 mt-1 block">Ücretsiz İnternet Erişim Noktası</span></div></div>`;
                                div.onclick = () => { window.aktifListeOgesi(elementId, coords[1], coords[0], 16); };
                                fragment.appendChild(div);
                            }
                        }
                    });
                    if(listDOM) listDOM.appendChild(fragment);
                } else if(listDOM) {
                    listDOM.innerHTML = '<div class="p-6 text-center text-gray-500 font-medium">Sistemde güncel Wi-Fi noktası bulunamadı.</div>';
                }
            }

            else if(endpoint === 'toplanma_alanlari') {
                if(data && data.features && data.features.length > 0) {
                    data.features.forEach((item, index) => {
                        let props = item.properties || {};
                        let lat = parseFloat(props.ENLEM); let lng = parseFloat(props.BOYLAM);
                        if(!isNaN(lat) && !isNaN(lng)) {
                            let elementId = "afet_" + index;
                            let ad = toTitleCase(props.ADI); let ilce = toTitleCase(props.ILCEAD);
                            let ilceHtml = ilce ? `<span class="text-[10px] text-gray-600 bg-gray-100 px-2 py-0.5 rounded block mb-1">İlçe: ${ilce}</span>` : '';
                            
                            let popupIcerik = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-shield-halved text-xl text-emerald-600"></i></div><h3 class="font-bold text-[14px] text-gray-800 leading-tight mb-2">${ad}</h3>${ilceHtml}<span class="text-[10px] text-emerald-700 font-bold bg-emerald-100 px-2 py-1 rounded block mb-3">Kapasite: ${props.ALAN} m²</span><a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold mt-2 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;

                            animasyonluMarkerEkle(lat, lng, '<div class="custom-marker-icon w-8 h-8 bg-emerald-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-shield-halved text-sm"></i></div>', elementId, popupIcerik);
                            
                            if(listDOM) {
                                let div = document.createElement('div');
                                div.id = elementId;
                                div.className = "list-item p-4 border-b border-gray-100 hover:bg-emerald-50 cursor-pointer transition-colors bg-white";
                                div.innerHTML = `<h3 class="text-sm font-bold text-gray-800 leading-tight mb-2">${ad}</h3><div class="flex items-center gap-2 text-[11px] text-gray-600"><span class="bg-gray-100 px-2 py-1 rounded">İlçe: ${ilce}</span><span class="bg-emerald-100 text-emerald-700 font-bold px-2 py-1 rounded">Kapasite: ${props.ALAN} m²</span></div>`;
                                div.onclick = () => { window.aktifListeOgesi(elementId, lat, lng, 16); };
                                fragment.appendChild(div);
                            }
                        }
                    });
                    if(listDOM) listDOM.appendChild(fragment);
                } else if(listDOM) {
                    listDOM.innerHTML = '<div class="p-6 text-center text-gray-500 font-medium">Sistemde toplanma alanı verisi bulunamadı.</div>';
                }
            }

            else if(endpoint === 'gezilecek_yerler') {
                if(Array.isArray(data) && data.length > 0) {
                    data.forEach((item, index) => {
                        let lat = parseFloat(item.latitude); let lng = parseFloat(item.longitude);
                        if(!isNaN(lat) && !isNaN(lng)) {
                            let elementId = "muze_" + index;
                            let title = duzenleMetin(toTitleCase(item.title));
                            let fullContent = item.content ? duzenleMetin(item.content) : 'Kayseri tarihi alanı.';
                            let shortContent = fullContent.length > 100 ? fullContent.substring(0, 100) + '...' : fullContent;
                            
                            let btnGeniSlet = fullContent.length > 100 ? `<button onclick="window.genisletMetin(event, this);" class="text-[11px] text-rose-600 font-bold mt-1 mb-2 block w-full text-center bg-rose-50 rounded py-1.5 hover:bg-rose-100 transition-colors">Açıklamayı Genişlet <i class="fa-solid fa-chevron-down"></i></button>` : '';
                            let resimHTML = item.bannerImage ? `<img src="${item.bannerImage}" style="height: 144px; width: 100%; object-fit: cover;" class="w-full h-36 object-cover rounded-md mb-2 shadow-sm border border-gray-200">` : '';
                            
                            let popupIcerik = document.createElement('div');
                            popupIcerik.className = "p-1 w-64 box-border";
                            popupIcerik.style.width = "240px";
                            popupIcerik.innerHTML = `
                                ${resimHTML}
                                <h3 class="font-bold text-[14px] text-rose-700 leading-tight mb-2">${title}</h3>
                                <div class="text-[11px] text-gray-600 mb-2 leading-tight">${shortContent}</div>
                                <div class="text-[11px] text-gray-600 mb-2 leading-tight hidden max-h-48 overflow-y-auto pr-1 scrollbar-hide">${fullContent}</div>
                                ${btnGeniSlet}
                                <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-2 rounded text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-2 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a>
                            `;
                            
                            animasyonluMarkerEkle(lat, lng, '<div class="custom-marker-icon w-8 h-8 bg-rose-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-building-columns text-sm"></i></div>', elementId, popupIcerik);
                            
                            if(listDOM) {
                                let div = document.createElement('div');
                                div.id = elementId;
                                div.className = "list-item p-4 border-b border-gray-200 hover:bg-rose-50 cursor-pointer transition-colors bg-white";
                                div.innerHTML = `
                                    <div class="flex items-start gap-3 w-full">
                                        ${item.bannerImage ? `<img src="${item.bannerImage}" loading="lazy" class="w-[72px] h-[72px] rounded-lg object-cover flex-shrink-0 shadow-sm border border-gray-200">` : `<div class="w-[72px] h-[72px] bg-gray-100 rounded-lg flex flex-shrink-0 items-center justify-center text-gray-400"><i class="fa-solid fa-image"></i></div>`}
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-[13px] font-bold text-gray-800 leading-tight mb-1.5 line-clamp-2">${title}</h3>
                                            <p class="text-[10px] text-gray-500 leading-snug line-clamp-3">${shortContent}</p>
                                        </div>
                                    </div>
                                `;
                                div.onclick = () => { window.aktifListeOgesi(elementId, lat, lng, 16); };
                                fragment.appendChild(div);
                            }
                        }
                    });
                    if(listDOM) listDOM.appendChild(fragment);
                } else if(listDOM) {
                    listDOM.innerHTML = '<div class="p-6 text-center text-gray-500 font-medium">Sistemde tarihi yer verisi bulunamadı.</div>';
                }
            }

            else if(endpoint === 'pazar_yerleri') {
                if(data && data.features && data.features.length > 0) {
                    data.features.forEach((item, index) => {
                        let props = item.properties || {};
                        if (item.geometry && item.geometry.coordinates) {
                            let elementId = "pazar_" + index;
                            let coords = item.geometry.coordinates; 
                            let ad = toTitleCase(props.ADI || 'Pazar Yeri');
                            let gun = toTitleCase(props.PAZARGUNAD || 'Belirsiz');
                            let aciklama = props.ACIKLAMA ? duzenleMetin(toTitleCase(props.ACIKLAMA)) : '';

                            let popupIcerik = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-basket-shopping text-xl text-sky-600"></i></div><h3 class="font-bold text-[14px] text-gray-800 leading-tight mb-1">${ad}</h3><span class="text-[11px] bg-sky-500 text-white px-2 py-1 rounded-full font-bold block mt-1 mb-2">${gun}</span><span class="text-[10px] text-gray-500 block mb-3">${aciklama}</span><a href="https://www.google.com/maps/dir/?api=1&destination=${coords[1]},${coords[0]}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                            
                            animasyonluMarkerEkle(coords[1], coords[0], '<div class="custom-marker-icon w-8 h-8 bg-sky-500 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-basket-shopping text-sm"></i></div>', elementId, popupIcerik);
                            
                            if(listDOM) {
                                let div = document.createElement('div');
                                div.id = elementId;
                                div.className = "list-item p-4 border-b border-gray-200 hover:bg-sky-50 cursor-pointer transition-colors bg-white";
                                div.innerHTML = `<div class="flex items-center gap-3"><div class="w-10 h-10 bg-sky-100 rounded flex items-center justify-center shrink-0"><i class="fa-solid fa-basket-shopping text-sky-600 text-lg"></i></div><div><h3 class="text-sm font-bold text-gray-800 leading-tight">${ad}</h3><span class="text-xs text-sky-600 font-medium mt-1 block"><i class="fa-regular fa-calendar-days mr-1"></i>${gun}</span>${aciklama ? `<span class="text-[10px] text-gray-500 mt-0.5 block">${aciklama}</span>` : ''}</div></div>`;
                                div.onclick = () => { window.aktifListeOgesi(elementId, coords[1], coords[0], 15); };
                                fragment.appendChild(div);
                            }
                        }
                    });
                    if(listDOM) listDOM.appendChild(fragment);
                } else if(listDOM) {
                    listDOM.innerHTML = '<div class="p-6 text-center text-gray-500 font-medium">Sistemde pazar yeri verisi bulunamadı.</div>';
                }
            }

            else if(endpoint === 'otoparklar') {
                if(data && data.features && data.features.length > 0) {
                    data.features.forEach((item, index) => {
                        let props = item.properties || {};
                        if (item.geometry && item.geometry.coordinates) {
                            let elementId = "otopark_" + index;
                            let coords = item.geometry.coordinates; 
                            let ad = toTitleCase(props.ADI || 'Şehir Otoparkı');

                            let popupIcerik = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-square-parking text-2xl text-gray-800"></i></div><h3 class="font-bold text-[13px] text-gray-800 leading-tight mb-3">${ad}</h3><a href="https://www.google.com/maps/dir/?api=1&destination=${coords[1]},${coords[0]}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                            
                            animasyonluMarkerEkle(coords[1], coords[0], '<div class="custom-marker-icon w-8 h-8 bg-gray-800 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md font-bold font-sans">P</div>', elementId, popupIcerik);
                            
                            if(listDOM) {
                                let div = document.createElement('div');
                                div.id = elementId;
                                div.className = "list-item p-4 border-b border-gray-200 hover:bg-gray-100 cursor-pointer transition-colors bg-white";
                                div.innerHTML = `<div class="flex items-center gap-3"><div class="w-10 h-10 bg-gray-800 rounded flex items-center justify-center shrink-0 font-bold text-white text-lg">P</div><div><h3 class="text-sm font-bold text-gray-800 leading-tight">${ad}</h3><span class="text-[10px] text-gray-500 mt-1 block">Otopark İşletmesi</span></div></div>`;
                                div.onclick = () => { window.aktifListeOgesi(elementId, coords[1], coords[0], 16); };
                                fragment.appendChild(div);
                            }
                        }
                    });
                    if(listDOM) listDOM.appendChild(fragment);
                } else if (listDOM) {
                    listDOM.innerHTML = '<div class="p-6 text-center text-gray-500 font-medium">Sistemde otopark verisi bulunamadı.</div>';
                }
            }

        })
        .catch(err => {
            console.error(err);
            let listDOM = listId ? document.getElementById(listId) : null;
            if(listDOM) listDOM.innerHTML = '<div class="p-6 text-center text-red-500 font-medium">Veri çekilirken hata oluştu.</div>';
        });
    });
}

function havaDurumuCek() {
    fetch('https://api.open-meteo.com/v1/forecast?latitude=38.7205&longitude=35.4826&current_weather=true')
    .then(res => res.json())
    .then(data => {
        if(data && data.current_weather) {
            document.getElementById('weather-widget').innerHTML = '<i class="fa-solid fa-cloud-sun text-yellow-500 mr-1"></i> Kayseri: ' + data.current_weather.temperature + '°C';
            document.getElementById('weather-source').innerText = "Hava: Open-Meteo API";
        }
    })
    .catch(err => {
        document.getElementById('weather-widget').innerHTML = '<i class="fa-solid fa-cloud-sun text-gray-500 mr-1"></i> Hava durumu alınamadı';
    });
}

const urlParams = new URLSearchParams(window.location.search);
const hedefHizmet = urlParams.get('hizmet');
let hedefIlce = urlParams.get('ilce');

if (hedefHizmet) {
    if (hedefHizmet === 'kaybis' || hedefHizmet === 'kaski_su_kesinti' || hedefHizmet === 'kameralar' || hedefHizmet === 'sarj_istasyonlari' || hedefHizmet === 'spor_tesisleri') {
        gosterKullanilamayanHizmetler();
    }
    veriCekVeGoster(hedefHizmet);
} else {
    veriCekVeGoster('tumu');
}

havaDurumuCek();
</script>
</body>
</html>