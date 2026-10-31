<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayseri Şehir Portalı | Şehrin Dijital İkizi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        html { scroll-behavior: smooth; }
        
        .submenu-container > button:first-child {
            margin-top: 0.5rem !important; 
        }
        .submenu-container > button:last-child {
            margin-bottom: 0.75rem !important; 
        }
        
        .open-meteo-logo { transform: scale(1.3); }

        .marker-anim {
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease-out;
            will-change: transform, opacity;
        }

        .markers-locked .leaflet-marker-pane,
        .markers-locked .leaflet-shadow-pane,
        .markers-locked .leaflet-popup-pane {
            pointer-events: none !important;
        }

        .minimal-popup .leaflet-popup-content-wrapper {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            border-radius: 14px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.18);
            border: 1px solid rgba(255,255,255,0.9);
            padding: 0;
            overflow: hidden;
        }
        .minimal-popup .leaflet-popup-tip { background: rgba(255, 255, 255, 0.98); }
        .minimal-popup .leaflet-popup-content { margin: 8px 10px; line-height: 1.4; }

        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        
        .submenu-container {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-in-out, opacity 0.3s ease-in;
        }
        .submenu-open {
            max-height: 1000px;
            opacity: 1;
        }

        .bento-card {
            transition: opacity 0.28s ease, transform 0.32s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.3s ease, border-color 0.3s ease;
            will-change: opacity, transform;
        }
        .bento-fade-out {
            opacity: 0 !important;
            transform: scale(0.96) translateY(8px) !important;
            pointer-events: none !important;
        }
        .tech-details {
            display: none;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.35s ease, transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .bento-expanded .tech-details {
            display: block;
            margin-top: 1.75rem;
            padding-top: 1.75rem;
        }
        .bento-expanded.details-visible .tech-details {
            opacity: 1;
            transform: translateY(0);
        }

        .ecg-line {
            stroke-dasharray: 220;
            stroke-dashoffset: 220;
            animation: ecg-pulse 2.2s linear infinite;
            filter: drop-shadow(0 0 5px rgba(250, 204, 21, 0.85));
        }
        @keyframes ecg-pulse {
            0% { stroke-dashoffset: 220; opacity: 0.3; }
            15% { opacity: 1; }
            85% { opacity: 1; }
            100% { stroke-dashoffset: -220; opacity: 0.3; }
        }

        .code-inline {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.76rem;
            font-weight: 600;
            padding: 0.15rem 0.45rem;
            border-radius: 0.375rem;
            background: #020617;
            color: #38bdf8;
            border: 1px solid #1e293b;
            white-space: nowrap;
        }
        .code-inline .kw { color: #c084fc; }
        .code-inline .fn { color: #fde047; }
        .code-inline .str { color: #4ade80; }
        .code-inline .num { color: #fb923c; }
        .code-inline .prm { color: #f472b6; }

        .ide-block {
            background: #020617;
            border: 1px solid #1e293b;
            border-radius: 0.875rem;
            padding: 0.85rem 1.1rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.78rem;
            line-height: 1.6;
            color: #e2e8f0;
            overflow-x: auto;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.6);
        }
        .ide-block .cm { color: #64748b; font-style: italic; }
        .ide-block .kw { color: #c084fc; font-weight: 600; }
        .ide-block .fn { color: #60a5fa; font-weight: 600; }
        .ide-block .str { color: #4ade80; }
        .ide-block .num { color: #fb923c; }
        .ide-block .prp { color: #38bdf8; }
        .ide-block .op { color: #94a3b8; }

        .glow-card {
            position: relative;
            overflow: hidden;
        }
        .glow-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(
                280px circle at var(--mouse-x, -500px) var(--mouse-y, -500px),
                rgba(59, 130, 246, 0.22),
                rgba(168, 85, 247, 0.08) 40%,
                transparent 80%
            );
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
            z-index: 1;
        }
        .glow-card:hover::before {
            opacity: 1;
        }
        .glow-card > * {
            position: relative;
            z-index: 2;
        }

        .source-drawer-wrapper {
            display: grid;
            grid-template-rows: 0fr;
            opacity: 0;
            transform: translateY(-8px);
            transition: grid-template-rows 0.45s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.35s ease, transform 0.4s cubic-bezier(0.22, 1, 0.36, 1), margin-top 0.4s ease;
        }
        .source-drawer-wrapper.drawer-open {
            grid-template-rows: 1fr;
            opacity: 1;
            transform: translateY(0);
            margin-top: 1.5rem;
        }
        .source-drawer-inner {
            overflow: hidden;
        }
        .source-card-active {
            border-color: #2563eb !important;
            background-color: #ffffff !important;
            box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.12), 0 8px 10px -6px rgba(37, 99, 235, 0.1) !important;
            transform: translateY(-4px);
        }

        @keyframes scroll-left {
            0% { transform: translateX(0); }
            100% { transform: translateX(calc(-280px * 8)); } 
        }
        @keyframes scroll-right {
            0% { transform: translateX(calc(-280px * 8)); }
            100% { transform: translateX(0); }
        }
        .marquee-track-left { display: flex; gap: 20px; width: max-content; animation: scroll-left 38s linear infinite; }
        .marquee-track-right { display: flex; gap: 20px; width: max-content; animation: scroll-right 38s linear infinite; }
        .marquee-item { width: 260px; flex-shrink: 0; }
        .marquee-mask { mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent); -webkit-mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent); }
        .map-scroll-passthrough { touch-action: pan-y !important; }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased text-gray-800 selection:bg-blue-600 selection:text-white">

    <nav class="w-full bg-white/80 backdrop-blur-md fixed top-0 z-50 border-b border-gray-200 transition-transform duration-500" id="main-nav">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 sm:h-20 items-center gap-2">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <div class="w-10 h-10 sm:w-14 sm:h-14 flex items-center justify-center bg-white rounded-lg shadow-sm p-1 border border-gray-100 shrink-0">
                        <img src="assets/icons/kbb.png" alt="Kayseri BB" class="w-full h-full object-contain">
                    </div>
                    <span class="font-bold text-[15px] sm:text-2xl tracking-tight text-gray-900 whitespace-nowrap">Kayseri Şehir Portalı</span>
                </div>
                <div class="flex items-center shrink-0">
                    <a href="portal.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 sm:px-6 sm:py-2.5 rounded-full text-xs sm:text-base font-bold shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 whitespace-nowrap">
                        Portal'a Giriş <i class="fa-solid fa-arrow-right ml-1 sm:ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div id="hero-wrapper" class="relative h-screen min-h-[600px] w-full overflow-hidden bg-gray-100 map-scroll-passthrough">
        
        <div id="hero-map" class="absolute inset-0 z-0 map-scroll-passthrough markers-locked"></div>
        <div id="hero-overlay" class="absolute inset-0 bg-white/85 backdrop-blur-sm z-0 pointer-events-none transition-all duration-1000 ease-out"></div>
        
        <div id="hero-filter-menu" class="absolute top-24 left-4 z-20 flex flex-col items-start gap-2 opacity-0 -translate-y-full transition-all duration-1000 ease-out pointer-events-none w-[280px] max-h-[calc(100vh-8rem)] overflow-y-auto scrollbar-hide pr-2">

            <button onclick="document.getElementById('ana-menu-icerik').classList.toggle('hidden');" class="w-full px-4 py-3 bg-slate-900/90 backdrop-blur-md rounded-xl shadow-lg border border-slate-700 font-bold text-[13px] text-white transition-all hover:bg-slate-800 flex items-center justify-between z-30 pointer-events-auto">
                <span class="flex items-center gap-2"><i class="fa-solid fa-layer-group text-blue-400"></i> Harita Katmanları</span>
                <i class="fa-solid fa-chevron-down text-gray-300"></i>
            </button>

            <div id="ana-menu-icerik" class="w-full flex flex-col gap-2 pointer-events-auto">
                <button onclick="filterHeroMap('all'); closeSubmenus();" id="btn-filter-all" class="hero-filter-btn w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-xl shadow-md border-2 border-blue-500 font-bold text-[12px] text-gray-800 transition-all hover:bg-blue-50 flex items-center gap-2 text-left z-30">
                    <i class="fa-solid fa-globe text-blue-500 w-4 text-center"></i> Tümü
                </button>
                
                <div class="w-full flex flex-col gap-1.5 shrink-0">
                    <button onclick="toggleSubmenu('aktif-submenu', 'deaktif-submenu')" class="w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-xl shadow-md border border-gray-200 font-bold text-[12px] text-gray-800 hover:bg-gray-50 transition-all flex items-center justify-between z-30">
                        <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-[0_0_5px_rgba(34,197,94,0.5)]"></span> Aktif Hizmetler</span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-gray-400"></i>
                    </button>
                    <div id="aktif-submenu" class="submenu-container scrollbar-hide flex flex-col gap-1.5 pl-2">
                        <button onclick="filterHeroMap('eczane')" id="btn-filter-eczane" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] hover:bg-red-50 transition-all flex items-center gap-2 text-left"><img src="assets/icons/eczane.png" class="w-4 h-4 object-contain"> Nöbetçi Eczaneler</button>
                        <button onclick="filterHeroMap('deprem')" id="btn-filter-deprem" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] hover:bg-purple-50 transition-all flex items-center gap-2 text-left"><img src="assets/icons/kandilli.jpg" onerror="this.src='assets/icons/kandilli.png'" class="w-5 h-5 object-contain rounded-full">Son Depremler</button>
                        <button onclick="filterHeroMap('afad')" id="btn-filter-afad" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] hover:bg-emerald-50 transition-all flex items-center gap-2 text-left"><img src="assets/icons/afad2.png" onerror="this.src='assets/icons/afad.png'" class="w-5 h-5 object-contain"> Afet Toplanma Alanları</button>
                        <button onclick="filterHeroMap('otopark')" id="btn-filter-otopark" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] text-gray-800 hover:bg-gray-100 transition-all flex items-center gap-2 text-left"><i class="fa-solid fa-square-parking text-sm w-4 text-center"></i> Şehir Otoparkları</button>
                        <button onclick="filterHeroMap('wifi')" id="btn-filter-wifi" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] text-green-700 hover:bg-green-50 transition-all flex items-center gap-2 text-left"><i class="fa-solid fa-wifi text-xs w-4 text-center"></i> Ücretsiz Wi-Fi Noktaları</button>
                        <button onclick="filterHeroMap('pazar')" id="btn-filter-pazar" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] text-sky-600 hover:bg-sky-50 transition-all flex items-center gap-2 text-left"><i class="fa-solid fa-basket-shopping text-xs w-4 text-center"></i> Pazarlar</button>
                        <button onclick="filterHeroMap('tarihi')" id="btn-filter-tarihi" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] text-rose-700 hover:bg-rose-50 transition-all flex items-center gap-2 text-left"><img src="assets/icons/kültür2.png" onerror="this.src='assets/icons/kültür.png'" class="w-4 h-4 object-contain"> Tarihi Yerler</button>
                        <button onclick="filterHeroMap('vefat')" id="btn-filter-vefat" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] text-zinc-800 hover:bg-zinc-100 transition-all flex items-center gap-2 text-left"><i class="fa-solid fa-ribbon text-xs w-4 text-center text-zinc-700"></i> Vefat İlanları</button>
                        <button onclick="filterHeroMap('muhtar')" id="btn-filter-muhtar" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] text-teal-700 hover:bg-teal-50 transition-all flex items-center gap-2 text-left"><i class="fa-solid fa-users text-xs w-4 text-center text-teal-600"></i> Mahalle/Muhtarlık Bilgileri</button>
                        <button onclick="filterHeroMap('cadde_sokak')" id="btn-filter-cadde_sokak" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] text-indigo-700 hover:bg-indigo-50 transition-all flex items-center gap-2 text-left"><i class="fa-solid fa-road text-xs w-4 text-center text-indigo-600"></i> İlçe İstatistikleri</button>
                        <button onclick="filterHeroMap('hal')" id="btn-filter-hal" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] text-orange-600 hover:bg-orange-50 transition-all flex items-center gap-2 text-left mb-2.5"><img src="assets/icons/hal.png" onerror="this.onerror=null; this.src='assets/icons/kbb.png';" class="w-4 h-4 object-contain rounded-full"> Hal Fiyatları</button>
                    </div>
                </div>

                <div class="w-full flex flex-col gap-1.5 shrink-0">
                    <button onclick="toggleSubmenu('deaktif-submenu', 'aktif-submenu')" class="w-full px-4 py-2.5 bg-white/95 backdrop-blur-md rounded-xl shadow-md border border-gray-200 font-bold text-[12px] text-gray-800 hover:bg-gray-50 transition-all flex items-center justify-between z-30">
                        <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-[0_0_5px_rgba(239,68,68,0.5)]"></span> Deaktif Hizmetler</span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-gray-400"></i>
                    </button>
                    <div id="deaktif-submenu" class="submenu-container scrollbar-hide flex flex-col gap-1.5 pl-2">
                        <button onclick="filterHeroMap('kamera')" id="btn-filter-kamera" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] text-slate-800 hover:bg-slate-100 transition-all flex items-center gap-2 text-left"><i class="fa-solid fa-video text-xs w-4 text-center"></i> Şehir Kameraları</button>
                        <button onclick="filterHeroMap('kaybis')" id="btn-filter-kaybis" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] hover:bg-blue-50 transition-all flex items-center gap-2 text-left"><img src="assets/icons/kaybis.png" class="w-4 h-4 object-contain"> KAYBİS İstasyonları</button>
                        <button onclick="filterHeroMap('kaski')" id="btn-filter-kaski" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] hover:bg-cyan-50 transition-all flex items-center gap-2 text-left"><img src="assets/icons/kaski.png" class="w-4 h-4 object-contain"> Su Kesintileri</button>
                        <button onclick="filterHeroMap('sarj')" id="btn-filter-sarj" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] text-amber-600 hover:bg-amber-50 transition-all flex items-center gap-2 text-left"><i class="fa-solid fa-charging-station text-xs w-4 text-center"></i> Elektrikli Şarj Noktaları</button>
                        <button onclick="filterHeroMap('spor')" id="btn-filter-spor" class="hero-filter-btn w-full px-3 py-2 bg-white/95 backdrop-blur-md rounded-lg shadow-sm border-2 border-transparent font-bold text-[11px] text-indigo-600 hover:bg-indigo-50 transition-all flex items-center gap-2 text-left"><i class="fa-solid fa-volleyball text-xs w-4 text-center"></i> Spor Tesisleri ve Parklar</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="hero-content" class="absolute inset-0 z-10 flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8 text-center origin-center pointer-events-none mt-8 sm:mt-10">
            
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-full bg-blue-50 text-blue-700 font-semibold text-xs sm:text-sm mb-5 sm:mb-6 border border-blue-100 shadow-sm pointer-events-auto">
                <span class="relative flex h-2.5 w-2.5 sm:h-3 sm:w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 sm:h-3 sm:w-3 bg-blue-600"></span></span>
                Gerçek Zamanlı Açık Veri Entegrasyonu
            </div>
            
            <h1 class="text-4xl sm:text-5xl md:text-7xl font-extrabold tracking-tight text-gray-900 mb-6 sm:mb-8 leading-tight">
                KAYSERİ'NİN DİJİTAL İKİZİ <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">TEK BİR HARİTADA.</span>
            </h1>
            
            <p class="mt-2 sm:mt-4 max-w-2xl mx-auto text-base sm:text-xl text-gray-700 font-medium mb-8 sm:mb-10 drop-shadow-sm px-2">
                Kayseri Büyükşehir Belediyesi, Kandilli Rasathanesi ve AFAD gibi güvenilir kaynaklardan alınan anlık verilerle şehri keşfedin, planlayın ve güvende kalın.
            </p>
            
            <a href="#nasil-calisir" class="inline-block bg-gray-900 hover:bg-gray-800 text-white px-7 py-3.5 sm:px-8 sm:py-4 rounded-2xl font-bold text-base sm:text-lg shadow-2xl hover:shadow-gray-900/20 transition-all transform hover:-translate-y-1 pointer-events-auto">
                NASIL ÇALIŞIR? <i class="fa-solid fa-arrow-down ml-2"></i>
            </a>
            
            <div class="mt-10 sm:mt-12 text-xs sm:text-sm text-gray-500 animate-bounce bg-white/80 backdrop-blur-md px-4 py-2 sm:px-5 rounded-full border border-gray-200 shadow-sm pointer-events-none">
                <i class="fa-solid fa-hand-pointer mr-1"></i>
                <span class="hidden md:inline">Keşfetmek için haritaya tıklayın...</span>
                <span class="inline md:hidden font-semibold text-gray-600">Keşfetmek için haritaya çift dokunun...</span>
            </div>
            
        </div>
    </div>


    <section id="nasil-calisir" class="relative z-20 bg-[#050811] pt-12 sm:pt-16 pb-20 sm:pb-28 border-t border-slate-800 shadow-[0_-20px_60px_rgba(0,0,0,0.6)] overflow-hidden">
        
        <canvas id="city-twin-canvas" class="absolute inset-0 w-full h-full z-0 block"></canvas>

        <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-[#050811] to-transparent pointer-events-none z-0"></div>
        <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-[#0b1120] to-transparent pointer-events-none z-0"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pointer-events-none">
            
            <div class="pointer-events-auto grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-14 sm:mb-20 bg-slate-900/80 text-white backdrop-blur-xl p-4 sm:p-6 rounded-3xl shadow-[0_15px_40px_rgba(0,0,0,0.6)] border border-slate-800/90">
                
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-4 p-2 sm:px-4 border-r border-slate-800">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-blue-500/20 border border-blue-500/30 text-blue-400 flex items-center justify-center text-lg sm:text-xl shrink-0"><i class="fa-solid fa-bolt"></i></div>
                    <div class="min-w-0">
                        <div class="text-lg sm:text-2xl font-black text-white tracking-tight leading-tight">5 Dakika</div>
                        <div class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-normal sm:tracking-wider leading-snug break-words">Veri Senkronizasyonu</div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-4 p-2 sm:px-4 lg:border-r border-slate-800">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-lg sm:text-xl shrink-0"><i class="fa-solid fa-layer-group"></i></div>
                    <div class="min-w-0">
                        <div class="text-lg sm:text-2xl font-black text-white tracking-tight leading-tight">16+ Katman</div>
                        <div class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-normal sm:tracking-wider leading-snug break-words">Mekansal & CBS Veri Seti</div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-4 p-2 sm:px-4 border-r border-slate-800 pt-4 lg:pt-0 border-t lg:border-t-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-purple-500/20 border border-purple-500/30 text-purple-400 flex items-center justify-center text-lg sm:text-xl shrink-0"><i class="fa-solid fa-tower-broadcast"></i></div>
                    <div class="min-w-0">
                        <div class="text-lg sm:text-2xl font-black text-white tracking-tight leading-tight">7/24 Canlı</div>
                        <div class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-normal sm:tracking-wider leading-snug break-words">Kesintisiz Akış</div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-4 p-2 sm:px-4 pt-4 lg:pt-0 border-t lg:border-t-0 border-slate-800">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-amber-500/20 border border-amber-500/30 text-amber-400 flex items-center justify-center text-lg sm:text-xl shrink-0"><i class="fa-solid fa-code-branch"></i></div>
                    <div class="min-w-0">
                        <div class="text-lg sm:text-2xl font-black text-white tracking-tight leading-tight">%100 Açık</div>
                        <div class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-normal sm:tracking-wider leading-snug break-words">Şeffaf API Altyapısı</div>
                    </div>
                </div>

            </div>

            <div class="max-w-3xl mx-auto text-center mb-10 sm:mb-16">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-500/10 border border-blue-400/30 text-cyan-400 text-xs font-bold uppercase tracking-widest mb-4 shadow-lg backdrop-blur-md">
                    <i class="fa-solid fa-microchip"></i> Sistem Mimarisi
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight leading-tight drop-shadow-[0_4px_24px_rgba(0,0,0,0.9)]">
                    Şehrin Verisi Nasıl İşleniyor?
                </h2>
                <p class="mt-4 text-base sm:text-lg text-slate-300 font-medium drop-shadow-[0_2px_12px_rgba(0,0,0,0.9)]">
                    Dağınık kurumsal verileri tek bir merkezde toplayan, anlamlandıran ve gerçek zamanlı olarak haritalandıran yeni nesil açık veri motoru.
                </p>
            </div>

            <div id="bento-container" class="pointer-events-auto grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-6 relative">
                
                <div id="bento-1" data-col="md:col-span-7" class="bento-card md:col-span-7 group bg-slate-900/75 hover:bg-slate-900/90 backdrop-blur-xl text-white rounded-[1.75rem] sm:rounded-[2rem] p-6 sm:p-8 shadow-2xl border border-slate-700/70 hover:border-blue-500/50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-blue-600/20 border border-blue-500/40 text-blue-400 flex items-center justify-center text-xl sm:text-2xl shadow-lg shadow-blue-600/20">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> Canlı Senkronizasyon
                            </span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-extrabold text-white mb-2.5">Otomatik Veri Senkronizasyonu</h3>
                        <p class="text-slate-300 leading-relaxed text-sm sm:text-base">
                            Arka planda çalışan otonom servislerimiz, Kayseri Büyükşehir Belediyesi ve ilgili resmi kurumların sunucularıyla <strong class="text-white">her 5 dakikada bir</strong> haberleşir. Nöbetçi eczanelerden vefat ilanlarına, hal fiyatlarından Wi-Fi noktalarına kadar tüm değişiklikler anında haritaya yansır.
                        </p>
                    </div>

                    <div class="tech-details border-t border-slate-800 text-slate-300 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-800/70 p-4 rounded-2xl border border-slate-700">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-400 block mb-1">Veri Çekme Protokolü</span>
                                <p class="text-xs font-semibold text-white">Asenkron cURL / RESTful Polling & GeoJSON Stream</p>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-cyan-400 block mb-1">Önbellek & TTL Süresi</span>
                                <p class="text-xs font-semibold text-white">300 Saniye (5 Dk) Stale-While-Revalidate Cache</p>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400 block mb-1">Veri Sanitizasyonu</span>
                                <p class="text-xs font-semibold text-white">UTF-8 Normalizasyonu & EPSG:4326 Koordinat Doğrulama</p>
                            </div>
                        </div>

                        <div class="space-y-3 text-sm leading-relaxed">
                            <h4 class="font-extrabold text-white text-base"><i class="fa-solid fa-diagram-project text-blue-400 mr-2"></i>1. Dağıtık Veri Toplama ve Proxy Katmanı (API Gateway)</h4>
                            <p>
                                Kurumsal açık veri kaynakları (Kayseri BB Açık Veri Portalı, Nöbetçi Eczane, Vefat İlanları, Hal Fiyatları, Muhtarlık ve Kültür Envanteri) farklı veri şemalarında yayın yapar. Sunucu tarafındaki <span class="code-inline"><span class="str">api/api_getir.php</span>?<span class="prm">endpoint</span>=<span class="fn">{servis}</span></span> mikro-proxy katmanımız, istemciyi CORS kısıtlamalarından ve harici sunucu gecikmelerinden izole eder.
                            </p>

                            <h4 class="font-extrabold text-white text-base pt-2"><i class="fa-solid fa-memory text-blue-400 mr-2"></i>2. Önbellekleme (Caching) ve Hata Toleransı (Failover)</h4>
                            <p>
                                Binlerce kullanıcının aynı anda haritayı açması durumunda resmi kurumların API sunucularına aşırı yük binmesini önlemek amacıyla <strong class="text-white">yerel önbellekleme (Local Snapshot Cache)</strong> uygulanır. İlgili kurumun sunucusu geçici olarak yanıt vermezse (HTTP 5xx veya Timeout), sistem son başarılı senkronize edilmiş veri paketini sunmaya devam eder.
                            </p>

                            <h4 class="font-extrabold text-white text-base pt-2"><i class="fa-solid fa-code text-blue-400 mr-2"></i>3. Şema Standardizasyonu ve Koordinat Doğrulama</h4>
                            <p>
                                Ham API çıktılarındaki bozuk karakter kodlamaları <span class="code-inline"><span class="fn">turkceKarakterDuzelt</span>(<span class="prm">str</span>)</span> ve Regex tabanlı <span class="code-inline"><span class="fn">duzenleMetin</span>(<span class="prm">metin</span>)</span> fonksiyonlarından geçirilir. Enlem ve boylam değerleri ise <span class="code-inline"><span class="fn">parseFloat</span>(<span class="prm">e.latitude</span>)</span> ve <span class="code-inline">!<span class="fn">isNaN</span>(<span class="prm">lat</span>)</span> filtreleriyle doğrulanır:
                            </p>
                            <div class="ide-block">
                                <span class="cm">// Asenkron Veri Çekme ve Koordinat Sanitizasyon Hattı</span><br>
                                <span class="fn">fetch</span>(<span class="str">'api/api_getir.php?endpoint=nobetci_eczane'</span>)<br>
                                &nbsp;&nbsp;.<span class="fn">then</span>(<span class="prp">r</span> <span class="op">=&gt;</span> <span class="prp">r</span>.<span class="fn">json</span>())<br>
                                &nbsp;&nbsp;.<span class="fn">then</span>(<span class="prp">data</span> <span class="op">=&gt;</span> {<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;<span class="kw">const</span> <span class="prp">lat</span> <span class="op">=</span> <span class="fn">parseFloat</span>(<span class="prp">item.latitude</span>), <span class="prp">lng</span> <span class="op">=</span> <span class="fn">parseFloat</span>(<span class="prp">item.longitude</span>);<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;<span class="kw">if</span> (!<span class="fn">isNaN</span>(<span class="prp">lat</span>) <span class="op">&amp;&amp;</span> !<span class="fn">isNaN</span>(<span class="prp">lng</span>)) <span class="fn">addPortalMarker</span>(<span class="prp">lat</span>, <span class="prp">lng</span>, <span class="prp">icon</span>, <span class="prp">popup</span>, <span class="str">'eczane'</span>);<br>
                                &nbsp;&nbsp;});
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between">
                        <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-slate-400">
                            <span class="px-3 py-1.5 bg-slate-800/90 border border-slate-700 rounded-lg text-slate-200"><i class="fa-solid fa-server mr-1.5 text-blue-400"></i>REST API</span>
                            <span class="px-3 py-1.5 bg-slate-800/90 border border-slate-700 rounded-lg text-slate-200"><i class="fa-solid fa-file-code mr-1.5 text-emerald-400"></i>GeoJSON</span>
                            <span class="px-3 py-1.5 bg-slate-800/90 border border-slate-700 rounded-lg text-slate-200"><i class="fa-solid fa-bolt mr-1.5 text-amber-400"></i>Cache</span>
                        </div>
                        <button onclick="toggleBentoDetails('bento-1')" title="Detayları Aç / Kapat" class="w-10 h-10 rounded-full bg-blue-500/20 hover:bg-blue-600 text-blue-300 hover:text-white border border-blue-500/30 transition-all flex items-center justify-center cursor-pointer focus:outline-none shadow-sm shrink-0">
                            <i class="fa-solid fa-arrow-right btn-icon transition-transform duration-300"></i>
                        </button>
                    </div>
                </div>

                <div id="bento-2" data-col="md:col-span-5" class="bento-card md:col-span-5 group bg-slate-900/75 hover:bg-slate-900/90 backdrop-blur-xl text-white rounded-[1.75rem] sm:rounded-[2rem] p-6 sm:p-8 shadow-2xl border border-slate-700/70 hover:border-purple-500/50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-purple-500/20 border border-purple-400/30 text-purple-300 flex items-center justify-center text-xl sm:text-2xl">
                                <i class="fa-solid fa-satellite-dish"></i>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30 uppercase tracking-wider">
                                Kandilli & AFAD
                            </span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-extrabold text-white mb-2.5">Canlı Sismik ve Afet Takibi</h3>
                        <p class="text-slate-300 leading-relaxed text-sm">
                            Kandilli Rasathanesi'nden alınan anlık sismik veriler ile AFAD acil toplanma alanları tek bir kriz ekranında birleştirilir. Olası bir afet anında en yakın güvenli bölgeye saniyeler içinde rota oluşturulur.
                        </p>
                    </div>

                    <div class="tech-details border-t border-slate-800 text-slate-300 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-800/70 p-4 rounded-2xl border border-slate-700">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-purple-400 block mb-1">Sismik Veri Kaynağı</span>
                                <p class="text-xs font-semibold text-white">Boğaziçi Üni. Kandilli Rasathanesi Canlı Akışı (Son 100 Deprem)</p>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400 block mb-1">Güvenli Alan Katmanı</span>
                                <p class="text-xs font-semibold text-white">AFAD & KBB Coğrafi Toplanma Alanları (Kapasite m² Verili)</p>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400 block mb-1">Kamera Dinamiği</span>
                                <p class="text-xs font-semibold text-white">Otomatik Türkiye Geneli (Zoom 6) / İl Merkezi (Zoom 12) Geçişi</p>
                            </div>
                        </div>

                        <div class="space-y-3 text-sm leading-relaxed">
                            <h4 class="font-extrabold text-white text-base"><i class="fa-solid fa-wave-square text-purple-400 mr-2"></i>1. Gerçek Zamanlı Sismik Telemetri Ayrıştırma (Parsing)</h4>
                            <p>
                                Kandilli Rasathanesi'nin sunduğu canlı sismik akıştan gelen son 100 sarsıntı verisi anlık olarak çekilir. Her bir deprem kaydının GeoJSON <span class="code-inline">[<span class="prm">boylam</span>, <span class="prm">enlem</span>]</span> dizilimi, Leaflet'in <span class="code-inline"><span class="fn">L.latLng</span>(<span class="prm">coords[1]</span>, <span class="prm">coords[0]</span>)</span> standardına tersine çevrilerek haritalandırılır. Deprem büyüklüğü <span class="code-inline"><span class="fn">parseFloat</span>(<span class="prm">deprem.mag</span>).<span class="fn">toFixed</span>(<span class="num">1</span>)</span> ile yuvarlanır.
                            </p>

                            <h4 class="font-extrabold text-white text-base pt-2"><i class="fa-solid fa-shield-halved text-emerald-400 mr-2"></i>2. AFAD Toplanma Alanları ve Kapasite Entegrasyonu</h4>
                            <p>
                                AFAD ve belediye coğrafi bilgi sistemlerinden (CBS/GIS) alınan toplanma alanı verileri; ilçe adı (<span class="code-inline"><span class="prm">props.ILCEAD</span></span>), tesis adı ve metrekare cinsinden toplam alan kapasitesi (<span class="code-inline"><span class="prm">props.ALAN</span> <span class="str">'m²'</span></span>) parametreleriyle birlikte sisteme yüklenir.
                            </p>

                            <div class="ide-block">
                                <span class="cm">// Dinamik Sismik Kamera Ölçeklendirme ve Koordinat Dönüşümü</span><br>
                                <span class="kw">const</span> <span class="prp">targetZoom</span> <span class="op">=</span> (<span class="prp">category</span> <span class="op">===</span> <span class="str">'deprem'</span>) <span class="op">?</span> <span class="num">6</span> <span class="op">:</span> <span class="prp">initialZoom</span>;<br>
                                <span class="kw">const</span> <span class="prp">targetCenter</span> <span class="op">=</span> (<span class="prp">category</span> <span class="op">===</span> <span class="str">'deprem'</span>) <span class="op">?</span> <span class="fn">L.latLng</span>(<span class="num">39.2</span>, <span class="num">35.0</span>) <span class="op">:</span> <span class="fn">L.latLng</span>(<span class="num">38.7205</span>, <span class="num">35.4826</span>);<br>
                                <span class="prp">heroMap</span>.<span class="fn">flyTo</span>(<span class="prp">targetCenter</span>, <span class="prp">targetZoom</span>, { <span class="prp">duration</span>: <span class="num">1.2</span>, <span class="prp">easeLinearity</span>: <span class="num">0.25</span> });
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-16 h-7 bg-slate-950/90 rounded-lg border border-amber-500/30 flex items-center justify-center px-1.5 overflow-hidden shadow-inner">
                                <svg viewBox="0 0 110 32" class="w-full h-full overflow-visible">
                                    <path d="M 0,16 L 14,16 Q 18,10 22,16 L 28,16 L 32,23 L 38,3 L 44,29 L 49,13 Q 53,16 56,16 L 64,16 Q 70,7 76,16 L 84,16 Q 88,11 92,16 L 110,16" fill="none" stroke="rgba(250, 204, 21, 0.18)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path class="ecg-line" d="M 0,16 L 14,16 Q 18,10 22,16 L 28,16 L 32,23 L 38,3 L 44,29 L 49,13 Q 53,16 56,16 L 64,16 Q 70,7 76,16 L 84,16 Q 88,11 92,16 L 110,16" fill="none" stroke="#facc15" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-amber-300 tracking-wide">Sismik Sensör Ağı Aktif</span>
                        </div>
                        <button onclick="toggleBentoDetails('bento-2')" title="Detayları Aç / Kapat" class="w-10 h-10 rounded-full bg-purple-500/20 hover:bg-purple-500 text-purple-300 hover:text-white border border-purple-500/30 transition-all flex items-center justify-center cursor-pointer focus:outline-none shrink-0">
                            <i class="fa-solid fa-arrow-right btn-icon transition-transform duration-300"></i>
                        </button>
                    </div>
                </div>

                <div id="bento-3" data-col="md:col-span-3" class="bento-card md:col-span-3 group bg-slate-900/75 hover:bg-slate-900/90 backdrop-blur-xl text-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-2xl border border-slate-700/70 hover:border-emerald-500/50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3.5 mb-3">
                            <div class="w-11 h-11 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <h4 class="text-base sm:text-lg font-extrabold text-white leading-snug">Vektörel Harita Motoru</h4>
                        </div>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            Leaflet.js ve Canvas tabanlı harita mimarisi, binlerce lokasyon noktasını ve poligon sınırlarını sıfır gecikme ile işler.
                        </p>
                    </div>
                    
                    <div class="tech-details border-t border-slate-800 text-slate-300 space-y-4 text-sm leading-relaxed">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-slate-800/70 p-3.5 rounded-xl border border-slate-700">
                            <div><span class="text-[10px] font-bold uppercase text-emerald-400 block">Render Motoru</span><b class="text-xs text-white">HTML5 Canvas & GPU Compositing</b></div>
                            <div><span class="text-[10px] font-bold uppercase text-emerald-400 block">Kare Hızı Hedefi</span><b class="text-xs text-white">Sabit 60 FPS (requestAnimationFrame)</b></div>
                            <div><span class="text-[10px] font-bold uppercase text-emerald-400 block">Poligon Motoru</span><b class="text-xs text-white">L.geoJSON SmoothFactor 2.5</b></div>
                        </div>
                        <p>
                            <strong class="text-white">1. Donanımsal Hızlandırma (Hardware Acceleration):</strong> Geleneksel web haritalarında her bir işaretçi ayrı bir DOM düğümü olarak eklendiğinde 500+ noktada tarayıcıda ciddi "Reflow / Repaint" darboğazları oluşur. Sistemimizde aktif edilen <span class="code-inline"><span class="prp">preferCanvas</span>: <span class="kw">true</span></span> parametresi ve ilçe/mahalle sınır poligonlarında kullanılan <span class="code-inline"><span class="prp">smoothFactor</span>: <span class="num">2.5</span></span> optimizasyonu sayesinde çizim yükü doğrudan ekran kartına (GPU) devredilir.
                        </p>
                        <div class="ide-block">
                            <span class="cm">// GPU Hızlandırmalı Harita Başlatıcı ve Poligon Sınır Render</span><br>
                            <span class="kw">const</span> <span class="prp">heroMap</span> <span class="op">=</span> <span class="fn">L.map</span>(<span class="str">'hero-map'</span>, {<br>
                            &nbsp;&nbsp;<span class="prp">preferCanvas</span>: <span class="kw">true</span>, <span class="prp">zoomControl</span>: <span class="kw">false</span>, <span class="prp">scrollWheelZoom</span>: <span class="kw">false</span><br>
                            }).<span class="fn">setView</span>([<span class="num">38.7205</span>, <span class="num">35.4826</span>], <span class="num">12</span>);
                        </div>
                    </div>

                    <div class="mt-4 pt-3 flex justify-end">
                        <button onclick="toggleBentoDetails('bento-3')" title="Detayları Aç / Kapat" class="w-9 h-9 rounded-full bg-emerald-500/20 hover:bg-emerald-500 text-emerald-300 hover:text-white border border-emerald-500/30 transition-all flex items-center justify-center cursor-pointer focus:outline-none">
                            <i class="fa-solid fa-arrow-right btn-icon transition-transform duration-300 text-sm"></i>
                        </button>
                    </div>
                </div>

                <div id="bento-4" data-col="md:col-span-3" class="bento-card md:col-span-3 group bg-slate-900/75 hover:bg-slate-900/90 backdrop-blur-xl text-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-2xl border border-slate-700/70 hover:border-amber-500/50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3.5 mb-3">
                            <div class="w-11 h-11 rounded-xl bg-amber-500/20 border border-amber-500/30 text-amber-400 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid fa-magnifying-glass-location"></i>
                            </div>
                            <h4 class="text-base sm:text-lg font-extrabold text-white leading-snug">Akıllı Geocoding</h4>
                        </div>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            Gelişmiş adres ve sınır çözümleme algoritması ile aradığınız mahalleyi, sokağı, eczaneyi veya tarihi yapıyı saniyeler içinde bulur.
                        </p>
                    </div>

                    <div class="tech-details border-t border-slate-800 text-slate-300 space-y-4 text-sm leading-relaxed">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-slate-800/70 p-3.5 rounded-xl border border-slate-700">
                            <div><span class="text-[10px] font-bold uppercase text-amber-400 block">Sorgu Optimizasyonu</span><b class="text-xs text-white">600ms Debounce & Set Deduplication</b></div>
                            <div><span class="text-[10px] font-bold uppercase text-amber-400 block">Arama Motoru</span><b class="text-xs text-white">Nominatim OSM + Poligon GeoJSON</b></div>
                            <div><span class="text-[10px] font-bold uppercase text-amber-400 block">Yarış Durumu Koruması</span><b class="text-xs text-white">Request ID & Boundary Cache</b></div>
                        </div>
                        <p>
                            <strong class="text-white">1. Debouncing ve İdari Sınır Çözümleme:</strong> Arama kutusuna yazılan her harfte sunucuya istek gönderilmesini önlemek için <span class="code-inline"><span class="fn">setTimeout</span>(<span class="prm">fn</span>, <span class="num">600</span>)</span> tabanlı Debounce zamanlayıcısı kullanılır. Muhtarlık ve ilçe istatistiklerinde ise <span class="code-inline"><span class="prm">polygon_geojson</span>=<span class="num">1</span></span> parametresiyle mahalle ve ilçe sınır poligonları çekilerek <span class="code-inline"><span class="prp">sinirCache</span></span> belleğine alınır.
                        </p>
                        <div class="ide-block">
                            <span class="cm">// Türkçe Karakter Duyarsız Fonetik Normalizasyon ve OSM Poligon Sorgusu</span><br>
                            <span class="kw">const</span> <span class="prp">endpoint</span> <span class="op">=</span> <span class="str">`https://nominatim.openstreetmap.org/search?format=json&amp;q=</span><span class="fn">${encodeURIComponent(sorgu)}</span><span class="str">&amp;polygon_geojson=1`</span>;<br>
                            <span class="kw">const</span> <span class="prp">normalize</span> <span class="op">=</span> (<span class="prp">s</span>) <span class="op">=&gt;</span> <span class="prp">s</span>.<span class="fn">toLocaleLowerCase</span>(<span class="str">'tr-TR'</span>).<span class="fn">replace</span>(<span class="num">/[ğüşıöç]/g</span>, <span class="prp">charMap</span>);
                        </div>
                    </div>

                    <div class="mt-4 pt-3 flex justify-end">
                        <button onclick="toggleBentoDetails('bento-4')" title="Detayları Aç / Kapat" class="w-9 h-9 rounded-full bg-amber-500/20 hover:bg-amber-500 text-amber-300 hover:text-white border border-amber-500/30 transition-all flex items-center justify-center cursor-pointer focus:outline-none">
                            <i class="fa-solid fa-arrow-right btn-icon transition-transform duration-300 text-sm"></i>
                        </button>
                    </div>
                </div>

                <div id="bento-5" data-col="md:col-span-3" class="bento-card md:col-span-3 group bg-slate-900/75 hover:bg-slate-900/90 backdrop-blur-xl text-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-2xl border border-slate-700/70 hover:border-rose-500/50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3.5 mb-3">
                            <div class="w-11 h-11 rounded-xl bg-rose-500/20 border border-rose-500/30 text-rose-400 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid fa-filter"></i>
                            </div>
                            <h4 class="text-base sm:text-lg font-extrabold text-white leading-snug">Dinamik Filtreleme</h4>
                        </div>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            Sayfa yenilemeye gerek kalmadan 16 kategori ve 711 mahalle arasında anlık geçiş yapın; ilçe ve metin filtreleriyle sonuçları daraltın.
                        </p>
                    </div>

                    <div class="tech-details border-t border-slate-800 text-slate-300 space-y-4 text-sm leading-relaxed">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-slate-800/70 p-3.5 rounded-xl border border-slate-700">
                            <div><span class="text-[10px] font-bold uppercase text-rose-400 block">Bellek Yönetimi</span><b class="text-xs text-white">Zero Garbage-Collection Overhead</b></div>
                            <div><span class="text-[10px] font-bold uppercase text-rose-400 block">Geçiş Animasyonu</span><b class="text-xs text-white">Cubic-Bezier (0.34, 1.56, 0.64, 1)</b></div>
                            <div><span class="text-[10px] font-bold uppercase text-rose-400 block">Hayalet Tıklama Koruması</span><b class="text-xs text-white">Dinamik Pointer-Events İzolasyonu</b></div>
                        </div>
                        <p>
                            <strong class="text-white">1. Sıfır Bellek Yükü ile Katman Değişimi:</strong> Kategori değiştirildiğinde işaretçi nesneleri RAM'den silinip baştan yaratılmaz. Her işaretçi bellekte <span class="code-inline"><span class="prp">marker.category</span></span> özelliğiyle tutulur; filtreleme anında yalnızca hedef katmanın CSS matrisi <span class="code-inline"><span class="fn">scale</span>(<span class="num">1</span>)</span> durumuna getirilirken diğerleri <span class="code-inline"><span class="prp">pointer-events</span>: <span class="str">none</span></span> ile izole edilir.
                        </p>
                        <div class="ide-block">
                            <span class="cm">// Bellekten Silmeden GPU Seviyesinde Katman Filtreleme</span><br>
                            <span class="prp">heroMarkers</span>.<span class="fn">eachLayer</span>((<span class="prp">marker</span>) <span class="op">=&gt;</span> {<br>
                            &nbsp;&nbsp;<span class="kw">const</span> <span class="prp">show</span> <span class="op">=</span> (<span class="prp">category</span> <span class="op">===</span> <span class="str">'all'</span>) <span class="op">||</span> (<span class="prp">marker.category</span> <span class="op">===</span> <span class="prp">category</span>);<br>
                            &nbsp;&nbsp;<span class="prp">innerEl.classList</span>.<span class="fn">toggle</span>(<span class="str">'scale-100'</span>, <span class="prp">show</span>);<br>
                            &nbsp;&nbsp;<span class="prp">outerEl.style.pointerEvents</span> <span class="op">=</span> <span class="prp">show</span> <span class="op">?</span> <span class="str">'auto'</span> <span class="op">:</span> <span class="str">'none'</span>;<br>
                            });
                        </div>
                    </div>

                    <div class="mt-4 pt-3 flex justify-end">
                        <button onclick="toggleBentoDetails('bento-5')" title="Detayları Aç / Kapat" class="w-9 h-9 rounded-full bg-rose-500/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/30 transition-all flex items-center justify-center cursor-pointer focus:outline-none">
                            <i class="fa-solid fa-arrow-right btn-icon transition-transform duration-300 text-sm"></i>
                        </button>
                    </div>
                </div>

                <div id="bento-6" data-col="md:col-span-3" class="bento-card md:col-span-3 group bg-slate-900/75 hover:bg-slate-900/90 backdrop-blur-xl text-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-2xl border border-slate-700/70 hover:border-indigo-500/50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3.5 mb-3">
                            <div class="w-11 h-11 rounded-xl bg-indigo-500/20 border border-indigo-500/30 text-indigo-400 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid fa-route"></i>
                            </div>
                            <h4 class="text-base sm:text-lg font-extrabold text-white leading-snug">Tek Tıkla Navigasyon</h4>
                        </div>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            Nöbetçi eczane, taziye evi, cami, otopark veya toplanma alanlarına Google Haritalar entegrasyonuyla anında yol tarifi alın.
                        </p>
                    </div>

                    <div class="tech-details border-t border-slate-800 text-slate-300 space-y-4 text-sm leading-relaxed">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-slate-800/70 p-3.5 rounded-xl border border-slate-700">
                            <div><span class="text-[10px] font-bold uppercase text-indigo-400 block">Koordinat Sistemi</span><b class="text-xs text-white">WGS84 / EPSG:4326 Datum</b></div>
                            <div><span class="text-[10px] font-bold uppercase text-indigo-400 block">Bağlantı Protokolü</span><b class="text-xs text-white">Universal Deep-Link URI Scheme</b></div>
                            <div><span class="text-[10px] font-bold uppercase text-indigo-400 block">Mobil Dokunmatik</span><b class="text-xs text-white">Touch-Action Pan-Y & Double-Tap</b></div>
                        </div>
                        <p>
                            <strong class="text-white">1. Evrensel Derin Bağlantı (Universal Deep Linking):</strong> Haritadaki herhangi bir noktaya tıklandığında, ilgili noktanın hassas enlem/boylam veya açık adres değerleri <span class="code-inline"><span class="str">https://www.google.com/maps/dir/?</span><span class="prm">api</span>=<span class="num">1</span>&amp;<span class="prm">destination</span>=<span class="fn">${hedef}</span></span> şemasına dinamik olarak enjekte edilir. Bu yapı masaüstünde doğrudan rota planlayıcıyı açarken, iOS ve Android cihazlarda doğrudan yerleşik (native) harita uygulamasını başlatır.
                        </p>
                        <p>
                            <strong class="text-white">2. Mobil Ergonomi ve Çift Dokunma (Double-Tap) Koruması:</strong> Mobil kullanıcıların sayfayı aşağı kaydırırken yanlışlıkla haritanın içine hapsolmasını engellemek için harita katmanına <span class="code-inline"><span class="prp">touch-action</span>: <span class="str">pan-y</span></span> uygulanmıştır. 350 milisaniye içinde gerçekleşen çift dokunuşlar ise <span class="code-inline">(<span class="prm">dx</span> &lt; <span class="num">40</span> &amp;&amp; <span class="prm">dy</span> &lt; <span class="num">40</span>)</span> kontrolüyle algılanarak 3D harita modunu başlatır:
                        </p>
                        <div class="ide-block">
                            <span class="cm">// Evrensel Navigasyon URI Şeması ve Mobil Çift Dokunma (Double-Tap) Algılayıcı</span><br>
                            <span class="kw">const</span> <span class="prp">dirUrl</span> <span class="op">=</span> <span class="str">`https://www.google.com/maps/dir/?api=1&amp;destination=</span><span class="fn">${lat}</span><span class="str">,</span><span class="fn">${lng}</span><span class="str">`</span>;<br>
                            <span class="kw">if</span> (<span class="prp">tapLength</span> <span class="op">&gt;</span> <span class="num">0</span> <span class="op">&amp;&amp;</span> <span class="prp">tapLength</span> <span class="op">&lt;</span> <span class="num">350</span> <span class="op">&amp;&amp;</span> <span class="prp">dx</span> <span class="op">&lt;</span> <span class="num">40</span> <span class="op">&amp;&amp;</span> <span class="prp">dy</span> <span class="op">&lt;</span> <span class="num">40</span>) <span class="fn">trigger3DEffect</span>();
                        </div>
                    </div>

                    <div class="mt-4 pt-3 flex justify-end">
                        <button onclick="toggleBentoDetails('bento-6')" title="Detayları Aç / Kapat" class="w-9 h-9 rounded-full bg-indigo-500/20 hover:bg-indigo-600 text-indigo-300 hover:text-white border border-indigo-500/30 transition-all flex items-center justify-center cursor-pointer focus:outline-none">
                            <i class="fa-solid fa-arrow-right btn-icon transition-transform duration-300 text-sm"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="py-20 bg-[#0b1120] text-white relative z-20 overflow-hidden border-t border-slate-800/80" id="services-section">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/15 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-600/15 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
            
            <div class="mb-10 bg-slate-900/90 backdrop-blur-md border border-slate-800 rounded-2xl p-3 sm:px-5 flex flex-wrap items-center justify-between gap-4 shadow-lg">
                <div class="flex items-center gap-2.5">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-400">Canlı Telemetri Akışı</span>
                </div>

                <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-xs text-slate-300">
                    <a href="portal.php?hizmet=deprem" class="flex items-center gap-2 hover:text-white transition">
                        <i class="fa-solid fa-wave-square text-purple-400"></i>
                        <span class="text-slate-400">Son Deprem:</span>
                        <span id="live-ticker-quake" class="font-bold text-white bg-purple-500/20 border border-purple-500/30 px-2 py-0.5 rounded-md">Veri çekiliyor...</span>
                    </a>

                    <span class="hidden sm:inline text-slate-700">|</span>

                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-cloud-sun text-amber-400"></i>
                        <span class="text-slate-400">Kayseri Meteoroloji:</span>
                        <span id="live-ticker-weather" class="font-bold text-white bg-amber-500/20 border border-amber-500/30 px-2 py-0.5 rounded-md">Yükleniyor...</span>
                    </div>

                    <span class="hidden lg:inline text-slate-700">|</span>

                    <div class="hidden lg:flex items-center gap-2">
                        <i class="fa-solid fa-database text-blue-400"></i>
                        <span class="text-slate-400">Harita Belleği:</span>
                        <span id="live-ticker-markers" class="font-bold text-blue-300">Senkronize Ediliyor...</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                
                <div class="lg:col-span-5 flex flex-col justify-between bg-slate-900/50 border border-slate-800/90 rounded-2xl p-6 shadow-xl">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold uppercase tracking-widest mb-3">
                            <i class="fa-solid fa-compass"></i> Şehir Rehberi & Katman Yönlendirici
                        </div>
                        <h2 class="text-3xl md:text-5xl font-black tracking-tight text-white leading-tight">TÜM HİZMETLER</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="px-3 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold"><i class="fa-solid fa-circle-check mr-1.5"></i>11 Aktif Veri Katmanı</span>
                            <span class="px-3 py-1 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-bold"><i class="fa-solid fa-clock-rotate-left mr-1.5"></i>5 Planlanan / KVKK Kısıtlı Servis</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-5 border-t border-slate-800/80 space-y-4">
                        <div class="grid grid-cols-3 gap-2.5 text-center">
                            <div class="bg-slate-950/70 border border-slate-800 rounded-xl p-2.5">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">Projeksiyon</span>
                                <span class="text-xs font-extrabold text-blue-400 font-mono">EPSG:4326</span>
                            </div>
                            <div class="bg-slate-950/70 border border-slate-800 rounded-xl p-2.5">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">İdari Kapsam</span>
                                <span class="text-xs font-extrabold text-emerald-400 font-mono">16 İlçe / 711 Mah.</span>
                            </div>
                            <div class="bg-slate-950/70 border border-slate-800 rounded-xl p-2.5">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">Tepki Süresi</span>
                                <span class="text-xs font-extrabold text-purple-400 font-mono">&lt;16ms (60FPS)</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 bg-gradient-to-r from-blue-600/20 to-purple-600/20 border border-blue-500/30 rounded-xl p-3.5">
                            <div class="min-w-0">
                                <span class="text-xs font-extrabold text-white block truncate">Tam Ekran CBS / GIS Modu</span>
                                <span class="text-[11px] text-slate-300 block truncate">Gelişmiş arama, ilçe sınırları ve yan listelerle inceleyin</span>
                            </div>
                            <a href="portal.php" class="shrink-0 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition shadow-md flex items-center gap-1.5">
                                Başlat <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 bg-slate-900/80 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl flex flex-col justify-between">
                    <div>
                        <p class="text-slate-300 text-sm leading-relaxed mb-3">
                            Aşağıdaki çift yönlü sonsuz akış (marquee) vitrininde yer alan her bir hizmet kartı, tam ekran harita motorumuzun (<span class="code-inline"><span class="str">portal.php</span></span>) ilgili veri katmanına doğrudan derin bağlantı (<span class="code-inline">?<span class="prm">hizmet</span>=<span class="fn">{endpoint}</span></span>) sağlar. Bir kategoriye tıkladığınızda sistem, URL üzerindeki sorgu parametresini <span class="code-inline"><span class="kw">new</span> <span class="fn">URLSearchParams</span>(<span class="prm">window.location.search</span>)</span> ile yakalar, ilgili yan liste panelini (drawer) otomatik açar ve harita kamerasını o verinin coğrafi ölçeğine uçurur.
                        </p>
                        <p class="text-slate-400 text-xs leading-relaxed mb-4">
                            Nöbetçi eczaneler, depremler, toplanma alanları, vefat ilanları, 711 mahalle muhtarlık envanteri, 16 ilçe istatistikleri ve hal fiyatlarından oluşan <strong>11 aktif katman</strong> anlık olarak sunulurken; KVKK veya kurum içi kapalı ağ politikaları nedeniyle dış erişime kapalı olan <strong>5 deaktif hizmet</strong> seçildiğinde <span class="code-inline"><span class="fn">gosterKullanilamayanHizmetler</span>()</span> tetiklenerek kullanıcıya şeffaf durum bildirimi (Toast UI) gösterilir.
                        </p>
                    </div>

                    <div class="ide-block">
                        <span class="cm">// URL Parametresi Yakalama, Otonom Katman Yükleme ve Kamera Ölçeklendirme</span><br>
                        <span class="kw">const</span> <span class="prp">urlParams</span> <span class="op">=</span> <span class="kw">new</span> <span class="fn">URLSearchParams</span>(<span class="prp">window.location.search</span>);<br>
                        <span class="kw">const</span> <span class="prp">hedefHizmet</span> <span class="op">=</span> <span class="prp">urlParams</span>.<span class="fn">get</span>(<span class="str">'hizmet'</span>) <span class="op">||</span> <span class="str">'tumu'</span>;<br>
                        <span class="kw">const</span> <span class="prp">pasifServisler</span> <span class="op">=</span> [<span class="str">'kaybis'</span>, <span class="str">'kaski_su_kesinti'</span>, <span class="str">'kameralar'</span>, <span class="str">'sarj_istasyonlari'</span>, <span class="str">'spor_tesisleri'</span>];<br>
                        <span class="kw">if</span> (<span class="prp">pasifServisler</span>.<span class="fn">includes</span>(<span class="prp">hedefHizmet</span>)) <span class="fn">gosterKullanilamayanHizmetler</span>();<br>
                        <span class="fn">veriCekVeGoster</span>(<span class="prp">hedefHizmet</span>);
                    </div>
                </div>

            </div>
        </div>
        
        <div class="w-full overflow-hidden mb-5 relative marquee-mask">
            <div class="marquee-track-left">
                <?php for($i=0; $i<4; $i++): ?>
                <a href="portal.php?hizmet=deprem" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-purple-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white p-1.5 flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <img src="assets/icons/kandilli.jpg" onerror="this.src='assets/icons/kandilli.png'" class="w-full h-full object-contain">
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-purple-400 uppercase tracking-wider block">Canlı Veri</span>
                            <h3 class="font-bold text-white text-sm truncate">Son Depremler</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=toplanma_alanlari" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-emerald-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white p-1.5 flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <img src="assets/icons/afad2.png" onerror="this.src='assets/icons/afad.png'" class="w-full h-full object-contain">
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider block">Güvenli Bölge</span>
                            <h3 class="font-bold text-white text-sm truncate">Afet Toplanma Alanları</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=gezilecek_yerler" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-rose-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white p-2 flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <img src="assets/icons/kültür2.png" onerror="this.src='assets/icons/kültür.png'" class="w-full h-full object-contain">
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-rose-400 uppercase tracking-wider block">Kültür & Turizm</span>
                            <h3 class="font-bold text-white text-sm truncate">Tarihi Yerler</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=otoparklar" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-slate-400/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-slate-700 text-white flex items-center justify-center shrink-0 shadow-md text-lg group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-square-parking"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Ulaşım</span>
                            <h3 class="font-bold text-white text-sm truncate">Şehir Otoparkları</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=pazar_yerleri" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-sky-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-sky-500 text-white flex items-center justify-center shrink-0 shadow-md text-lg group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-basket-shopping"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-sky-400 uppercase tracking-wider block">Sosyal Yaşam</span>
                            <h3 class="font-bold text-white text-sm truncate">Pazarlar</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=nobetci_eczane" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-red-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white p-2 flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <img src="assets/icons/eczane.png" class="w-full h-full object-contain">
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-red-400 uppercase tracking-wider block">Sağlık</span>
                            <h3 class="font-bold text-white text-sm truncate">Nöbetçi Eczaneler</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=vefat" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-zinc-400/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-zinc-800 border border-zinc-600 text-white flex items-center justify-center shrink-0 shadow-md text-lg group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-ribbon"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-zinc-300 uppercase tracking-wider block">Mezarlık & Taziye</span>
                            <h3 class="font-bold text-white text-sm truncate">Vefat İlanları</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=muhtar" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-teal-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-teal-600 text-white flex items-center justify-center shrink-0 shadow-md text-lg group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-teal-400 uppercase tracking-wider block">711 Mahalle</span>
                            <h3 class="font-bold text-white text-sm truncate">Mahalle/Muhtarlık Bilgileri</h3>
                        </div>
                    </div>
                </a>
                <?php endfor; ?>
            </div>
        </div>

        <div class="w-full overflow-hidden relative marquee-mask">
            <div class="marquee-track-right">
                <?php for($i=0; $i<4; $i++): ?>
                <a href="portal.php?hizmet=cadde_sokak" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-indigo-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md text-lg group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-road"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider block">16 İlçe CBS & Nüfus</span>
                            <h3 class="font-bold text-white text-sm truncate">İlçe İstatistikleri</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=hal" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-orange-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white p-1.5 flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <img src="assets/icons/hal.png" onerror="this.onerror=null; this.src='assets/icons/kbb.png';" class="w-full h-full object-contain rounded-full">
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-orange-400 uppercase tracking-wider block">Ekonomi & Tarım</span>
                            <h3 class="font-bold text-white text-sm truncate">Hal Fiyat Listesi</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=wifi_noktalari" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-green-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-green-500 text-white flex items-center justify-center shrink-0 shadow-md text-lg group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-wifi"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-green-400 uppercase tracking-wider block">İnternet</span>
                            <h3 class="font-bold text-white text-sm truncate">Ücretsiz Wi-Fi Noktaları</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=kaybis" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-blue-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white p-1.5 flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <img src="assets/icons/kaybis.png" class="w-full h-full object-contain">
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-blue-400 uppercase tracking-wider block">Bisiklet Paylaşım</span>
                            <h3 class="font-bold text-white text-sm truncate">KAYBİS İstasyonları</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=kaski_su_kesinti" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-cyan-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white p-1.5 flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <img src="assets/icons/kaski.png" class="w-full h-full object-contain">
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-cyan-400 uppercase tracking-wider block">Altyapı</span>
                            <h3 class="font-bold text-white text-sm truncate">Su Kesintileri</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=kameralar" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-slate-400/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-slate-700 text-white flex items-center justify-center shrink-0 shadow-md text-lg group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-video"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Canlı İzleme</span>
                            <h3 class="font-bold text-white text-sm truncate">Şehir Kameraları</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=sarj_istasyonlari" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-amber-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md text-lg group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-charging-station"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider block">E-Mobilite</span>
                            <h3 class="font-bold text-white text-sm truncate">Elektrikli Şarj Noktaları</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=spor_tesisleri" class="marquee-item group block">
                    <div class="glow-card bg-slate-800/60 hover:bg-slate-800/90 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-indigo-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-indigo-500 text-white flex items-center justify-center shrink-0 shadow-md text-lg group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-volleyball"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider block">Yaşam & Spor</span>
                            <h3 class="font-bold text-white text-sm truncate">Spor Tesisleri ve Parklar</h3>
                        </div>
                    </div>
                </a>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <section class="py-20 bg-white border-t border-slate-200 relative z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="text-xs font-bold uppercase tracking-widest text-blue-600 block mb-2">Resmi Veri Sağlayıcılar</span>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">VERİ KAYNAKLARI</h2>
                <p class="mt-2 text-sm text-slate-500">Hangi kurumdan hangi veri setlerinin ve uç noktaların (endpoints) kullanıldığını görmek için kartlara tıklayın.</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-5">
                <button type="button" onclick="toggleSourceInspector('kbb')" id="src-card-kbb" class="source-card relative group flex flex-col items-center justify-center p-6 bg-slate-50 rounded-2xl border-2 border-slate-200/80 hover:border-blue-500 hover:bg-white hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-pointer focus:outline-none">
                    <div class="absolute top-3.5 right-3.5 flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                        <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>
                        <span class="text-[9px] font-bold text-emerald-700 uppercase">8 Veri Seti</span>
                    </div>
                    <div class="h-14 flex items-center justify-center mb-3 mt-2">
                        <img src="assets/icons/veri-platformu.png" alt="Kayseri BB" class="max-h-full object-contain group-hover:scale-105 transition-transform">
                    </div>
                    <span class="font-bold text-xs text-slate-700 group-hover:text-blue-600 text-center">Kayseri BB Açık Veri Portalı</span>
                    <span class="mt-2 text-[10px] font-semibold text-blue-600 flex items-center gap-1 opacity-80 group-hover:opacity-100">Veri Setlerini Gör <i class="fa-solid fa-chevron-down text-[9px]"></i></span>
                </button>
                
                <button type="button" onclick="toggleSourceInspector('kandilli')" id="src-card-kandilli" class="source-card relative group flex flex-col items-center justify-center p-6 bg-slate-50 rounded-2xl border-2 border-slate-200/80 hover:border-blue-500 hover:bg-white hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-pointer focus:outline-none">
                    <div class="absolute top-3.5 right-3.5 flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                        <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>
                        <span class="text-[9px] font-bold text-emerald-700 uppercase">Canlı API</span>
                    </div>
                    <div class="h-14 flex items-center justify-center mb-3 mt-2">
                        <img src="assets/icons/kandilli.jpg" onerror="this.src='assets/icons/kandilli.png'" alt="Kandilli" class="max-h-full object-contain rounded-lg group-hover:scale-105 transition-transform">
                    </div>
                    <span class="font-bold text-xs text-slate-700 group-hover:text-blue-600 text-center">Kandilli Rasathanesi API</span>
                    <span class="mt-2 text-[10px] font-semibold text-blue-600 flex items-center gap-1 opacity-80 group-hover:opacity-100">Veri Setlerini Gör <i class="fa-solid fa-chevron-down text-[9px]"></i></span>
                </button>
                
                <button type="button" onclick="toggleSourceInspector('afad')" id="src-card-afad" class="source-card relative group flex flex-col items-center justify-center p-6 bg-slate-50 rounded-2xl border-2 border-slate-200/80 hover:border-blue-500 hover:bg-white hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-pointer focus:outline-none">
                    <div class="absolute top-3.5 right-3.5 flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                        <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>
                        <span class="text-[9px] font-bold text-emerald-700 uppercase">GeoJSON</span>
                    </div>
                    <div class="h-14 flex items-center justify-center mb-3 mt-2">
                        <img src="assets/icons/afad.png" alt="AFAD" class="max-h-full object-contain group-hover:scale-105 transition-transform">
                    </div>
                    <span class="font-bold text-xs text-slate-700 group-hover:text-blue-600 text-center">AFAD Toplanma Alanları</span>
                    <span class="mt-2 text-[10px] font-semibold text-blue-600 flex items-center gap-1 opacity-80 group-hover:opacity-100">Veri Setlerini Gör <i class="fa-solid fa-chevron-down text-[9px]"></i></span>
                </button>

                <button type="button" onclick="toggleSourceInspector('kultur')" id="src-card-kultur" class="source-card relative group flex flex-col items-center justify-center p-6 bg-slate-50 rounded-2xl border-2 border-slate-200/80 hover:border-blue-500 hover:bg-white hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-pointer focus:outline-none">
                    <div class="absolute top-3.5 right-3.5 flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                        <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>
                        <span class="text-[9px] font-bold text-emerald-700 uppercase">JSON Envanter</span>
                    </div>
                    <div class="h-14 flex items-center justify-center mb-3 mt-2">
                        <img src="assets/icons/kültür.png" alt="Kültür Bakanlığı" class="max-h-full object-contain group-hover:scale-105 transition-transform">
                    </div>
                    <span class="font-bold text-xs text-slate-700 group-hover:text-blue-600 text-center">İl Kültür Turizm Müd.</span>
                    <span class="mt-2 text-[10px] font-semibold text-blue-600 flex items-center gap-1 opacity-80 group-hover:opacity-100">Veri Setlerini Gör <i class="fa-solid fa-chevron-down text-[9px]"></i></span>
                </button>
                
                <button type="button" onclick="toggleSourceInspector('meteo')" id="src-card-meteo" class="source-card relative group col-span-2 md:col-span-1 flex flex-col items-center justify-center p-6 bg-slate-50 rounded-2xl border-2 border-slate-200/80 hover:border-blue-500 hover:bg-white hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-pointer focus:outline-none">
                    <div class="absolute top-3.5 right-3.5 flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                        <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>
                        <span class="text-[9px] font-bold text-emerald-700 uppercase">REST API</span>
                    </div>
                    <div class="h-14 flex items-center justify-center mb-3 mt-2">
                        <img src="assets/icons/open.png" alt="Open Meteo" class="max-h-full object-contain open-meteo-logo group-hover:scale-150 transition-transform">
                    </div>
                    <span class="font-bold text-xs text-slate-700 group-hover:text-blue-600 text-center">Open-Meteo Weather API</span>
                    <span class="mt-2 text-[10px] font-semibold text-blue-600 flex items-center gap-1 opacity-80 group-hover:opacity-100">Veri Setlerini Gör <i class="fa-solid fa-chevron-down text-[9px]"></i></span>
                </button>
            </div>

            <div id="source-drawer" class="source-drawer-wrapper">
                <div class="source-drawer-inner">
                    <div class="bg-slate-900 text-white rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl relative overflow-hidden">
                        <div class="absolute -right-20 -top-20 w-72 h-72 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
                        
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800 relative z-10">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-white p-2 flex items-center justify-center shrink-0 shadow-md">
                                    <img id="inspector-logo" src="assets/icons/veri-platformu2.png" class="w-full h-full object-contain">
                                </div>
                                <div>
                                    <span id="inspector-badge" class="text-[10px] font-extrabold uppercase tracking-widest text-blue-400 block">Kurumsal Veri Sağlayıcı</span>
                                    <h3 id="inspector-title" class="text-xl sm:text-2xl font-black text-white">Kayseri Büyükşehir Belediyesi Açık Veri Portalı</h3>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 self-end sm:self-center">
                                <a id="inspector-link" href="https://acikveri.kayseri.bel.tr/" target="_blank" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition flex items-center gap-2 shadow-md">
                                    Resmi Siteye Git <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                </a>
                                <button onclick="closeSourceInspector()" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition flex items-center justify-center cursor-pointer" title="Paneli Kapat">
                                    <i class="fa-solid fa-xmark text-base"></i>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pt-6 relative z-10 items-stretch">
                            <div class="lg:col-span-7 flex flex-col">
                                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-3"><i class="fa-solid fa-database text-blue-400 mr-1.5"></i>Bu Kaynaktan Çekilen Aktif Veri Setleri</h4>
                                <div id="inspector-datasets" class="space-y-3 flex-1 max-h-[460px] overflow-y-auto pr-1 scrollbar-hide"></div>
                            </div>

                            <div class="lg:col-span-5 flex flex-col justify-between space-y-4">
                                <div>
                                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-3"><i class="fa-solid fa-code text-purple-400 mr-1.5"></i>Veri Şeması & Entegrasyon Kodu</h4>
                                    <div id="inspector-code" class="ide-block"></div>
                                </div>

                                <div>
                                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-2.5"><i class="fa-solid fa-server text-emerald-400 mr-1.5"></i>Bağlantı & Telemetri Parametreleri</h4>
                                    <div id="inspector-metrics" class="grid grid-cols-2 gap-2.5"></div>
                                </div>

                                <div id="inspector-note" class="p-3.5 rounded-xl bg-slate-800/70 border border-slate-700/80 text-xs text-slate-300 leading-relaxed"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>

    <footer class="bg-slate-950 text-slate-400 border-t border-slate-800 relative z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-slate-800/80">
                
                <div class="md:col-span-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-white rounded-lg p-1 flex items-center justify-center">
                            <img src="assets/icons/kbb.png" alt="Kayseri BB" class="w-full h-full object-contain">
                        </div>
                        <span class="font-bold text-xl text-white tracking-tight">Kayseri Şehir Portalı</span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed mb-6 max-w-sm">
                        Kayseri'nin açık veri ekosistemini tek bir interaktif harita üzerinde buluşturan yeni nesil dijital ikiz platformu.
                    </p>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-xs font-medium text-slate-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tüm API Servisleri Aktif
                    </div>
                </div>

                <div class="md:col-span-3">
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-4">Popüler Katmanlar</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="portal.php?hizmet=nobetci_eczane" class="hover:text-white transition">Nöbetçi Eczaneler</a></li>
                        <li><a href="portal.php?hizmet=deprem" class="hover:text-white transition">Son Depremler</a></li>
                        <li><a href="portal.php?hizmet=toplanma_alanlari" class="hover:text-white transition">Afet Toplanma Alanları</a></li>
                        <li><a href="portal.php?hizmet=vefat" class="hover:text-white transition">Vefat İlanları</a></li>
                        <li><a href="portal.php?hizmet=muhtar" class="hover:text-white transition">Mahalle & Muhtarlık Bilgileri</a></li>
                        <li><a href="portal.php?hizmet=cadde_sokak" class="hover:text-white transition">İlçe İstatistikleri</a></li>
                    </ul>
                </div>

                <div class="md:col-span-4">
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-4">Tam Ekran Deneyimi</h4>
                    <p class="text-sm text-slate-400 mb-4 leading-relaxed">
                        Gelişmiş arama, filtreleme, ilçe/mahalle sınır çizimleri ve liste görünümleri için tam ekran Şehir Portalı arayüzüne geçiş yapın.
                    </p>
                    <a href="portal.php" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition">
                        Portal'ı Başlat <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    </a>
                </div>

            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>© 2026 Kayseri Şehir Portalı. Tüm hakları saklıdır.</p>
                <p>Bu proje açık veri felsefesini desteklemek amacıyla geliştirilmiştir. Verilerin güncelliği ilgili kurumların API'lerine bağlıdır.</p>
            </div>
        </div>
    </footer>

    <div id="toast-panel" class="fixed bottom-5 right-5 transform translate-y-20 opacity-0 transition-all duration-500 z-50 pointer-events-none"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        let initialZoom = 12;
        let currentMinZoom = 10; 
        let isFlying = false;    
        let currentCategory = 'all'; 
        let menuTimeout = null;
        let effectTriggered = false;
        let markersInteractive = false; 
        let totalLoadedMarkers = 0;
        let activeSourceKey = null;

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
            'YEŞİLHİSAR': { alan_m2: '877,764,742',   alan_km2: '877.76',  mahalle_sayisi: 35,  nufus_2024: '15,674',  nufus_2025: '15,409',  degisim: '-1.71' }
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
            'YEŞİLHİSAR': { cadde: '26',    sokak: '258',    bulvar: '0',  meydan: '1',  kume: '30',  toplam: '315' }
        };

        (function initDarkCyberGIS3D() {
            const canvas = document.getElementById('city-twin-canvas');
            const section = document.getElementById('nasil-calisir');
            if (!canvas || !section || typeof THREE === 'undefined') return;

            let width = section.offsetWidth;
            let height = section.offsetHeight;
            let isVisible = false;
            let animId = null;

            const scene = new THREE.Scene();
            scene.background = new THREE.Color(0x050811);
            scene.fog = new THREE.FogExp2(0x050811, 0.00135);

            const camera = new THREE.PerspectiveCamera(45, width / height, 1, 2400);

            const renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, powerPreference: "high-performance" });
            renderer.setSize(width, height);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

            const worldGroup = new THREE.Group();
            scene.add(worldGroup);

            const terrainSize = 1350;
            const segments = 68;
            const terrainGeo = new THREE.PlaneGeometry(terrainSize, terrainSize, segments, segments);
            terrainGeo.rotateX(-Math.PI / 2);

            const posAttr = terrainGeo.attributes.position;
            const vertexCount = posAttr.count;
            const baseHeights = new Float32Array(vertexCount);
            const colorArray = new Float32Array(vertexCount * 3);

            for (let i = 0; i < vertexCount; i++) {
                const vx = posAttr.getX(i);
                const vz = posAttr.getZ(i);
                const dist = Math.hypot(vx, vz);

                let h = 0;
                if (dist > 180) {
                    const ridge = Math.sin(vx * 0.012) * Math.cos(vz * 0.012) * 32;
                    const mountain = Math.pow((dist - 180) / 550, 1.6) * 85;
                    h = Math.max(-8, ridge + mountain * (0.6 + 0.4 * Math.sin(vx * 0.008 - vz * 0.006)));
                } else {
                    h = Math.sin(vx * 0.03) * Math.cos(vz * 0.03) * 2.5;
                }

                baseHeights[i] = h;
                posAttr.setY(i, h);

                const normH = Math.min(1, Math.max(0, (h + 10) / 95));
                colorArray[i * 3]     = 0.08 + normH * 0.15;
                colorArray[i * 3 + 1] = 0.35 + normH * 0.55;
                colorArray[i * 3 + 2] = 0.75 + normH * 0.25;
            }

            terrainGeo.setAttribute('color', new THREE.BufferAttribute(colorArray, 3));

            const terrainWireMat = new THREE.MeshBasicMaterial({
                vertexColors: true,
                wireframe: true,
                transparent: true,
                opacity: 0.22,
                blending: THREE.AdditiveBlending
            });
            const terrainWire = new THREE.Mesh(terrainGeo, terrainWireMat);
            worldGroup.add(terrainWire);

            const lidarPointsMat = new THREE.PointsMaterial({
                size: 3.2,
                vertexColors: true,
                transparent: true,
                opacity: 0.75,
                blending: THREE.AdditiveBlending
            });
            const lidarPoints = new THREE.Points(terrainGeo, lidarPointsMat);
            worldGroup.add(lidarPoints);

            const towers = [];
            const hubCoords = [];
            const unitBox = new THREE.BoxGeometry(1, 1, 1);
            unitBox.translate(0, 0.5, 0);
            const unitEdges = new THREE.EdgesGeometry(unitBox);

            const towerPalette = [0x0ea5e9, 0x3b82f6, 0x8b5cf6, 0x10b981, 0xf59e0b];

            for (let gx = -5; gx <= 5; gx++) {
                for (let gz = -5; gz <= 5; gz++) {
                    if (gx === 0 && gz === 0) continue;
                    const gridDist = Math.hypot(gx, gz);
                    if (gridDist > 5.2) continue;

                    const wx = gx * 42 + (Math.random() - 0.5) * 8;
                    const wz = gz * 42 + (Math.random() - 0.5) * 8;

                    const isCoreHub = (Math.abs(gx) === 2 && Math.abs(gz) === 2) || (gx === -3 && gz === 1) || (gx === 3 && gz === -1) || (gx === 0 && gz === -3);
                    const w = isCoreHub ? 20 : (11 + Math.random() * 11);
                    const d = isCoreHub ? 20 : (11 + Math.random() * 11);
                    const h = isCoreHub ? (68 + Math.random() * 34) : (10 + Math.pow(Math.max(0.1, 1 - gridDist / 5.5), 1.5) * 52);

                    const accentHex = isCoreHub ? towerPalette[(Math.abs(gx + gz)) % towerPalette.length] : 0x1e40af;

                    const tGroup = new THREE.Group();
                    tGroup.position.set(wx, 0, wz);

                    const bodyMat = new THREE.MeshBasicMaterial({
                        color: accentHex,
                        transparent: true,
                        opacity: isCoreHub ? 0.28 : 0.12,
                        blending: THREE.AdditiveBlending,
                        depthWrite: false
                    });
                    const bodyMesh = new THREE.Mesh(unitBox, bodyMat);
                    bodyMesh.scale.set(w, h, d);
                    tGroup.add(bodyMesh);

                    const edgeMat = new THREE.LineBasicMaterial({
                        color: isCoreHub ? 0x38bdf8 : 0x3b82f6,
                        transparent: true,
                        opacity: isCoreHub ? 0.85 : 0.42,
                        blending: THREE.AdditiveBlending
                    });
                    const wireMesh = new THREE.LineSegments(unitEdges, edgeMat);
                    wireMesh.scale.set(w, h, d);
                    tGroup.add(wireMesh);

                    if (isCoreHub) {
                        const beamGeo = new THREE.CylinderGeometry(0.6, 2.2, 160, 8, 1, true);
                        beamGeo.translate(0, 80, 0);
                        const beamMat = new THREE.MeshBasicMaterial({
                            color: accentHex,
                            transparent: true,
                            opacity: 0.24,
                            side: THREE.DoubleSide,
                            blending: THREE.AdditiveBlending,
                            depthWrite: false
                        });
                        const beam = new THREE.Mesh(beamGeo, beamMat);
                        beam.position.y = h;
                        tGroup.add(beam);

                        hubCoords.push(new THREE.Vector3(wx, h + 6, wz));
                    }

                    worldGroup.add(tGroup);
                    towers.push({
                        group: tGroup,
                        bodyMat: bodyMat,
                        edgeMat: edgeMat,
                        wx: wx,
                        wz: wz,
                        baseH: h,
                        curScale: 1,
                        isCoreHub: isCoreHub,
                        baseOpacity: isCoreHub ? 0.28 : 0.12
                    });
                }
            }

            const hudGroup = new THREE.Group();
            hudGroup.position.y = 4;
            worldGroup.add(hudGroup);

            const ring1 = new THREE.Mesh(
                new THREE.RingGeometry(115, 117.5, 96),
                new THREE.MeshBasicMaterial({ color: 0x0ea5e9, side: THREE.DoubleSide, transparent: true, opacity: 0.35, blending: THREE.AdditiveBlending })
            );
            ring1.rotation.x = -Math.PI / 2;
            hudGroup.add(ring1);

            const ring2 = new THREE.Mesh(
                new THREE.RingGeometry(195, 197, 128),
                new THREE.MeshBasicMaterial({ color: 0x6366f1, side: THREE.DoubleSide, transparent: true, opacity: 0.25, blending: THREE.AdditiveBlending })
            );
            ring2.rotation.x = -Math.PI / 2 + 0.14;
            ring2.rotation.y = 0.08;
            hudGroup.add(ring2);

            const ring3 = new THREE.Mesh(
                new THREE.RingGeometry(275, 276.5, 128),
                new THREE.MeshBasicMaterial({ color: 0x10b981, side: THREE.DoubleSide, transparent: true, opacity: 0.18, blending: THREE.AdditiveBlending })
            );
            ring3.rotation.x = -Math.PI / 2 - 0.1;
            hudGroup.add(ring3);

            const arcPulses = [];
            if (hubCoords.length >= 2) {
                for (let i = 0; i < hubCoords.length; i++) {
                    const start = hubCoords[i];
                    const end = hubCoords[(i + 1) % hubCoords.length];
                    const mid = start.clone().lerp(end, 0.5);
                    mid.y += 75 + (i % 3) * 20;

                    const curve = new THREE.QuadraticBezierCurve3(start, mid, end);
                    const arcPts = curve.getPoints(50);
                    const arcGeo = new THREE.BufferGeometry().setFromPoints(arcPts);
                    const cHex = towerPalette[i % towerPalette.length];

                    const arcLine = new THREE.Line(
                        arcGeo,
                        new THREE.LineBasicMaterial({ color: cHex, transparent: true, opacity: 0.5, blending: THREE.AdditiveBlending })
                    );
                    worldGroup.add(arcLine);

                    const pSphere = new THREE.Mesh(
                        new THREE.SphereGeometry(3.2, 12, 12),
                        new THREE.MeshBasicMaterial({ color: 0xffffff, blending: THREE.AdditiveBlending })
                    );
                    worldGroup.add(pSphere);

                    arcPulses.push({
                        curve: curve,
                        mesh: pSphere,
                        t: Math.random(),
                        speed: 0.005 + Math.random() * 0.004
                    });
                }
            }

            const cursorScanner = new THREE.Mesh(
                new THREE.RingGeometry(28, 32, 48),
                new THREE.MeshBasicMaterial({ color: 0x00f2fe, side: THREE.DoubleSide, transparent: true, opacity: 0.0, blending: THREE.AdditiveBlending })
            );
            cursorScanner.rotation.x = -Math.PI / 2;
            cursorScanner.position.y = 2.5;
            worldGroup.add(cursorScanner);

            const empWaves = [];
            const empBaseGeo = new THREE.RingGeometry(1, 1.07, 64);
            empBaseGeo.rotateX(-Math.PI / 2);

            const groundPlane = new THREE.Plane(new THREE.Vector3(0, 1, 0), 0);
            const raycaster = new THREE.Raycaster();
            const mouseNDC = new THREE.Vector2(-99, -99);
            const intersectPoint = new THREE.Vector3(0, 0, 0);
            const smoothCursor3D = new THREE.Vector3(0, 0, 0);
            let isPointerInside = false;

            let camYaw = 0.65;
            let camPitch = 0.50;
            let targetYaw = 0.65;
            let targetPitch = 0.50;
            let isDragging = false;
            let lastX = 0, lastY = 0;

            section.addEventListener('mousedown', (e) => {
                if (e.target.closest('.bento-card, a, button')) return;
                isDragging = true;
                lastX = e.clientX;
                lastY = e.clientY;
            });

            window.addEventListener('mouseup', () => {
                isDragging = false;
            });

            section.addEventListener('mousemove', (e) => {
                const rect = section.getBoundingClientRect();
                const mx = e.clientX - rect.left;
                const my = e.clientY - rect.top;

                mouseNDC.x = (mx / width) * 2 - 1;
                mouseNDC.y = -(my / height) * 2 + 1;
                isPointerInside = true;

                if (isDragging) {
                    const dx = e.clientX - lastX;
                    const dy = e.clientY - lastY;
                    targetYaw += dx * 0.006;
                    targetPitch = Math.max(0.24, Math.min(1.12, targetPitch + dy * 0.0045));
                    lastX = e.clientX;
                    lastY = e.clientY;
                } else {
                    targetYaw = 0.65 + mouseNDC.x * 0.35;
                    targetPitch = 0.50 + mouseNDC.y * 0.16;
                }
            });

            section.addEventListener('mouseleave', () => {
                isDragging = false;
                isPointerInside = false;
                targetYaw = 0.65;
                targetPitch = 0.50;
            });

            section.addEventListener('click', (e) => {
                if (e.target.closest('.bento-card, a, button')) return;

                const empMat = new THREE.MeshBasicMaterial({
                    color: 0x00f2fe,
                    side: THREE.DoubleSide,
                    transparent: true,
                    opacity: 0.95,
                    blending: THREE.AdditiveBlending
                });
                const empMesh = new THREE.Mesh(empBaseGeo, empMat);
                const spawnX = isPointerInside ? smoothCursor3D.x : 0;
                const spawnZ = isPointerInside ? smoothCursor3D.z : 0;
                empMesh.position.set(spawnX, 3, spawnZ);
                worldGroup.add(empMesh);

                empWaves.push({
                    mesh: empMesh,
                    wx: spawnX,
                    wz: spawnZ,
                    r: 10,
                    maxR: 680,
                    alpha: 0.95
                });
            });

            function onResize() {
                width = section.offsetWidth;
                height = section.offsetHeight;
                camera.aspect = width / height;
                camera.updateProjectionMatrix();
                renderer.setSize(width, height);
            }
            window.addEventListener('resize', onResize);

            let clock = 0;
            function render3D() {
                if (!isVisible) return;
                clock += 0.015;

                const idleOrbit = Math.sin(clock * 0.35) * 0.09;
                camYaw += ((targetYaw + idleOrbit) - camYaw) * 0.05;
                camPitch += (targetPitch - camPitch) * 0.05;

                const radius = window.innerWidth < 768 ? 490 : 390;
                camera.position.x = Math.sin(camYaw) * Math.cos(camPitch) * radius;
                camera.position.y = Math.sin(camPitch) * radius;
                camera.position.z = Math.cos(camYaw) * Math.cos(camPitch) * radius;
                camera.lookAt(0, 15, 0);

                ring1.rotation.z += 0.004;
                ring2.rotation.z -= 0.003;
                ring3.rotation.z += 0.002;

                if (isPointerInside) {
                    raycaster.setFromCamera(mouseNDC, camera);
                    raycaster.ray.intersectPlane(groundPlane, intersectPoint);
                    smoothCursor3D.lerp(intersectPoint, 0.15);
                    cursorScanner.position.x = smoothCursor3D.x;
                    cursorScanner.position.z = smoothCursor3D.z;
                    cursorScanner.rotation.z -= 0.04;
                    cursorScanner.material.opacity += (0.75 - cursorScanner.material.opacity) * 0.1;
                } else {
                    cursorScanner.material.opacity *= 0.9;
                }

                for (let i = 0; i < arcPulses.length; i++) {
                    const ap = arcPulses[i];
                    ap.t = (ap.t + ap.speed) % 1;
                    ap.curve.getPoint(ap.t, ap.mesh.position);
                }

                for (let i = empWaves.length - 1; i >= 0; i--) {
                    const ew = empWaves[i];
                    ew.r += 9.5;
                    ew.alpha *= 0.96;
                    ew.mesh.scale.set(ew.r, 1, ew.r);
                    ew.mesh.material.opacity = ew.alpha;

                    if (ew.r >= ew.maxR || ew.alpha < 0.03) {
                        worldGroup.remove(ew.mesh);
                        ew.mesh.material.dispose();
                        empWaves.splice(i, 1);
                    }
                }

                const colors = terrainGeo.attributes.color.array;
                for (let i = 0; i < vertexCount; i++) {
                    const vx = posAttr.getX(i);
                    const vz = posAttr.getZ(i);
                    let dynamicH = baseHeights[i] + Math.sin(vx * 0.015 + clock) * Math.cos(vz * 0.015 + clock) * 2.2;
                    let energy = 0;

                    if (isPointerInside) {
                        const dMouse = Math.hypot(vx - smoothCursor3D.x, vz - smoothCursor3D.z);
                        if (dMouse < 135) {
                            const factor = Math.pow(1 - dMouse / 135, 2);
                            dynamicH += factor * 28;
                            energy = Math.max(energy, factor);
                        }
                    }

                    for (let w = 0; w < empWaves.length; w++) {
                        const ew = empWaves[w];
                        const dWave = Math.abs(Math.hypot(vx - ew.wx, vz - ew.wz) - ew.r);
                        if (dWave < 48) {
                            const wFactor = Math.cos((dWave / 48) * (Math.PI * 0.5)) * ew.alpha;
                            dynamicH += wFactor * 34;
                            energy = Math.max(energy, wFactor);
                        }
                    }

                    posAttr.setY(i, dynamicH);

                    const normH = Math.min(1, Math.max(0, (dynamicH + 10) / 95));
                    colors[i * 3]     = 0.06 + normH * 0.15 + energy * 0.1;
                    colors[i * 3 + 1] = 0.32 + normH * 0.50 + energy * 0.65;
                    colors[i * 3 + 2] = 0.75 + normH * 0.25 + energy * 0.25;
                }
                posAttr.needsUpdate = true;
                terrainGeo.attributes.color.needsUpdate = true;

                for (let i = 0; i < towers.length; i++) {
                    const t = towers[i];
                    let scaleTarget = 1;
                    let glowBoost = 0;

                    if (isPointerInside) {
                        const d = Math.hypot(t.wx - smoothCursor3D.x, t.wz - smoothCursor3D.z);
                        if (d < 120) {
                            const f = 1 - d / 120;
                            scaleTarget += f * 0.65;
                            glowBoost = Math.max(glowBoost, f);
                        }
                    }

                    for (let w = 0; w < empWaves.length; w++) {
                        const ew = empWaves[w];
                        const dW = Math.abs(Math.hypot(t.wx - ew.wx, t.wz - ew.wz) - ew.r);
                        if (dW < 45) {
                            const wf = Math.cos((dW / 45) * (Math.PI * 0.5)) * ew.alpha;
                            scaleTarget += wf * 0.85;
                            glowBoost = Math.max(glowBoost, wf);
                        }
                    }

                    t.curScale += (scaleTarget - t.curScale) * 0.15;
                    t.group.scale.y = t.curScale;
                    t.bodyMat.opacity = t.baseOpacity + glowBoost * 0.42;
                }

                renderer.render(scene, camera);
                animId = requestAnimationFrame(render3D);
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        if (!isVisible) {
                            isVisible = true;
                            onResize();
                            render3D();
                        }
                    } else {
                        isVisible = false;
                        if (animId) cancelAnimationFrame(animId);
                    }
                });
            }, { threshold: 0.05 });

            observer.observe(section);
        })();

        const sourceCatalog = {
            kbb: {
                title: "Kayseri Büyükşehir Belediyesi Açık Veri Portalı",
                badge: "8 Aktif Veri Seti + 5 Planlanan Katman",
                logo: "assets/icons/veri-platformu2.png",
                url: "https://acikveri.kayseri.bel.tr/",
                datasets: [
                    { name: "Nöbetçi Eczaneler Veri Seti", endpoint: "api/api_getir.php?endpoint=nobetci_eczane", format: "REST JSON", desc: "Günlük nöbetçi eczane adı, açık adresi, telefon numarası, nöbet türü (24 Saat / Gece 01:00) ve WGS84 koordinatlarını içerir." },
                    { name: "Vefat ve Taziye İlanları Veri Seti", endpoint: "api/api_getir.php?endpoint=vefat", format: "REST JSON", desc: "Günlük vefat eden vatandaşların ad-soyad, doğum yeri/yılı, anne/baba adı, cenaze namazı vakti/camisi, mezarlık yeri ve taziye adresi koordinatlarını içerir." },
                    { name: "Mahalle ve Muhtarlık Envanteri (711 Mahalle)", endpoint: "assets/data/muhtarlar.json", format: "JSON & CBS Poligon", desc: "Kayseri'nin 16 ilçesindeki 711 mahallenin muhtar adı, iletişim numarası, mahalle nüfusu, yüzölçümü (ha) ve idari sınır poligonlarını barındırır." },
                    { name: "CBS İlçe Demografi ve Cadde/Sokak İstatistikleri", endpoint: "portal.php?hizmet=cadde_sokak", format: "CBS Veri Tablosu", desc: "16 ilçenin km²/m² yüzölçümü, 2024-2025 nüfus değişim oranları ve toplam 18.222 cadde, sokak, bulvar, meydan ve küme ev dağılımını içerir." },
                    { name: "Büyükşehir Hal Fiyat Listesi Servisi", endpoint: "api/api_getir.php?endpoint=hal", format: "REST JSON", desc: "Kayseri Yaş Sebze ve Meyve Hali'ndeki günlük meyve/sebze ürün fiyatlarını (₺) ve birim bilgilerini içerir." },
                    { name: "Ücretsiz Wi-Fi Erişim Noktaları", endpoint: "api/api_getir.php?endpoint=wifi_noktalari", format: "GeoJSON", desc: "Meydan, park ve tesislerdeki KBB ücretsiz internet noktalarının (properties.ADI) lokasyonlarını içerir." },
                    { name: "Şehir Otoparkları Envanteri", endpoint: "assets/data/otoparklar-456371.geojson.txt", format: "GeoJSON", desc: "Katlı ve açık şehir otoparklarının konum ve işletme adlarını (properties.ADI) barındırır." },
                    { name: "Semt Pazarları Coğrafi Veri Seti", endpoint: "assets/data/pazar_yeri-4932711.geojson.txt", format: "GeoJSON", desc: "Pazar yerlerinin kurulduğu gün (PAZARGUNAD), açıklama ve mahalle koordinatlarını içerir." }
                ],
                metrics: [
                    { label: "İletişim Protokolü", val: "HTTPS / Proxy JSON", color: "text-blue-400" },
                    { label: "İdari Kapsam", val: "16 İlçe / 711 Mahalle", color: "text-emerald-400" },
                    { label: "Koordinat Sistemi", val: "WGS84 / EPSG:4326", color: "text-purple-400" },
                    { label: "Yol Ağı Envanteri", val: "18,222 Cadde & Sokak", color: "text-amber-400" }
                ],
                code: `<span class="cm">// KBB Vefat, Muhtarlık ve Hal Fiyatları Çoklu Uç Nokta Entegrasyonu</span><br><span class="fn">fetch</span>(<span class="str">'api/api_getir.php?endpoint=vefat'</span>)<br>&nbsp;&nbsp;.<span class="fn">then</span>(<span class="prp">r</span> <span class="op">=&gt;</span> <span class="prp">r</span>.<span class="fn">json</span>())<br>&nbsp;&nbsp;.<span class="fn">then</span>(<span class="prp">list</span> <span class="op">=&gt;</span> <span class="prp">list</span>.<span class="fn">forEach</span>(<span class="prp">item</span> <span class="op">=&gt;</span> {<br>&nbsp;&nbsp;&nbsp;&nbsp;<span class="kw">const</span> { <span class="prp">adiSoyadi</span>, <span class="prp">taziyeAdresi</span>, <span class="prp">mezarlikYeri</span>, <span class="prp">latitude</span>, <span class="prp">longitude</span> } <span class="op">=</span> <span class="prp">item</span>;<br>&nbsp;&nbsp;}));`,
                note: `<i class="fa-solid fa-circle-info text-blue-400 mr-1.5"></i><b>Mimari Not:</b> KAYBİS, KASKİ Su Kesintileri, Şarj Noktaları ve Spor Tesisleri katmanları portal arayüzünde tanımlanmış olup, kurumun ilgili canlı uç noktaları açıldığında aynı proxy hattı üzerinden otomatik devreye girecektir.`
            },
            kandilli: {
                title: "Boğaziçi Üniversitesi Kandilli Rasathanesi API",
                badge: "Gerçek Zamanlı Sismik Telemetri",
                logo: "assets/icons/kandilli.jpg",
                url: "http://www.koeri.boun.edu.tr/",
                datasets: [
                    { name: "Türkiye Geneli Canlı Deprem Akışı (Son 100 Deprem)", endpoint: "https://api.orhanaydogdu.com.tr/deprem/kandilli/live", format: "Live GeoJSON", desc: "Türkiye ve yakın çevresindeki son 100 depremin merkez üssü (title), büyüklük (mag), derinlik (depth km) ve zaman damgası verilerini anlık sunar." }
                ],
                metrics: [
                    { label: "Veri Akış Tipi", val: "Canlı REST / GeoJSON", color: "text-purple-400" },
                    { label: "Sorgu Limiti", val: "Son 100 Sismik Kayıt", color: "text-emerald-400" },
                    { label: "Büyüklük Ölçeği", val: "ML / Mw (toFixed 1)", color: "text-amber-400" },
                    { label: "Kamera Odaklama", val: "Otomatik Zoom 6 (TR)", color: "text-blue-400" }
                ],
                code: `<span class="cm">// Kandilli Canlı Deprem Akışı ve Büyüklük (Mw) İşleme</span><br><span class="fn">fetch</span>(<span class="str">'https://api.orhanaydogdu.com.tr/deprem/kandilli/live'</span>)<br>&nbsp;&nbsp;.<span class="fn">then</span>(<span class="prp">r</span> <span class="op">=&gt;</span> <span class="prp">r</span>.<span class="fn">json</span>())<br>&nbsp;&nbsp;.<span class="fn">then</span>(<span class="prp">d</span> <span class="op">=&gt;</span> <span class="prp">d.result</span>.<span class="fn">slice</span>(<span class="num">0</span>, <span class="num">100</span>).<span class="fn">forEach</span>(<span class="prp">q</span> <span class="op">=&gt;</span> {<br>&nbsp;&nbsp;&nbsp;&nbsp;<span class="kw">const</span> [<span class="prp">lng</span>, <span class="prp">lat</span>] <span class="op">=</span> <span class="prp">q.geojson.coordinates</span>;<br>&nbsp;&nbsp;&nbsp;&nbsp;<span class="kw">const</span> <span class="prp">mag</span> <span class="op">=</span> <span class="fn">parseFloat</span>(<span class="prp">q.mag</span>).<span class="fn">toFixed</span>(<span class="num">1</span>);<br>&nbsp;&nbsp;}));`,
                note: `<i class="fa-solid fa-bolt text-purple-400 mr-1.5"></i><b>Entegrasyon Detayı:</b> Bu veri seti hem harita üzerindeki mor deprem işaretçilerini hem de sayfadaki "Canlı Telemetri Akışı" barında yer alan son deprem bilgisini gerçek zamanlı besler.`
            },
            afad: {
                title: "AFAD & KBB Afet ve Acil Durum Toplanma Alanları",
                badge: "Kriz & Güvenli Bölge Mekansal Veri Seti",
                logo: "assets/icons/afad2.png",
                url: "https://www.afad.gov.tr/",
                datasets: [
                    { name: "Kayseri Afet ve Acil Durum Toplanma Alanları", endpoint: "assets/data/toplanma-alanlari_alan-3829284.geojson.txt", format: "GeoJSON", desc: "Kayseri genelindeki tüm resmi toplanma alanlarının adını (ADI), bağlı bulunduğu ilçeyi (ILCEAD), yüzölçümü kapasitesini (ALAN m²) ve hassas ENLEM / BOYLAM değerlerini içerir." }
                ],
                metrics: [
                    { label: "Veri Formatı", val: "GeoJSON FeatureCollection", color: "text-emerald-400" },
                    { label: "Kapsam", val: "Tüm Kayseri İlçeleri", color: "text-blue-400" },
                    { label: "Öznitelik Verisi", val: "Alan Kapasitesi (m²)", color: "text-amber-400" },
                    { label: "Rota Entegrasyonu", val: "Google Maps Deep-Link", color: "text-purple-400" }
                ],
                code: `<span class="cm">// AFAD Toplanma Alanı Kapasite ve Koordinat Çözümleme</span><br><span class="prp">data.features</span>.<span class="fn">forEach</span>(<span class="prp">item</span> <span class="op">=&gt;</span> {<br>&nbsp;&nbsp;<span class="kw">const</span> { <span class="prp">ADI</span>, <span class="prp">ILCEAD</span>, <span class="prp">ALAN</span>, <span class="prp">ENLEM</span>, <span class="prp">BOYLAM</span> } <span class="op">=</span> <span class="prp">item.properties</span>;<br>&nbsp;&nbsp;<span class="kw">const</span> <span class="prp">lat</span> <span class="op">=</span> <span class="fn">parseFloat</span>(<span class="prp">ENLEM</span>), <span class="prp">lng</span> <span class="op">=</span> <span class="fn">parseFloat</span>(<span class="prp">BOYLAM</span>);<br>&nbsp;&nbsp;<span class="fn">addPortalMarker</span>(<span class="prp">lat</span>, <span class="prp">lng</span>, <span class="prp">afadIcon</span>, <span class="str">\`Kapasite: \${ALAN} m²\`</span>, <span class="str">'afad'</span>);<br>});`,
                note: `<i class="fa-solid fa-shield-halved text-emerald-400 mr-1.5"></i><b>Kriz Yönetimi:</b> Herhangi bir kısıtlama olmaksızın veri setindeki yüzlerce güvenli alan noktasının tamamı Canvas motoru üzerine işlenerek en yakın noktaya Google Haritalar rotası oluşturulur.`
            },
            kultur: {
                title: "Kayseri İl Kültür ve Turizm BakanlığıEnvanteri",
                badge: "Kültürel Miras & Turizm Veri Seti",
                logo: "assets/icons/kültür2.png",
                url: "https://ktb.gov.tr/",
                datasets: [
                    { name: "Kayseri Müzeleri, Kümbetler ve Tarihi Yerler", endpoint: "assets/data/kayseri-gezilecek-yerler.json", format: "JSON Array", desc: "Şehirdeki tarihi yapıların başlığını (title), detaylı tarihçe metnini (content), yüksek çözünürlüklü görsel bağlantısını (bannerImage) ve koordinatlarını barındırır." }
                ],
                metrics: [
                    { label: "Veri Yapısı", val: "Yapılandırılmış JSON", color: "text-rose-400" },
                    { label: "Medya Desteği", val: "Banner Image Lazy-Load", color: "text-amber-400" },
                    { label: "Metin İşleme", val: "Regex Boşluk/Noktalama", color: "text-blue-400" },
                    { label: "Pop-up Şablonu", val: "Dinamik Genişletilebilir", color: "text-emerald-400" }
                ],
                code: `<span class="cm">// Tarihi Yerler Görsel ve Genişletilebilir Tarihçe Şeması</span><br><span class="kw">const</span> { <span class="prp">title</span>, <span class="prp">content</span>, <span class="prp">bannerImage</span>, <span class="prp">latitude</span>, <span class="prp">longitude</span> } <span class="op">=</span> <span class="prp">item</span>;<br><span class="kw">const</span> <span class="prp">shortText</span> <span class="op">=</span> <span class="prp">content.length</span> <span class="op">&gt;</span> <span class="num">100</span> <span class="op">?</span> <span class="prp">content</span>.<span class="fn">substring</span>(<span class="num">0</span>, <span class="num">100</span>) <span class="op">+</span> <span class="str">'...'</span> <span class="op">:</span> <span class="prp">content</span>;`,
                note: `<i class="fa-solid fa-landmark text-rose-400 mr-1.5"></i><b>Görsel Zenginleştirme:</b> Bu veri seti, harita pop-up'larında fotoğraf (<code>bannerImage</code>) ve dinamik "Açıklamayı Genişlet" butonu barındıran özel zengin kart şablonunu kullanır.`
            },
            meteo: {
                title: "Open-Meteo Küresel Meteoroloji Servisi",
                badge: "Gerçek Zamanlı Hava Durumu Telemetrisi",
                logo: "assets/icons/open2.png",
                url: "https://open-meteo.com/",
                datasets: [
                    { name: "Kayseri Anlık Hava Durumu ve Rüzgar Telemetrisi", endpoint: "https://api.open-meteo.com/v1/forecast?latitude=38.7205&longitude=35.4826&current_weather=true", format: "REST JSON", desc: "Kayseri merkez koordinatları (38.7205, 35.4826) için anlık sıcaklık (°C), rüzgar hızı (km/s), rüzgar yönü ve WMO hava durumu kodlarını döndürür." }
                ],
                metrics: [
                    { label: "Kimlik Doğrulama", val: "Açık Erişim (No-Key)", color: "text-emerald-400" },
                    { label: "Hedef Koordinat", val: "38.7205°N, 35.4826°E", color: "text-amber-400" },
                    { label: "Parametreler", val: "Sıcaklık (°C) & Rüzgar", color: "text-blue-400" },
                    { label: "Yanıt Süresi", val: "~45ms Küresel CDN", color: "text-purple-400" }
                ],
                code: `<span class="cm">// Open-Meteo Kayseri Koordinat Bazlı Canlı Sorgu</span><br><span class="fn">fetch</span>(<span class="str">'https://api.open-meteo.com/v1/forecast?latitude=38.7205&amp;longitude=35.4826&amp;current_weather=true'</span>)<br>&nbsp;&nbsp;.<span class="fn">then</span>(<span class="prp">res</span> <span class="op">=&gt;</span> <span class="prp">res</span>.<span class="fn">json</span>())<br>&nbsp;&nbsp;.<span class="fn">then</span>(({ <span class="prp">current_weather</span> }) <span class="op">=&gt;</span> {<br>&nbsp;&nbsp;&nbsp;&nbsp;<span class="kw">const</span> { <span class="prp">temperature</span>, <span class="prp">windspeed</span> } <span class="op">=</span> <span class="prp">current_weather</span>;<br>&nbsp;&nbsp;});`,
                note: `<i class="fa-solid fa-cloud-sun text-amber-400 mr-1.5"></i><b>API Avantajı:</b> API anahtarı (API Key) gerektirmeyen açık kaynaklı meteoroloji motoru sayesinde hem ana sayfa telemetri barı hem de <code>portal.php</code> sol alt widget'ı anlık güncellenir.`
            }
        };

        function toggleSourceInspector(key) {
            const drawer = document.getElementById('source-drawer');
            const allCards = document.querySelectorAll('.source-card');

            if (activeSourceKey === key && drawer.classList.contains('drawer-open')) {
                closeSourceInspector();
                return;
            }

            activeSourceKey = key;
            const info = sourceCatalog[key];
            if (!info) return;

            allCards.forEach(c => c.classList.remove('source-card-active'));
            const activeCard = document.getElementById('src-card-' + key);
            if (activeCard) activeCard.classList.add('source-card-active');

            document.getElementById('inspector-logo').src = info.logo;
            document.getElementById('inspector-badge').innerText = info.badge;
            document.getElementById('inspector-title').innerText = info.title;
            document.getElementById('inspector-link').href = info.url;
            document.getElementById('inspector-code').innerHTML = info.code;
            document.getElementById('inspector-note').innerHTML = info.note;

            const metricsEl = document.getElementById('inspector-metrics');
            if (metricsEl && info.metrics) {
                metricsEl.innerHTML = info.metrics.map(m => `
                    <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-3">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block mb-0.5">${m.label}</span>
                        <span class="text-xs font-extrabold ${m.color} font-mono">${m.val}</span>
                    </div>
                `).join('');
            }

            const listEl = document.getElementById('inspector-datasets');
            listEl.innerHTML = info.datasets.map(ds => `
                <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700/80 hover:border-blue-500/50 transition">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                        <h5 class="font-bold text-white text-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span> ${ds.name}
                        </h5>
                        <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30">${ds.format}</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed mb-2.5">${ds.desc}</p>
                    <div class="flex items-center gap-2 text-[11px] font-mono text-emerald-400 bg-slate-950/80 px-3 py-1.5 rounded-lg border border-slate-800 overflow-x-auto">
                        <i class="fa-solid fa-link text-slate-500 shrink-0"></i>
                        <span class="truncate">${ds.endpoint}</span>
                    </div>
                </div>
            `).join('');

            drawer.classList.add('drawer-open');
        }

        function closeSourceInspector() {
            const drawer = document.getElementById('source-drawer');
            drawer.classList.remove('drawer-open');
            document.querySelectorAll('.source-card').forEach(c => c.classList.remove('source-card-active'));
            activeSourceKey = null;
        }
        
        var heroMap = L.map('hero-map', {
            zoomControl: false, 
            attributionControl: false,
            scrollWheelZoom: false,
            dragging: false,
            doubleClickZoom: false,
            preferCanvas: true
        }).setView([38.7205, 35.4826], initialZoom); 

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(heroMap);

        var heroMarkers = L.layerGroup().addTo(heroMap);

        let bentoAnimLock = false;
        function toggleBentoDetails(cardId) {
            if (bentoAnimLock) return;
            bentoAnimLock = true;

            const targetCard = document.getElementById(cardId);
            const allCards = document.querySelectorAll('.bento-card');
            const isExpanding = !targetCard.classList.contains('bento-expanded');
            const icon = targetCard.querySelector('.btn-icon');

            if (isExpanding) {
                allCards.forEach(c => {
                    if (c.id !== cardId) {
                        c.classList.add('bento-fade-out');
                    }
                });

                setTimeout(() => {
                    allCards.forEach(c => {
                        if (c.id !== cardId) c.classList.add('hidden');
                    });

                    targetCard.classList.remove(targetCard.dataset.col);
                    targetCard.classList.add('md:col-span-12', 'bento-expanded');

                    if (icon) {
                        icon.classList.remove('fa-arrow-right');
                        icon.classList.add('fa-compress');
                    }

                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            targetCard.classList.add('details-visible');
                            bentoAnimLock = false;
                        });
                    });
                }, 260);
            } else {
                targetCard.classList.remove('details-visible');
                targetCard.classList.add('bento-fade-out');

                setTimeout(() => {
                    targetCard.classList.remove('md:col-span-12', 'bento-expanded');
                    targetCard.classList.add(targetCard.dataset.col);

                    if (icon) {
                        icon.classList.remove('fa-compress');
                        icon.classList.add('fa-arrow-right');
                    }

                    allCards.forEach(c => {
                        if (c.id !== cardId) {
                            c.classList.remove('hidden');
                            c.classList.add('bento-fade-out');
                        }
                    });

                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            allCards.forEach(c => c.classList.remove('bento-fade-out'));
                            setTimeout(() => { bentoAnimLock = false; }, 280);
                        });
                    });
                }, 260);
            }
        }

        const servicesSection = document.getElementById('services-section');
        if (servicesSection) {
            servicesSection.addEventListener('mousemove', function(e) {
                document.querySelectorAll('.glow-card').forEach(card => {
                    const rect = card.getBoundingClientRect();
                    card.style.setProperty('--mouse-x', `${e.clientX - rect.left}px`);
                    card.style.setProperty('--mouse-y', `${e.clientY - rect.top}px`);
                });
            });
        }

        function loadLiveWeatherTicker() {
            fetch('https://api.open-meteo.com/v1/forecast?latitude=38.7205&longitude=35.4826&current_weather=true')
                .then(r => r.json())
                .then(d => {
                    if (d && d.current_weather) {
                        const wEl = document.getElementById('live-ticker-weather');
                        if (wEl) wEl.innerText = `${d.current_weather.temperature}°C (Rüzgar: ${d.current_weather.windspeed} km/s)`;
                    }
                })
                .catch(() => {});
        }
        loadLiveWeatherTicker();

        function updateMarkerCounter() {
            totalLoadedMarkers++;
            const mEl = document.getElementById('live-ticker-markers');
            if (mEl) mEl.innerText = `${totalLoadedMarkers} Nokta Hazır`;
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
                'BILECIK': 'BİLECİK', 'USAK': 'UŞAK', 'AYDIN': 'AYDIN', 'DENIZLI': 'DENİZLİ',
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

        function secimleriSifirla() {
            document.querySelectorAll('.custom-marker-icon, .quake-marker-icon').forEach(icon => {
                icon.classList.remove('border-yellow-500', 'border-4', 'scale-125', 'z-50');
                if (icon.dataset.defaultBorder) {
                    icon.classList.add(icon.dataset.defaultBorder, 'border-2');
                } else {
                    icon.classList.add('border-white', 'border-2');
                }
            });
        }
        heroMap.on('popupclose', secimleriSifirla);
        heroMap.on('click', secimleriSifirla);

        heroMap.on('popupopen', function(e) {
            if (!markersInteractive) {
                heroMap.closePopup();
                return;
            }
            e.popup.update();
            setTimeout(() => { e.popup.update(); }, 50);
            let marker = e.popup._source;
            if (marker) {
                secimleriSifirla();
                setTimeout(() => {
                    if (marker.getElement()) {
                        let iconEl = marker.getElement().querySelector('.custom-marker-icon, .quake-marker-icon');
                        if (iconEl) {
                            iconEl.classList.remove('border-white', 'border-red-500', 'border-orange-500', 'border-2');
                            iconEl.classList.add('border-yellow-500', 'border-4', 'scale-125', 'z-50');
                        }
                    }
                }, 50);
            }
        });

        function toastGoster(baslik, mesaj) {
            let toast = document.getElementById('toast-panel');
            toast.innerHTML = '<div class="bg-gray-800 text-white px-6 py-4 rounded-2xl shadow-xl flex items-center gap-4"><i class="fa-solid fa-circle-info text-2xl text-blue-400"></i><div><h4 class="font-bold text-[15px]">' + baslik + '</h4><p class="text-[12px] text-gray-300 mt-1">' + mesaj + '</p></div></div>';
            toast.classList.remove('translate-y-20', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 4000);
        }

        function toggleSubmenu(targetId, otherId) {
            let target = document.getElementById(targetId);
            let other = document.getElementById(otherId);
            
            if(other.classList.contains('submenu-open')) {
                other.classList.remove('submenu-open');
            }
            
            if(target.classList.contains('submenu-open')) {
                target.classList.remove('submenu-open');
            } else {
                target.classList.add('submenu-open');
            }
        }
        
        function closeSubmenus() {
            document.getElementById('aktif-submenu').classList.remove('submenu-open');
            document.getElementById('deaktif-submenu').classList.remove('submenu-open');
        }

        function filterHeroMap(category, moveCamera = true) {
            currentCategory = category;
            
            document.querySelectorAll('.hero-filter-btn').forEach(btn => {
                btn.classList.remove('border-blue-500', 'bg-blue-50');
                btn.classList.add('border-transparent');
            });
            let activeBtn = document.getElementById('btn-filter-' + category);
            if(activeBtn) {
                activeBtn.classList.add('border-blue-500', 'bg-blue-50');
                activeBtn.classList.remove('border-transparent');
            }
            
            if (['kaybis', 'kaski', 'sarj', 'spor'].includes(category)) {
                toastGoster('Veri Sağlanamıyor', 'İlgili kurum tarafından bu hizmet için aktif API bağlantısı sunulmamaktadır.');
            } else if (category === 'kamera') {
                toastGoster('Canlı Yayın Kapalı', 'KVKK gereği Şehir Kameraları yayını durdurulmuştur.');
            } else if (category === 'muhtar') {
                toastGoster('Mahalle & Muhtarlık Bilgileri', 'Detaylı bilgi için şehir portalına giriş yapın.');
            } else if (category === 'cadde_sokak') {
                toastGoster('CBS İlçe İstatistikleri', '16 ilçenin nüfus, yüzölçümü ve cadde/sokak istatistikleri haritada yer alıyor. Detaylı bilgi için şehir portalına giriş yapın.');
            }
            
            heroMarkers.eachLayer(function(marker) {
                let outerEl = marker.getElement();
                let innerEl = outerEl ? outerEl.querySelector('.hero-marker') : null;
                
                let shouldShow = false;
                if (category === 'all') {
                    shouldShow = !['muhtar', 'cadde_sokak'].includes(marker.category);
                } else {
                    shouldShow = (marker.category === category);
                }
                
                if (shouldShow) {
                    marker.isFilteredOut = false; 
                    if (outerEl && innerEl && markersInteractive) {
                        requestAnimationFrame(() => {
                            innerEl.classList.remove('scale-0', 'opacity-0');
                            innerEl.classList.add('scale-100', 'opacity-100');
                            outerEl.style.pointerEvents = 'auto';
                        });
                    }
                } else {
                    marker.isFilteredOut = true; 
                    if (outerEl && innerEl) {
                        innerEl.classList.remove('scale-100', 'opacity-100');
                        innerEl.classList.add('scale-0', 'opacity-0');
                        outerEl.style.pointerEvents = 'none'; 
                    }
                }
            });
            
            heroMap.closePopup();

            if(moveCamera) {
                let targetZoom = initialZoom;
                let targetCenter = L.latLng(38.7205, 35.4826);

                if (category === 'deprem') {
                    targetZoom = 6;
                    targetCenter = L.latLng(39.2, 35.0);
                    currentMinZoom = 5;
                } else if (category === 'muhtar' || category === 'cadde_sokak') {
                    targetZoom = 9;
                    targetCenter = L.latLng(38.68, 35.65);
                    currentMinZoom = 8;
                } else {
                    currentMinZoom = 10;
                }

                let currentCenter = heroMap.getCenter();
                let isSameLocation = (heroMap.getZoom() === targetZoom) && 
                                     (Math.abs(currentCenter.lat - targetCenter.lat) < 0.01) && 
                                     (Math.abs(currentCenter.lng - targetCenter.lng) < 0.01);
                                     
                if (!isSameLocation) {
                    heroMap.stop();
                    isFlying = true;
                    heroMap.flyTo(targetCenter, targetZoom, { duration: 1.2, easeLinearity: 0.25 });
                }
            }
        }
        
        heroMap.on('moveend', function() {
            isFlying = false;
        });

        function addPortalMarker(lat, lng, rawIconHtml, popupContent, categoryClass) {
            let shouldBeVisibleInCurrent = (currentCategory === 'all')
                ? !['muhtar', 'cadde_sokak'].includes(categoryClass)
                : (categoryClass === currentCategory);

            let isVisible = markersInteractive && shouldBeVisibleInCurrent;
            let animState = isVisible ? 'scale-100 opacity-100' : 'scale-0 opacity-0';
            
            let iconHtml = rawIconHtml.replace('class="', `class="hero-marker marker-anim cat-${categoryClass} ${animState} `);
            let mIcon = L.divIcon({ html: iconHtml, className: 'hero-leaflet-marker', iconSize: [36,36], iconAnchor: [18,18] });
            let m = L.marker([lat, lng], {icon: mIcon}).addTo(heroMarkers);
            
            m.category = categoryClass;
            m.isFilteredOut = !shouldBeVisibleInCurrent;
            
            m.on('add', function() {
                let e = m.getElement();
                if(e) e.style.pointerEvents = (markersInteractive && !m.isFilteredOut) ? 'auto' : 'none';
            });

            m.on('click', function() {
                if (!markersInteractive) {
                    heroMap.closePopup();
                }
            });

            if (popupContent) {
                m.bindPopup(popupContent, {className: 'minimal-popup', closeButton: false, offset: [0,-5], maxWidth: 280});
            }
            updateMarkerCounter();
        }

        function loadHeroData() {
            fetch('api/api_getir.php?endpoint=nobetci_eczane').then(r=>r.json()).then(data => {
                let eczaneler = Array.isArray(data) ? data : (data.features || []);
                eczaneler.forEach(eczane => {
                    let lat = parseFloat(eczane.latitude), lng = parseFloat(eczane.longitude);
                    if(!isNaN(lat) && !isNaN(lng)) {
                        let ad = toTitleCase(eczane.name || 'Eczane');
                        let tel = eczane.phone || 'Telefon yok';
                        let adres = toTitleCase(eczane.address || 'Adres yok');
                        let nobet_turu = eczane.nobet_turu || eczane.NobetTuru || eczane.description || eczane.aciklama || eczane.type || "";
                        let badgeHTML = `<span class="inline-block bg-red-500 text-white text-[10px] px-2 py-0.5 rounded font-bold mb-2">Nöbetçi Eczane</span>`;
                        if (nobet_turu.toLowerCase().includes('24') || nobet_turu.toLowerCase().includes('tam')) {
                            badgeHTML = `<span class="inline-block bg-red-500 text-white text-[10px] px-2 py-0.5 rounded font-bold mb-2">24 Saat Açık</span>`;
                        } else if (nobet_turu.toLowerCase().includes('01:00') || nobet_turu.toLowerCase().includes('01.00') || nobet_turu.toLowerCase().includes('gece')) {
                            badgeHTML = `<span class="inline-block bg-blue-500 text-white text-[10px] px-2 py-0.5 rounded font-bold mb-2">Gece 01:00'a Kadar Açık</span>`;
                        }

                        let popupIcerik = `<div style="width: 240px;" class="p-3 w-64 box-border">${badgeHTML}<h3 class="font-bold text-[15px] text-red-600 border-b border-gray-100 pb-2 mb-2"><i class="fa-solid fa-staff-snake mr-1 text-red-400"></i> ${ad}</h3><div class="text-xs text-gray-600 mb-2"><p class="mb-1"><i class="fa-solid fa-location-dot w-4 text-gray-400"></i> ${adres}</p><p><i class="fa-solid fa-phone w-4 text-gray-400"></i> ${tel}</p></div><a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-3 py-2 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                        addPortalMarker(lat, lng, '<div data-default-border="border-red-500" class="custom-marker-icon w-9 h-9 bg-white rounded-full flex items-center justify-center border-2 border-red-500 shadow-md"><img src="assets/icons/eczane.png" class="w-6 h-6 object-contain"></div>', popupIcerik, 'eczane');
                    }
                });
            }).catch(e => console.log(e));

            fetch('api/api_getir.php?endpoint=vefat').then(r=>r.json()).then(data => {
                if (Array.isArray(data)) {
                    data.forEach(item => {
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

                            let popupIcerik = `<div style="width: 240px;" class="p-3 w-64 box-border"><span class="inline-block bg-zinc-800 text-white text-[10px] px-2 py-0.5 rounded font-bold mb-2"><i class="fa-solid fa-ribbon mr-1"></i>Taziye Noktası</span><h3 class="font-bold text-[15px] text-zinc-800 border-b border-gray-100 pb-2 mb-2">${ad}</h3><div class="text-xs text-gray-600 mb-2"><p class="mb-1"><i class="fa-solid fa-mosque w-4 text-gray-400"></i> ${vakit} / ${mezarlik}</p><p><i class="fa-solid fa-location-dot w-4 text-gray-400"></i> ${taziye}</p></div><a href="https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(rawTaziye)}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-3 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-2 shadow-sm"><i class="fa-solid fa-location-arrow mr-1"></i> Taziye Adresine Yol Tarifi Al</a>${camiPopupBtn}</div>`;
                            addPortalMarker(lat, lng, '<div class="custom-marker-icon w-8 h-8 bg-zinc-800 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-ribbon text-sm"></i></div>', popupIcerik, 'vefat');
                        }
                    });
                }
            }).catch(e => console.log(e));

            Object.keys(ilceMerkezKoordinatlari).forEach(ilceKey => {
                let coords = ilceMerkezKoordinatlari[ilceKey];
                let det = ilceDetayBilgileri[ilceKey] || {};
                let cs = ilceCaddeSokakBilgileri[ilceKey] || {};
                let ilceTitle = toTitleCase(ilceKey);

                let caddePopup = `<div style="width: 230px;" class="p-2.5 text-center box-border">
                    <span class="inline-block bg-indigo-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full mb-1"><i class="fa-solid fa-road mr-1"></i>İlçe İstatistikleri</span>
                    <h3 class="font-extrabold text-[15px] text-gray-800 mb-2">${ilceTitle} İlçesi</h3>
                    <div class="grid grid-cols-2 gap-1 text-[10px] text-gray-600 mb-2 text-left">
                        <div class="bg-gray-50 p-1.5 rounded border border-gray-100">Yüzölçümü: <b class="text-gray-800 block">${det.alan_km2} km²</b></div>
                        <div class="bg-gray-50 p-1.5 rounded border border-gray-100">2025 Nüfus: <b class="text-gray-800 block">${det.nufus_2025}</b></div>
                        <div class="bg-gray-50 p-1.5 rounded border border-gray-100">Mahalle: <b class="text-gray-800 block">${det.mahalle_sayisi} Adet</b></div>
                        <div class="bg-gray-50 p-1.5 rounded border border-gray-100">Cadde/Sokak: <b class="text-indigo-600 block">${cs.toplam} Yol</b></div>
                    </div>
                    <a href="portal.php?hizmet=cadde_sokak&ilce=${ilceKey}" class="block w-full bg-indigo-600 hover:bg-indigo-700 !text-white text-center px-2 py-1.5 rounded-md text-xs font-bold transition-colors shadow-sm"><i class="fa-solid fa-draw-polygon mr-1"></i> Detaylı Bilgi...</a>
                </div>`;
                addPortalMarker(coords[0], coords[1], '<div class="custom-marker-icon w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-road text-xs"></i></div>', caddePopup, 'cadde_sokak');

                let muhtarPopup = `<div style="width: 230px;" class="p-2.5 text-center box-border">
                    <span class="inline-block bg-teal-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full mb-1"><i class="fa-solid fa-users mr-1"></i>Muhtarlık Envanteri</span>
                    <h3 class="font-extrabold text-[15px] text-gray-800 mb-1">${ilceTitle} İlçesi</h3>
                    <p class="text-[11px] text-gray-600 mb-2">Toplam <b class="text-teal-700">${det.mahalle_sayisi} Mahalle</b> muhtarlık bilgisi ve mahalle sınır çizimi mevcuttur.</p>
                    <a href="portal.php?hizmet=muhtar&ilce=${ilceKey}" class="block w-full bg-teal-600 hover:bg-teal-700 !text-white text-center px-2 py-1.5 rounded-md text-xs font-bold transition-colors shadow-sm"><i class="fa-solid fa-list-check mr-1"></i> Mahalleleri & Muhtarları Gör</a>
                </div>`;
                addPortalMarker(coords[0], coords[1], '<div class="custom-marker-icon w-8 h-8 bg-teal-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-users text-xs"></i></div>', muhtarPopup, 'muhtar');
            });

            let halPopup = `<div style="width: 230px;" class="p-3 text-center box-border">
                <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-2 p-1">
                    <img src="assets/icons/hal.png" onerror="this.onerror=null; this.src='assets/icons/kbb.png';" class="w-full h-full object-contain rounded-full">
                </div>
                <h3 class="font-bold text-[14px] text-gray-800 leading-tight mb-1">Kayseri BB Yaş Sebze ve Meyve Hali</h3>
                <span class="text-[10px] bg-orange-500 text-white px-2 py-0.5 rounded-full font-bold inline-block mb-2">GÜNLÜK HAL FİYATLARI</span>
                <p class="text-[11px] text-gray-600 mb-2.5 leading-snug">Fevzioğlu Mh. Şehit Mehmet Erciyes Cd, No:50/A, Kocasinan/Kayseri</p>
                <a href="https://www.google.com/maps/dir/?api=1&destination=38.7657,35.5192" target="_blank" class="block w-full bg-blue-600 hover:bg-blue-700 !text-white text-center px-2 py-1.5 rounded-md text-xs font-bold transition-colors shadow-sm"><i class="fa-solid fa-location-arrow mr-1"></i> Hal Kompleksine Yol Tarifi</a>
            </div>`;
            addPortalMarker(38.7657, 35.5192, '<div data-default-border="border-orange-500" class="custom-marker-icon w-9 h-9 bg-white rounded-full flex items-center justify-center border-2 border-orange-500 shadow-md"><img src="assets/icons/hal.png" onerror="this.onerror=null; this.src=\'assets/icons/kbb.png\';" class="w-6 h-6 object-contain rounded-full"></div>', halPopup, 'hal');

            fetch('api/api_getir.php?endpoint=wifi_noktalari').then(r=>r.json()).then(data => {
                if(data && data.features) {
                    data.features.forEach(item => {
                        if(item.geometry && item.geometry.coordinates) {
                            let coords = item.geometry.coordinates;
                            let props = item.properties || {};
                            let ad = toTitleCase(props.ADI || 'Wi-Fi');
                            let popupIcerik = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-wifi text-xl text-green-600"></i></div><h3 class="font-bold text-[14px] text-gray-800 leading-tight mb-1">${ad}</h3><span class="text-[10px] bg-green-500 text-white px-2 py-0.5 rounded-full font-bold">KBB ÜCRETSİZ İNTERNET</span><a href="https://www.google.com/maps/dir/?api=1&destination=${coords[1]},${coords[0]}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                            addPortalMarker(coords[1], coords[0], '<div class="custom-marker-icon w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-wifi text-sm"></i></div>', popupIcerik, 'wifi');
                        }
                    });
                }
            }).catch(e => console.log(e));

            fetch('assets/data/otoparklar-456371.geojson.txt').then(r=>r.json()).then(data => {
                if(data && data.features) {
                    data.features.forEach(item => {
                        if(item.geometry && item.geometry.coordinates) {
                            let coords = item.geometry.coordinates;
                            let props = item.properties || {};
                            let ad = toTitleCase(props.ADI || 'Şehir Otoparkı');
                            let popupIcerik = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-square-parking text-2xl text-gray-800"></i></div><h3 class="font-bold text-[13px] text-gray-800 leading-tight mb-3">${ad}</h3><a href="https://www.google.com/maps/dir/?api=1&destination=${coords[1]},${coords[0]}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                            addPortalMarker(coords[1], coords[0], '<div class="custom-marker-icon w-8 h-8 bg-gray-800 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md font-bold font-sans">P</div>', popupIcerik, 'otopark');
                        }
                    });
                }
            }).catch(e => console.log(e));

            fetch('assets/data/pazar_yeri-4932711.geojson.txt').then(r=>r.json()).then(data => {
                if(data && data.features) {
                    data.features.forEach(item => {
                        if(item.geometry && item.geometry.coordinates) {
                            let coords = item.geometry.coordinates;
                            let props = item.properties || {};
                            let ad = toTitleCase(props.ADI || 'Pazar Yeri');
                            let gun = toTitleCase(props.PAZARGUNAD || 'Belirsiz');
                            let aciklama = props.ACIKLAMA ? duzenleMetin(toTitleCase(props.ACIKLAMA)) : '';
                            let popupIcerik = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-basket-shopping text-xl text-sky-600"></i></div><h3 class="font-bold text-[14px] text-gray-800 leading-tight mb-1">${ad}</h3><span class="text-[11px] bg-sky-500 text-white px-2 py-1 rounded-full font-bold block mt-1 mb-2">${gun}</span><span class="text-[10px] text-gray-500 block mb-3">${aciklama}</span><a href="https://www.google.com/maps/dir/?api=1&destination=${coords[1]},${coords[0]}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                            addPortalMarker(coords[1], coords[0], '<div class="custom-marker-icon w-8 h-8 bg-sky-500 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-basket-shopping text-sm"></i></div>', popupIcerik, 'pazar');
                        }
                    });
                }
            }).catch(e => console.log(e));

            fetch('assets/data/kayseri-gezilecek-yerler.json').then(r=>r.json()).then(data => {
                if(Array.isArray(data)) {
                    data.forEach(item => {
                        let lat = parseFloat(item.latitude), lng = parseFloat(item.longitude);
                        if(!isNaN(lat) && !isNaN(lng)) {
                            let title = duzenleMetin(toTitleCase(item.title));
                            let fullContent = item.content ? duzenleMetin(item.content) : 'Kayseri tarihi alanı.';
                            let shortContent = fullContent.length > 100 ? fullContent.substring(0, 100) + '...' : fullContent;
                            let btnGenislet = fullContent.length > 100 ? `<button onclick="window.genisletMetin(event, this);" class="text-[11px] text-rose-600 font-bold mt-1 mb-2 block w-full text-center bg-rose-50 rounded py-1.5 hover:bg-rose-100 transition-colors">Açıklamayı Genişlet <i class="fa-solid fa-chevron-down"></i></button>` : '';
                            let resimHTML = item.bannerImage ? `<img src="${item.bannerImage}" style="height: 144px; width: 100%; object-fit: cover;" class="w-full h-36 object-cover rounded-md mb-2 shadow-sm border border-gray-200">` : '';
                            
                            let popupIcerik = `<div style="width: 240px;" class="p-1 w-64 box-border">
                                ${resimHTML}
                                <h3 class="font-bold text-[14px] text-rose-700 leading-tight mb-2">${title}</h3>
                                <div class="text-[11px] text-gray-600 mb-2 leading-tight">${shortContent}</div>
                                <div class="text-[11px] text-gray-600 mb-2 leading-tight hidden max-h-48 overflow-y-auto pr-1 scrollbar-hide">${fullContent}</div>
                                ${btnGenislet}
                                <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-2 rounded text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-2 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a>
                            </div>`;
                            
                            addPortalMarker(lat, lng, '<div data-default-border="border-white" class="custom-marker-icon w-8 h-8 bg-rose-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-building-columns text-sm"></i></div>', popupIcerik, 'tarihi');
                        }
                    });
                }
            }).catch(e => console.log(e));
            
            fetch('assets/data/toplanma-alanlari_alan-3829284.geojson.txt').then(r=>r.json()).then(data => {
                if(data && data.features) {
                    data.features.forEach(item => {
                        let props = item.properties || {};
                        let lat = parseFloat(props.ENLEM), lng = parseFloat(props.BOYLAM);
                        if(!isNaN(lat) && !isNaN(lng)) {
                            let ad = toTitleCase(props.ADI);
                            let ilce = props.ILCEAD ? `<span class="text-[10px] text-gray-600 bg-gray-100 px-2 py-0.5 rounded block mb-1">İlçe: ${toTitleCase(props.ILCEAD)}</span>` : '';
                            let popupIcerik = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-shield-halved text-xl text-emerald-600"></i></div><h3 class="font-bold text-[14px] text-gray-800 leading-tight mb-2">${ad}</h3>${ilce}<span class="text-[10px] text-emerald-700 font-bold bg-emerald-100 px-2 py-1 rounded block mb-3">Kapasite: ${props.ALAN} m²</span><a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                            addPortalMarker(lat, lng, '<div class="custom-marker-icon w-8 h-8 bg-emerald-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-shield-halved text-sm"></i></div>', popupIcerik, 'afad');
                        }
                    });
                }
            }).catch(e => console.log(e));

            fetch('https://api.orhanaydogdu.com.tr/deprem/kandilli/live').then(r=>r.json()).then(data => {
                if(data && data.result) {
                    if (data.result.length > 0) {
                        const latest = data.result[0];
                        const qEl = document.getElementById('live-ticker-quake');
                        if (qEl) {
                            qEl.innerText = `${toTitleCase(latest.title)} (${parseFloat(latest.mag).toFixed(1)} Mw)`;
                        }
                    }

                    data.result.slice(0, 100).forEach(deprem => {
                        let coords = deprem.geojson.coordinates;
                        let magDisplay = parseFloat(deprem.mag).toFixed(1);
                        let ad = toTitleCase(deprem.title);
                        
                        let rawDate = deprem.date || deprem.date_time || deprem.timestamp || "";
                        let date = "Bilinmiyor";
                        if (rawDate) { 
                            let p = rawDate.split(" "); 
                            if (p.length > 0) { 
                                let y = p[0].split(/[-.]/); 
                                if (y.length === 3) date = y[2] + "/" + y[1] + "/" + y[0] + (p[1] ? " " + p[1] : ""); 
                            } 
                        }
                        
                        let popupIcerik = `<div style="width: 190px;" class="p-2 w-48 text-center box-border"><h3 class="font-bold text-[14px] text-purple-700 leading-tight mb-1">${ad}</h3><p class="text-[11px] text-gray-600 mb-1">Şiddet: <b class="text-purple-600">${magDisplay}</b> | Derinlik: <b>${deprem.depth} km</b></p><p class="text-[10px] text-gray-500"><i class="fa-regular fa-clock mr-1"></i>${date}</p></div>`;
                        addPortalMarker(coords[1], coords[0], `<div class="quake-marker-icon w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md font-bold text-sm">${magDisplay}</div>`, popupIcerik, 'deprem');
                    });
                }
            }).catch(e => console.log(e));

            const kameralar = [
                { ad: "Erciyes Tekir Kapı", lat: 38.5414, lng: 35.5312 },
                { ad: "Cumhuriyet Meydanı", lat: 38.7205, lng: 35.4826 },
                { ad: "Sivas Caddesi", lat: 38.7250, lng: 35.4900 },
                { ad: "Kartal Kavşağı", lat: 38.7115, lng: 35.4930 },
                { ad: "Talas Anayurt", lat: 38.6850, lng: 35.5350 }
            ];
            kameralar.forEach(k => {
                let popupIcerik = `<div style="width: 240px;" class="p-2 w-64 text-center box-border"><h3 class="font-bold text-[14px] text-slate-800 border-b border-gray-100 pb-2 mb-2"><i class="fa-solid fa-video-slash text-red-500"></i> ${k.ad}</h3><div class="text-xs text-gray-600 mb-2 leading-tight">KVKK Kararı gereğince anlık yayın kapalıdır.</div></div>`;
                addPortalMarker(k.lat, k.lng, '<div class="custom-marker-icon w-8 h-8 bg-slate-700 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-video text-sm"></i></div>', popupIcerik, 'kamera');
            });
        }

        loadHeroData();

        function trigger3DEffect() {
            if(effectTriggered) return; 
            effectTriggered = true;
            markersInteractive = false;
            
            currentMinZoom = 10; 
            
            const content = document.getElementById('hero-content');
            const overlay = document.getElementById('hero-overlay');
            const nav = document.getElementById('main-nav');
            const filterMenu = document.getElementById('hero-filter-menu');
            const heroWrapper = document.getElementById('hero-wrapper');
            const heroMapEl = document.getElementById('hero-map');
            
            heroMap.closePopup();
            heroMapEl.classList.add('markers-locked');

            heroWrapper.classList.remove('map-scroll-passthrough');
            heroMapEl.classList.remove('map-scroll-passthrough');
            heroMap.dragging.enable();
            heroMap.scrollWheelZoom.enable();
            heroMap.doubleClickZoom.enable();

            nav.style.transform = 'translateY(-100%)';
            
            content.style.transition = 'transform 2.5s cubic-bezier(0.4, 0, 0.2, 1), opacity 1.5s ease-in 1s, filter 2.5s ease-in 0.5s';
            content.style.transform = 'scale(35) translateZ(0)';
            content.style.opacity = '0';
            content.style.filter = 'blur(25px)';
            
            overlay.style.transition = 'opacity 2.5s ease-out, backdrop-filter 2.5s ease-out';
            overlay.style.opacity = '0';
            overlay.style.backdropFilter = 'blur(0px)';
            
            clearTimeout(menuTimeout);
            menuTimeout = setTimeout(() => {
                if(effectTriggered) {
                    markersInteractive = true;
                    heroMapEl.classList.remove('markers-locked');

                    filterMenu.classList.remove('opacity-0', '-translate-y-full', 'pointer-events-none');
                    filterMenu.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');
                    
                    filterHeroMap(currentCategory, false);
                }
            }, 2000);
        }

        function restore3DEffect() {
            if(!effectTriggered) return;
            effectTriggered = false;
            markersInteractive = false;
            clearTimeout(menuTimeout);

            const content = document.getElementById('hero-content');
            const overlay = document.getElementById('hero-overlay');
            const nav = document.getElementById('main-nav');
            const filterMenu = document.getElementById('hero-filter-menu');
            const heroWrapper = document.getElementById('hero-wrapper');
            const heroMapEl = document.getElementById('hero-map');

            heroMapEl.classList.add('markers-locked');
            heroWrapper.classList.add('map-scroll-passthrough');
            heroMapEl.classList.add('map-scroll-passthrough');
            heroMap.dragging.disable();
            heroMap.scrollWheelZoom.disable();
            heroMap.doubleClickZoom.disable();
            heroMap.closePopup();
            
            heroMap.stop(); 
            isFlying = true; 
            heroMap.flyTo([38.7205, 35.4826], initialZoom, { duration: 1.5 });
            
            filterHeroMap('all', false);
            closeSubmenus();
            currentMinZoom = 10; 
            
            filterMenu.classList.add('opacity-0', '-translate-y-full', 'pointer-events-none');
            filterMenu.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');

            heroMarkers.eachLayer(function(marker) {
                let outerEl = marker.getElement();
                let innerEl = outerEl ? outerEl.querySelector('.hero-marker') : null;
                if (outerEl && innerEl) {
                    innerEl.classList.remove('scale-100', 'opacity-100');
                    innerEl.classList.add('scale-0', 'opacity-0');
                    outerEl.style.pointerEvents = 'none'; 
                }
            });

            nav.style.transform = 'translateY(0)';

            content.style.transition = 'transform 2s cubic-bezier(0.2, 1, 0.4, 1), opacity 1.5s ease-out, filter 1.5s ease-out';
            content.style.transform = 'scale(1) translateZ(0)';
            content.style.opacity = '1';
            content.style.filter = 'blur(0px)';
            
            overlay.style.transition = 'opacity 2s ease-in, backdrop-filter 2s ease-in';
            overlay.style.opacity = '1';
        }

        heroMap.on('zoom', function() {
            if (effectTriggered && !isFlying && heroMap.getZoom() < currentMinZoom) {
                restore3DEffect();
            }
        });

        window.addEventListener('scroll', () => {
            if (window.scrollY > 2 && effectTriggered) { 
                restore3DEffect();
            }
        });

        heroMap.on('mousedown', function(e) {
            if (window.innerWidth >= 768 && (!e.originalEvent || e.originalEvent.pointerType !== 'touch')) {
                trigger3DEffect();
            }
        });

        let lastTapTime = 0;
        let lastTapX = 0;
        let lastTapY = 0;

        document.getElementById('hero-wrapper').addEventListener('touchend', function(e) {
            if (effectTriggered) return;
            if (e.target.closest('a, button')) return;

            const currentTime = new Date().getTime();
            const tapLength = currentTime - lastTapTime;
            const touch = e.changedTouches[0];
            
            if (touch) {
                const dx = Math.abs(touch.clientX - lastTapX);
                const dy = Math.abs(touch.clientY - lastTapY);

                if (tapLength > 0 && tapLength < 350 && dx < 40 && dy < 40) {
                    e.preventDefault();
                    trigger3DEffect();
                    lastTapTime = 0;
                    return;
                }
                lastTapX = touch.clientX;
                lastTapY = touch.clientY;
            }
            lastTapTime = currentTime;
        }, { passive: false });

    </script>
</body>
</html>