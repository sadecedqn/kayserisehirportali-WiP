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
        transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease-out;
        will-change: transform, opacity;
    }

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
            
            <div class="h-16 bg-blue-600/90 backdrop-blur-md text-white flex items-center px-4 rounded-2xl shadow-lg relative overflow-hidden border border-white/20">
                <div class="absolute inset-0 bg-cover bg-center opacity-20 filter blur-[2px]" style="background-image: url('assets/icons/kbb.png');"></div>
                <div class="flex items-center relative z-10 w-full">
                    <a href="index.php" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shrink-0 shadow-sm p-1 hover:scale-105 transition-transform" title="Ana Sayfaya Dön">
                        <img src="assets/icons/kbb.png" alt="KBB" class="w-full h-full object-contain">
                    </a>
                    <h1 class="text-[16px] font-bold ml-3 drop-shadow-md truncate">Kayseri Şehir Portalı</h1>
                </div>
            </div>

            <div class="relative">
                <div class="relative">
                    <input type="text" id="yer-arama" placeholder="Türkiye'de yer veya adres ara..." autocomplete="off" class="w-full bg-white/95 backdrop-blur-md border border-gray-200 text-sm rounded-full focus:ring-2 focus:ring-blue-500 focus:border-transparent block pl-10 p-2.5 shadow-md outline-none transition-all font-medium text-gray-700">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-blue-500"></i>
                    </div>
                </div>
                <ul id="arama-sonuclari" class="absolute z-50 w-full bg-white/95 backdrop-blur-md border border-gray-200 rounded-2xl shadow-2xl mt-2 hidden max-h-60 overflow-y-auto divide-y divide-gray-100"></ul>
            </div>

            <button onclick="veriCekVeGoster('tumu')" id="btn-hizmet-tumu" class="portal-filter-btn w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-md border-2 border-transparent font-bold text-[13px] text-gray-800 hover:bg-blue-50 transition-all flex items-center justify-center gap-2 cursor-pointer">
                <div class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                    <i class="fa-solid fa-earth-americas text-[11px]"></i>
                </div>
                <span>Tümü</span>
            </button>

            <div class="flex gap-2">
                <button id="tab-aktif" onclick="gosterKullanilabilirHizmetler()" class="flex-1 py-2 px-3 bg-white/95 backdrop-blur-md rounded-full shadow-md border-2 border-blue-500 font-bold text-[12px] text-gray-800 flex items-center justify-center gap-1.5 transition-all hover:bg-blue-50 cursor-pointer">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-[0_0_5px_rgba(34,197,94,0.6)]"></span> Aktif Hizmetler
                </button>
                <button id="tab-pasif" onclick="gosterKullanilamayanHizmetler()" class="flex-1 py-2 px-3 bg-white/95 backdrop-blur-md rounded-full shadow-md border-2 border-transparent font-bold text-[12px] text-gray-800 flex items-center justify-center gap-1.5 transition-all hover:bg-red-50 cursor-pointer">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-[0_0_5px_rgba(239,68,68,0.6)]"></span> Deaktif
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto scrollbar-hide my-3 space-y-2 pointer-events-auto pr-1">
            
            <button onclick="veriCekVeGoster('deprem')" id="btn-hizmet-deprem" class="portal-filter-btn active-service w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-md border-2 border-transparent font-bold text-[13px] text-gray-800 hover:bg-purple-50 transition-all flex items-center gap-3 cursor-pointer">
                <img src="assets/icons/kandilli.jpg" onerror="this.src='assets/icons/kandilli.png'" class="w-7 h-7 object-contain rounded-full shrink-0">
                <span>Son Depremler</span>
            </button>

            <button onclick="veriCekVeGoster('toplanma_alanlari')" id="btn-hizmet-toplanma_alanlari" class="portal-filter-btn active-service w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-md border-2 border-transparent font-bold text-[13px] text-gray-800 hover:bg-emerald-50 transition-all flex items-center gap-3 cursor-pointer">
                <img src="assets/icons/afad.png" class="w-7 h-7 object-contain shrink-0">
                <span>Afet Toplanma Alanları</span>
            </button>

            <button onclick="veriCekVeGoster('gezilecek_yerler')" id="btn-hizmet-gezilecek_yerler" class="portal-filter-btn active-service w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-md border-2 border-transparent font-bold text-[13px] text-rose-700 hover:bg-rose-50 transition-all flex items-center gap-3 cursor-pointer">
                <div class="w-7 h-7 bg-rose-600 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                    <i class="fa-solid fa-building-columns text-xs"></i>
                </div>
                <span>Müze ve Tarihi Yerler</span>
            </button>

            <button onclick="veriCekVeGoster('otoparklar')" id="btn-hizmet-otoparklar" class="portal-filter-btn active-service w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-md border-2 border-transparent font-bold text-[13px] text-gray-800 hover:bg-gray-100 transition-all flex items-center gap-3 cursor-pointer">
                <div class="w-7 h-7 bg-gray-800 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                    <i class="fa-solid fa-square-parking text-xs"></i>
                </div>
                <span>Şehir Otoparkları</span>
            </button>

            <button onclick="veriCekVeGoster('pazar_yerleri')" id="btn-hizmet-pazar_yerleri" class="portal-filter-btn active-service w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-md border-2 border-transparent font-bold text-[13px] text-sky-600 hover:bg-sky-50 transition-all flex items-center gap-3 cursor-pointer">
                <div class="w-7 h-7 bg-sky-500 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                    <i class="fa-solid fa-basket-shopping text-xs"></i>
                </div>
                <span>Semt Pazarları</span>
            </button>

            <button onclick="veriCekVeGoster('nobetci_eczane')" id="btn-hizmet-nobetci_eczane" class="portal-filter-btn active-service w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-md border-2 border-transparent font-bold text-[13px] text-gray-800 hover:bg-red-50 transition-all flex items-center gap-3 cursor-pointer">
                <img src="assets/icons/eczane.png" class="w-7 h-7 object-contain shrink-0">
                <span>Nöbetçi Eczaneler</span>
            </button>

            <button onclick="veriCekVeGoster('wifi_noktalari')" id="btn-hizmet-wifi_noktalari" class="portal-filter-btn active-service w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-md border-2 border-transparent font-bold text-[13px] text-green-700 hover:bg-green-50 transition-all flex items-center gap-3 cursor-pointer">
                <div class="w-7 h-7 bg-green-500 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                    <i class="fa-solid fa-wifi text-xs"></i>
                </div>
                <span>Ücretsiz Wi-Fi</span>
            </button>

            <button onclick="veriCekVeGoster('kameralar')" id="btn-hizmet-kameralar" class="portal-filter-btn inactive-service hidden w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-md border-2 border-transparent font-bold text-[13px] text-slate-800 hover:bg-slate-100 transition-all flex items-center gap-3 cursor-pointer">
                <div class="w-7 h-7 bg-slate-700 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                    <i class="fa-solid fa-video text-xs"></i>
                </div>
                <span>Şehir Kameraları</span>
            </button>

            <button onclick="veriCekVeGoster('kaybis')" id="btn-hizmet-kaybis" class="portal-filter-btn inactive-service hidden w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-md border-2 border-transparent font-bold text-[13px] text-gray-800 hover:bg-blue-50 transition-all flex items-center gap-3 cursor-pointer">
                <img src="assets/icons/kaybis.png" class="w-7 h-7 object-contain shrink-0">
                <span>KAYBİS İstasyonları</span>
            </button>

            <button onclick="veriCekVeGoster('kaski_su_kesinti')" id="btn-hizmet-kaski_su_kesinti" class="portal-filter-btn inactive-service hidden w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-md border-2 border-transparent font-bold text-[13px] text-gray-800 hover:bg-cyan-50 transition-all flex items-center gap-3 cursor-pointer">
                <img src="assets/icons/kaski.png" class="w-7 h-7 object-contain shrink-0">
                <span>Su Kesintileri</span>
            </button>

            <button onclick="veriCekVeGoster('sarj_istasyonlari')" id="btn-hizmet-sarj_istasyonlari" class="portal-filter-btn inactive-service hidden w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-md border-2 border-transparent font-bold text-[13px] text-amber-600 hover:bg-amber-50 transition-all flex items-center gap-3 cursor-pointer">
                <div class="w-7 h-7 bg-amber-500 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                    <i class="fa-solid fa-charging-station text-xs"></i>
                </div>
                <span>Elektrikli Şarj Noktaları</span>
            </button>

            <button onclick="veriCekVeGoster('spor_tesisleri')" id="btn-hizmet-spor_tesisleri" class="portal-filter-btn inactive-service hidden w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-md border-2 border-transparent font-bold text-[13px] text-indigo-600 hover:bg-indigo-50 transition-all flex items-center gap-3 cursor-pointer">
                <div class="w-7 h-7 bg-indigo-500 text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                    <i class="fa-solid fa-volleyball text-xs"></i>
                </div>
                <span>Spor Tesisleri ve Parklar</span>
            </button>
        </div>

        <div class="p-3.5 bg-white/95 backdrop-blur-md rounded-2xl shadow-lg border border-gray-200 pointer-events-auto shrink-0">
            <div class="flex items-center justify-between text-xs font-bold text-gray-700 mb-1">
                <span id="weather-widget"><i class="fa-solid fa-spinner fa-spin text-gray-400 mr-1"></i> Yükleniyor...</span>
            </div>
            <div class="flex justify-between items-end mt-1.5">
                <div class="text-[10px] text-gray-400 italic" id="weather-source"></div>
                <div class="text-[10px] text-gray-500 font-medium text-right" id="active-source"></div>
            </div>
        </div>

    </div>
    
    <div class="relative h-full z-10 pointer-events-none py-4">
        
        <div id="quake-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-500 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-purple-600 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold"><i class="fa-solid fa-house-crack mr-2"></i>Deprem Listesi</h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-gray-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-purple-50/80 border-b border-purple-100 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-deprem" oninput="listeAra('ara-deprem', 'quake-list')" placeholder="İl veya bölge ara (Örn: Muğla)..." class="w-full bg-white border border-purple-200 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-purple-400"></i></div>
                </div>
            </div>
            <div id="quake-list" class="flex-1 overflow-y-auto p-0 scrollbar-hide"></div>
        </div>
        
        <div id="afet-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-500 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-emerald-600 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold"><i class="fa-solid fa-shield-halved mr-2"></i>Toplanma Alanları</h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-gray-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-emerald-50/80 border-b border-emerald-100 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-afet" oninput="listeAra('ara-afet', 'afet-list')" placeholder="Mahalle veya alan ara..." class="w-full bg-white border border-emerald-200 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-emerald-400"></i></div>
                </div>
            </div>
            <div id="afet-list" class="flex-1 overflow-y-auto p-0 scrollbar-hide"></div>
        </div>
        
        <div id="muze-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-500 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-rose-600 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold"><i class="fa-solid fa-building-columns mr-2"></i>Tarihi Yerler</h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-gray-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-rose-50/80 border-b border-rose-100 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-muze" oninput="listeAra('ara-muze', 'muze-list')" placeholder="Müze veya eser ara..." class="w-full bg-white border border-rose-200 text-sm rounded-xl focus:ring-rose-500 focus:border-rose-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-rose-400"></i></div>
                </div>
            </div>
            <div id="muze-list" class="flex-1 overflow-y-auto p-0 bg-gray-50/50 scrollbar-hide"></div>
        </div>

        <div id="otopark-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-500 flex flex-col border border-gray-200 overflow-hidden">
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

        <div id="pazar-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-500 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-sky-500 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold"><i class="fa-solid fa-basket-shopping mr-2"></i>Semt Pazarları</h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-gray-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-sky-50/80 border-b border-sky-100 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-pazar" oninput="listeAra('ara-pazar', 'pazar-list')" placeholder="Pazar yeri veya gün ara..." class="w-full bg-white border border-sky-200 text-sm rounded-xl focus:ring-sky-500 focus:border-sky-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-sky-400"></i></div>
                </div>
            </div>
            <div id="pazar-list" class="flex-1 overflow-y-auto p-0 bg-gray-50/50 scrollbar-hide"></div>
        </div>
        
        <div id="eczane-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-500 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-red-600 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold"><i class="fa-solid fa-staff-snake mr-2"></i>Eczaneler</h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-gray-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-red-50/80 border-b border-red-100 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-eczane" oninput="listeAra('ara-eczane', 'eczane-list')" placeholder="Eczane ara..." class="w-full bg-white border border-red-200 text-sm rounded-xl focus:ring-red-500 focus:border-red-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-red-400"></i></div>
                </div>
            </div>
            <div id="eczane-list" class="flex-1 overflow-y-auto p-0 bg-gray-50/50 scrollbar-hide"></div>
        </div>

        <div id="wifi-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-500 flex flex-col border border-gray-200 overflow-hidden">
            <div class="p-4 bg-green-500 text-white flex justify-between items-center shrink-0">
                <h2 class="text-base font-bold"><i class="fa-solid fa-wifi mr-2"></i>Ücretsiz Wi-Fi</h2>
                <button onclick="kapatTumPaneller()" class="text-white hover:text-gray-200 focus:outline-none"><i class="fa-solid fa-times text-lg"></i></button>
            </div>
            <div class="p-3 bg-green-50/80 border-b border-green-100 shrink-0">
                <div class="relative">
                    <input type="text" id="ara-wifi" oninput="listeAra('ara-wifi', 'wifi-list')" placeholder="Wi-Fi noktası ara..." class="w-full bg-white border border-green-200 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block pl-9 p-2 outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-magnifying-glass text-green-400"></i></div>
                </div>
            </div>
            <div id="wifi-list" class="flex-1 overflow-y-auto p-0 bg-gray-50/50 scrollbar-hide"></div>
        </div>

        <div id="kamera-panel" class="pointer-events-auto absolute top-4 left-0 h-[calc(100%-2rem)] w-80 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl transform -translate-x-[300%] transition-transform duration-500 flex flex-col border border-gray-200 overflow-hidden">
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

<button onclick="document.getElementById('main-sidebar').classList.toggle('-translate-x-full')" class="absolute top-4 right-4 z-30 bg-white/95 backdrop-blur-md p-3 rounded-full shadow-lg lg:hidden pointer-events-auto">
    <i class="fa-solid fa-bars text-gray-800"></i>
</button>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="assets/script.js"></script>
</body>
</html>