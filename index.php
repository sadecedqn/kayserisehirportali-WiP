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

        .bg-grid-pattern {
            background-size: 32px 32px;
            background-image: 
                linear-gradient(to right, rgba(226, 232, 240, 0.6) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(226, 232, 240, 0.6) 1px, transparent 1px);
        }
        
        .open-meteo-logo { transform: scale(1.3); }

        .marker-anim {
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease-out;
            will-change: transform, opacity;
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
            max-width: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-width 0.5s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.35s ease-in;
        }
        .submenu-open {
            max-width: calc(100vw - 175px);
            opacity: 1;
            overflow-x: auto;
        }
        @media (min-width: 768px) {
            .submenu-open {
                max-width: 1000px;
                overflow: hidden;
            }
        }

        @keyframes scroll-left {
            0% { transform: translateX(0); }
            100% { transform: translateX(calc(-260px * 6)); } 
        }
        @keyframes scroll-right {
            0% { transform: translateX(calc(-260px * 6)); }
            100% { transform: translateX(0); }
        }
        .marquee-track-left {
            display: flex;
            gap: 20px;
            width: max-content;
            animation: scroll-left 30s linear infinite;
        }
        .marquee-track-right {
            display: flex;
            gap: 20px;
            width: max-content;
            animation: scroll-right 30s linear infinite;
        }
        .marquee-item {
            width: 240px;
            flex-shrink: 0;
        }

        .marquee-mask {
            mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
            -webkit-mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
        }

        .map-scroll-passthrough {
            touch-action: pan-y !important;
        }
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
                    <span class="font-bold text-[15px] sm:text-2xl tracking-tight text-gray-900 whitespace-nowrap">Kayseri - Şehir Portalı</span>
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
        
        <div id="hero-map" class="absolute inset-0 z-0 map-scroll-passthrough"></div>
        <div id="hero-overlay" class="absolute inset-0 bg-white/85 backdrop-blur-sm z-0 pointer-events-none transition-all duration-1000 ease-out"></div>
        
        <div id="hero-filter-menu" class="absolute top-3 md:top-4 left-0 w-full z-20 flex flex-col md:flex-row items-start md:items-center justify-start md:justify-center gap-1.5 md:gap-3 px-3 md:px-4 opacity-0 -translate-y-full transition-all duration-1000 ease-out pointer-events-none overflow-x-hidden md:overflow-x-auto scrollbar-hide py-1.5 md:py-2">

            <button onclick="filterHeroMap('all'); closeSubmenus();" id="btn-filter-all" class="hero-filter-btn shrink-0 px-3.5 py-1.5 md:px-5 md:py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-lg border-2 border-blue-500 font-bold text-[11px] md:text-[13px] text-gray-800 transition-all hover:bg-blue-50 z-30">Tümü</button>
            
            <div class="flex items-center gap-1.5 md:gap-2 max-w-full">
                <button onclick="toggleSubmenu('aktif-submenu', 'deaktif-submenu')" class="shrink-0 px-3.5 py-1.5 md:px-5 md:py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-md border border-gray-200 font-bold text-[11px] md:text-[13px] text-gray-800 hover:bg-gray-50 transition-all flex items-center gap-1.5 md:gap-2 z-30">
                    <span class="w-2 h-2 md:w-2.5 md:h-2.5 rounded-full bg-green-500 shadow-[0_0_5px_rgba(34,197,94,0.5)]"></span> Aktif Hizmetler <i class="fa-solid fa-chevron-right text-[9px] md:text-[10px] text-gray-400 ml-0.5 md:ml-1"></i>
                </button>
                <div id="aktif-submenu" class="submenu-container scrollbar-hide flex items-center gap-1.5 md:gap-2 py-1 pr-2">
                    <button onclick="filterHeroMap('eczane')" id="btn-filter-eczane" class="hero-filter-btn shrink-0 px-3 py-1.5 md:px-4 md:py-2 bg-white/95 backdrop-blur-md rounded-full shadow-sm border-2 border-transparent font-bold text-[11px] md:text-[12px] hover:bg-red-50 transition-all flex items-center gap-1.5"><img src="assets/icons/eczane.png" class="w-3.5 h-3.5 md:w-4 md:h-4 object-contain"> Eczaneler</button>
                    <button onclick="filterHeroMap('deprem')" id="btn-filter-deprem" class="hero-filter-btn shrink-0 px-3 py-1.5 md:px-4 md:py-2 bg-white/95 backdrop-blur-md rounded-full shadow-sm border-2 border-transparent font-bold text-[11px] md:text-[12px] hover:bg-purple-50 transition-all flex items-center gap-1.5"><img src="assets/icons/kandilli.jpg" onerror="this.src='assets/icons/kandilli.png'" class="w-4 h-4 md:w-6 md:h-6 object-contain rounded-full"> Depremler</button>
                    <button onclick="filterHeroMap('afad')" id="btn-filter-afad" class="hero-filter-btn shrink-0 px-3 py-1.5 md:px-4 md:py-2 bg-white/95 backdrop-blur-md rounded-full shadow-sm border-2 border-transparent font-bold text-[11px] md:text-[12px] hover:bg-emerald-50 transition-all flex items-center gap-1.5"><img src="assets/icons/afad.png" class="w-4 h-4 md:w-6 md:h-6 object-contain"> Toplanma Alanları</button>
                    <button onclick="filterHeroMap('otopark')" id="btn-filter-otopark" class="hero-filter-btn shrink-0 px-3 py-1.5 md:px-4 md:py-2 bg-white/95 backdrop-blur-md rounded-full shadow-sm border-2 border-transparent font-bold text-[11px] md:text-[12px] text-gray-800 hover:bg-gray-100 transition-all flex items-center gap-1.5"><i class="fa-solid fa-square-parking text-sm md:text-base"></i> Otoparklar</button>
                    <button onclick="filterHeroMap('wifi')" id="btn-filter-wifi" class="hero-filter-btn shrink-0 px-3 py-1.5 md:px-4 md:py-2 bg-white/95 backdrop-blur-md rounded-full shadow-sm border-2 border-transparent font-bold text-[11px] md:text-[12px] text-green-700 hover:bg-green-50 transition-all flex items-center gap-1.5"><i class="fa-solid fa-wifi text-xs md:text-sm"></i> Wi-Fi</button>
                    <button onclick="filterHeroMap('pazar')" id="btn-filter-pazar" class="hero-filter-btn shrink-0 px-3 py-1.5 md:px-4 md:py-2 bg-white/95 backdrop-blur-md rounded-full shadow-sm border-2 border-transparent font-bold text-[11px] md:text-[12px] text-sky-600 hover:bg-sky-50 transition-all flex items-center gap-1.5"><i class="fa-solid fa-basket-shopping text-xs md:text-sm"></i> Pazarlar</button>
                    <button onclick="filterHeroMap('tarihi')" id="btn-filter-tarihi" class="hero-filter-btn shrink-0 px-3 py-1.5 md:px-4 md:py-2 bg-white/95 backdrop-blur-md rounded-full shadow-sm border-2 border-transparent font-bold text-[11px] md:text-[12px] text-rose-700 hover:bg-rose-50 transition-all flex items-center gap-1.5"><i class="fa-solid fa-building-columns text-xs md:text-sm"></i> Tarihi Yerler</button>
                </div>
            </div>

            <div class="flex items-center gap-1.5 md:gap-2 max-w-full">
                <button onclick="toggleSubmenu('deaktif-submenu', 'aktif-submenu')" class="shrink-0 px-3.5 py-1.5 md:px-5 md:py-2.5 bg-white/95 backdrop-blur-md rounded-full shadow-md border border-gray-200 font-bold text-[11px] md:text-[13px] text-gray-800 hover:bg-gray-50 transition-all flex items-center gap-1.5 md:gap-2 z-30">
                    <span class="w-2 h-2 md:w-2.5 md:h-2.5 rounded-full bg-red-500 shadow-[0_0_5px_rgba(239,68,68,0.5)]"></span> Deaktif Hizmetler <i class="fa-solid fa-chevron-right text-[9px] md:text-[10px] text-gray-400 ml-0.5 md:ml-1"></i>
                </button>
                <div id="deaktif-submenu" class="submenu-container scrollbar-hide flex items-center gap-1.5 md:gap-2 py-1 pr-2">
                    <button onclick="filterHeroMap('kamera')" id="btn-filter-kamera" class="hero-filter-btn shrink-0 px-3 py-1.5 md:px-4 md:py-2 bg-white/95 backdrop-blur-md rounded-full shadow-sm border-2 border-transparent font-bold text-[11px] md:text-[12px] text-slate-800 hover:bg-slate-100 transition-all flex items-center gap-1.5"><i class="fa-solid fa-video text-xs md:text-sm"></i> Şehir Kameraları</button>
                    <button onclick="filterHeroMap('kaybis')" id="btn-filter-kaybis" class="hero-filter-btn shrink-0 px-3 py-1.5 md:px-4 md:py-2 bg-white/95 backdrop-blur-md rounded-full shadow-sm border-2 border-transparent font-bold text-[11px] md:text-[12px] hover:bg-blue-50 transition-all flex items-center gap-1.5"><img src="assets/icons/kaybis.png" class="w-4 h-4 md:w-6 md:h-6 object-contain"> KAYBİS</button>
                    <button onclick="filterHeroMap('kaski')" id="btn-filter-kaski" class="hero-filter-btn shrink-0 px-3 py-1.5 md:px-4 md:py-2 bg-white/95 backdrop-blur-md rounded-full shadow-sm border-2 border-transparent font-bold text-[11px] md:text-[12px] hover:bg-cyan-50 transition-all flex items-center gap-1.5"><img src="assets/icons/kaski.png" class="w-4 h-4 md:w-6 md:h-6 object-contain"> Su Kesintileri</button>
                    <button onclick="filterHeroMap('sarj')" id="btn-filter-sarj" class="hero-filter-btn shrink-0 px-3 py-1.5 md:px-4 md:py-2 bg-white/95 backdrop-blur-md rounded-full shadow-sm border-2 border-transparent font-bold text-[11px] md:text-[12px] text-amber-600 hover:bg-amber-50 transition-all flex items-center gap-1.5"><i class="fa-solid fa-charging-station text-xs md:text-sm"></i> Şarj Noktaları</button>
                    <button onclick="filterHeroMap('spor')" id="btn-filter-spor" class="hero-filter-btn shrink-0 px-3 py-1.5 md:px-4 md:py-2 bg-white/95 backdrop-blur-md rounded-full shadow-sm border-2 border-transparent font-bold text-[11px] md:text-[12px] text-indigo-600 hover:bg-indigo-50 transition-all flex items-center gap-1.5"><i class="fa-solid fa-volleyball text-xs md:text-sm"></i> Spor Tesisleri</button>
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


    <section id="nasil-calisir" class="relative z-20 bg-slate-50 bg-grid-pattern pt-12 sm:pt-16 pb-20 sm:pb-28 border-t border-slate-200/80 shadow-[0_-20px_50px_rgba(0,0,0,0.06)]">
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-14 sm:mb-20 bg-white/95 backdrop-blur-xl p-4 sm:p-6 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-200/80">
                
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-4 p-2 sm:px-4 border-r border-slate-100">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg sm:text-xl shrink-0"><i class="fa-solid fa-bolt"></i></div>
                    <div class="min-w-0">
                        <div class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight leading-tight">5 Dakika</div>
                        <div class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-normal sm:tracking-wider leading-snug break-words">Veri Senkronizasyonu</div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-4 p-2 sm:px-4 lg:border-r border-slate-100">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg sm:text-xl shrink-0"><i class="fa-solid fa-layer-group"></i></div>
                    <div class="min-w-0">
                        <div class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight leading-tight">12+ Katman</div>
                        <div class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-normal sm:tracking-wider leading-snug break-words">Mekansal Veri Seti</div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-4 p-2 sm:px-4 border-r border-slate-100 pt-4 lg:pt-0 border-t lg:border-t-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg sm:text-xl shrink-0"><i class="fa-solid fa-tower-broadcast"></i></div>
                    <div class="min-w-0">
                        <div class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight leading-tight">7/24 Canlı</div>
                        <div class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-normal sm:tracking-wider leading-snug break-words">Kesintisiz Akış</div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-4 p-2 sm:px-4 pt-4 lg:pt-0 border-t lg:border-t-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg sm:text-xl shrink-0"><i class="fa-solid fa-code-branch"></i></div>
                    <div class="min-w-0">
                        <div class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight leading-tight">%100 Açık</div>
                        <div class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-normal sm:tracking-wider leading-snug break-words">Şeffaf API Altyapısı</div>
                    </div>
                </div>

            </div>

            <div class="max-w-3xl mx-auto text-center mb-10 sm:mb-16">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900 text-white text-xs font-bold uppercase tracking-widest mb-4 shadow-sm">
                    <i class="fa-solid fa-microchip text-blue-400"></i> Sistem Mimarisi
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 tracking-tight leading-tight">
                    Şehrin Verisi Nasıl İşleniyor?
                </h2>
                <p class="mt-4 text-base sm:text-lg text-slate-600 font-medium">
                    Dağınık kurumsal verileri tek bir merkezde toplayan, anlamlandıran ve gerçek zamanlı olarak haritalandıran yeni nesil açık veri motoru.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-6">
                
                <div class="md:col-span-7 group relative bg-white rounded-[1.75rem] sm:rounded-[2rem] p-6 sm:p-8 md:p-10 shadow-sm hover:shadow-xl border border-slate-200/80 transition-all duration-500 overflow-hidden flex flex-col justify-between">
                    <div class="absolute -right-16 -top-16 w-64 h-64 bg-gradient-to-br from-blue-500/10 to-cyan-400/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700 pointer-events-none"></div>
                    
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-xl sm:text-2xl shadow-lg shadow-blue-600/30 group-hover:rotate-12 transition-transform duration-300">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-ping"></span> Canlı Senkronizasyon
                            </span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 mb-2.5">Otomatik Veri Senkronizasyonu</h3>
                        <p class="text-slate-600 leading-relaxed text-sm sm:text-base">
                            Arka planda çalışan otonom servislerimiz, Kayseri Büyükşehir Belediyesi ve ilgili resmi kurumların sunucularıyla <strong>her 5 dakikada bir</strong> haberleşir. Nöbetçi eczanelerden Wi-Fi noktalarına kadar tüm değişiklikler insan müdahalesi olmadan anında haritaya yansır.
                        </p>
                    </div>

                    <div class="mt-6 pt-5 border-t border-slate-100 flex flex-wrap items-center gap-2 sm:gap-3 text-xs font-bold text-slate-500">
                        <span class="px-3 py-1.5 bg-slate-100 rounded-lg text-slate-700"><i class="fa-solid fa-server mr-1.5 text-blue-500"></i>REST API</span>
                        <span class="px-3 py-1.5 bg-slate-100 rounded-lg text-slate-700"><i class="fa-solid fa-file-code mr-1.5 text-emerald-500"></i>GeoJSON</span>
                        <span class="px-3 py-1.5 bg-slate-100 rounded-lg text-slate-700"><i class="fa-solid fa-bolt mr-1.5 text-amber-500"></i>Önbellekleme (Cache)</span>
                    </div>
                </div>

                <div class="md:col-span-5 group relative bg-slate-900 text-white rounded-[1.75rem] sm:rounded-[2rem] p-6 sm:p-8 md:p-10 shadow-xl border border-slate-800 transition-all duration-500 overflow-hidden flex flex-col justify-between">
                    <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-purple-600/20 rounded-full blur-3xl group-hover:bg-purple-500/30 transition-all duration-700 pointer-events-none"></div>
                    
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

                    <div class="mt-6 bg-slate-800/80 border border-slate-700/80 rounded-2xl p-3.5 sm:p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-3 w-3 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                            </span>
                            <span class="text-xs font-bold text-slate-200">Sismik Sensör Ağı Aktif</span>
                        </div>
                        <a href="portal.php?hizmet=deprem" class="text-xs font-bold text-purple-400 hover:text-purple-300 transition whitespace-nowrap">Haritada Gör <i class="fa-solid fa-arrow-right ml-1"></i></a>
                    </div>
                </div>

                <div class="md:col-span-3 group bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-sm hover:shadow-xl border border-slate-200/80 transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center gap-3.5 mb-3">
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <h4 class="text-base sm:text-lg font-extrabold text-slate-900 leading-snug">Vektörel Harita Motoru</h4>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Leaflet.js ve Canvas tabanlı harita mimarisi, binlerce lokasyon noktasını sıfır gecikme ile işler.
                    </p>
                </div>

                <div class="md:col-span-3 group bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-sm hover:shadow-xl border border-slate-200/80 transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center gap-3.5 mb-3">
                        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                            <i class="fa-solid fa-magnifying-glass-location"></i>
                        </div>
                        <h4 class="text-base sm:text-lg font-extrabold text-slate-900 leading-snug">Akıllı Geocoding</h4>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Gelişmiş adres ve mekan çözümleme algoritması ile aradığınız sokağı, eczaneyi veya tarihi yapıyı saniyeler içinde bulur.
                    </p>
                </div>

                <div class="md:col-span-3 group bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-sm hover:shadow-xl border border-slate-200/80 transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center gap-3.5 mb-3">
                        <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-rose-600 group-hover:text-white transition-colors">
                            <i class="fa-solid fa-filter"></i>
                        </div>
                        <h4 class="text-base sm:text-lg font-extrabold text-slate-900 leading-snug">Dinamik Filtreleme</h4>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Sayfa yenilemeye gerek kalmadan kategoriler arasında anlık geçiş yapın; Türkçe karakter duyarsız arama ile sonuçları daraltın.
                    </p>
                </div>

                <div class="md:col-span-3 group bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-sm hover:shadow-xl border border-slate-200/80 transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center gap-3.5 mb-3">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <i class="fa-solid fa-route"></i>
                        </div>
                        <h4 class="text-base sm:text-lg font-extrabold text-slate-900 leading-snug">Tek Tıkla Navigasyon</h4>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Seçtiğiniz herhangi bir hizmet noktasına Google Haritalar entegrasyonu sayesinde bulunduğunuz konumdan anında yol tarifi alın.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <section class="py-24 bg-[#0b1120] text-white relative z-20 overflow-hidden border-t border-slate-800">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/15 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-600/15 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-14 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold uppercase tracking-widest mb-3">
                    <i class="fa-solid fa-compass"></i> Şehir Rehberi
                </div>
                <h2 class="text-3xl md:text-5xl font-black tracking-tight text-white">TÜM HİZMETLER</h2>
            </div>
            <p class="text-slate-400 max-w-md text-sm md:text-base">
                İhtiyacınız olan kategoriyi seçerek doğrudan interaktif portal haritası üzerinde incelemeye başlayın.
            </p>
        </div>
        
        <div class="w-full overflow-hidden mb-5 relative marquee-mask">
            <div class="marquee-track-left">
                <?php for($i=0; $i<4; $i++): ?>
                <a href="portal.php?hizmet=deprem" class="marquee-item group block">
                    <div class="bg-slate-800/60 hover:bg-slate-800 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-purple-500/60 transition-all duration-300 flex items-center gap-4">
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
                    <div class="bg-slate-800/60 hover:bg-slate-800 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-emerald-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white p-1.5 flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <img src="assets/icons/afad.png" class="w-full h-full object-contain">
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider block">Güvenli Bölge</span>
                            <h3 class="font-bold text-white text-sm truncate">Toplanma Alanları</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=gezilecek_yerler" class="marquee-item group block">
                    <div class="bg-slate-800/60 hover:bg-slate-800 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-rose-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md text-lg group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-rose-400 uppercase tracking-wider block">Kültür & Turizm</span>
                            <h3 class="font-bold text-white text-sm truncate">Tarihi Yerler</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=otoparklar" class="marquee-item group block">
                    <div class="bg-slate-800/60 hover:bg-slate-800 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-slate-400/60 transition-all duration-300 flex items-center gap-4">
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
                    <div class="bg-slate-800/60 hover:bg-slate-800 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-sky-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-sky-500 text-white flex items-center justify-center shrink-0 shadow-md text-lg group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-basket-shopping"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-sky-400 uppercase tracking-wider block">Sosyal Yaşam</span>
                            <h3 class="font-bold text-white text-sm truncate">Semt Pazarları</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=nobetci_eczane" class="marquee-item group block">
                    <div class="bg-slate-800/60 hover:bg-slate-800 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-red-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white p-2 flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <img src="assets/icons/eczane.png" class="w-full h-full object-contain">
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-red-400 uppercase tracking-wider block">Sağlık</span>
                            <h3 class="font-bold text-white text-sm truncate">Nöbetçi Eczaneler</h3>
                        </div>
                    </div>
                </a>
                <?php endfor; ?>
            </div>
        </div>

        <div class="w-full overflow-hidden relative marquee-mask">
            <div class="marquee-track-right">
                <?php for($i=0; $i<4; $i++): ?>
                <a href="portal.php?hizmet=wifi_noktalari" class="marquee-item group block">
                    <div class="bg-slate-800/60 hover:bg-slate-800 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-green-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-green-500 text-white flex items-center justify-center shrink-0 shadow-md text-lg group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-wifi"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-green-400 uppercase tracking-wider block">İnternet</span>
                            <h3 class="font-bold text-white text-sm truncate">Ücretsiz Wi-Fi</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=kaybis" class="marquee-item group block">
                    <div class="bg-slate-800/60 hover:bg-slate-800 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-blue-500/60 transition-all duration-300 flex items-center gap-4">
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
                    <div class="bg-slate-800/60 hover:bg-slate-800 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-cyan-500/60 transition-all duration-300 flex items-center gap-4">
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
                    <div class="bg-slate-800/60 hover:bg-slate-800 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-slate-400/60 transition-all duration-300 flex items-center gap-4">
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
                    <div class="bg-slate-800/60 hover:bg-slate-800 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-amber-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md text-lg group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-charging-station"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider block">E-Mobilite</span>
                            <h3 class="font-bold text-white text-sm truncate">Şarj Noktaları</h3>
                        </div>
                    </div>
                </a>
                <a href="portal.php?hizmet=spor_tesisleri" class="marquee-item group block">
                    <div class="bg-slate-800/60 hover:bg-slate-800 backdrop-blur-md rounded-2xl p-4 border border-slate-700/70 hover:border-indigo-500/60 transition-all duration-300 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-indigo-500 text-white flex items-center justify-center shrink-0 shadow-md text-lg group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-volleyball"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider block">Yaşam & Spor</span>
                            <h3 class="font-bold text-white text-sm truncate">Spor Tesisleri</h3>
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
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-5">
                <a href="https://acikveri.kayseri.bel.tr/" target="_blank" class="group flex flex-col items-center justify-center p-6 bg-slate-50 rounded-2xl border border-slate-200/80 hover:border-blue-500 hover:bg-white hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="h-14 flex items-center justify-center mb-3">
                        <img src="assets/icons/veri-platformu.png" alt="Kayseri BB" class="max-h-full object-contain group-hover:scale-105 transition-transform">
                    </div>
                    <span class="font-bold text-xs text-slate-700 group-hover:text-blue-600 text-center">Kayseri BB Açık Veri Portalı</span>
                </a>
                
                <a href="http://www.koeri.boun.edu.tr/" target="_blank" class="group flex flex-col items-center justify-center p-6 bg-slate-50 rounded-2xl border border-slate-200/80 hover:border-blue-500 hover:bg-white hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="h-14 flex items-center justify-center mb-3">
                        <img src="assets/icons/kandilli.jpg" onerror="this.src='assets/icons/kandilli.png'" alt="Kandilli" class="max-h-full object-contain rounded-lg group-hover:scale-105 transition-transform">
                    </div>
                    <span class="font-bold text-xs text-slate-700 group-hover:text-blue-600 text-center">Kandilli Rasathanesi API</span>
                </a>
                
                <a href="https://www.afad.gov.tr/" target="_blank" class="group flex flex-col items-center justify-center p-6 bg-slate-50 rounded-2xl border border-slate-200/80 hover:border-blue-500 hover:bg-white hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="h-14 flex items-center justify-center mb-3">
                        <img src="assets/icons/afad.png" alt="AFAD" class="max-h-full object-contain group-hover:scale-105 transition-transform">
                    </div>
                    <span class="font-bold text-xs text-slate-700 group-hover:text-blue-600 text-center">AFAD Toplanma Alanları</span>
                </a>

                <a href="https://ktb.gov.tr/" target="_blank" class="group flex flex-col items-center justify-center p-6 bg-slate-50 rounded-2xl border border-slate-200/80 hover:border-blue-500 hover:bg-white hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="h-14 flex items-center justify-center mb-3">
                        <img src="assets/icons/kültür.png" alt="Kültür Bakanlığı" class="max-h-full object-contain group-hover:scale-105 transition-transform">
                    </div>
                    <span class="font-bold text-xs text-slate-700 group-hover:text-blue-600 text-center">İl Kültür Turizm Müd.</span>
                </a>
                
                <a href="https://open-meteo.com/" target="_blank" class="group col-span-2 md:col-span-1 flex flex-col items-center justify-center p-6 bg-slate-50 rounded-2xl border border-slate-200/80 hover:border-blue-500 hover:bg-white hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="h-14 flex items-center justify-center mb-3">
                        <img src="assets/icons/open.png" alt="Open Meteo" class="max-h-full object-contain open-meteo-logo group-hover:scale-150 transition-transform">
                    </div>
                    <span class="font-bold text-xs text-slate-700 group-hover:text-blue-600 text-center">Open-Meteo Weather API</span>
                </a>
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
                        <span class="font-bold text-xl text-white tracking-tight">Kayseri - Şehir Portalı</span>
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
                        <li><a href="portal.php?hizmet=gezilecek_yerler" class="hover:text-white transition">Müze ve Tarihi Yerler</a></li>
                    </ul>
                </div>

                <div class="md:col-span-4">
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-4">Tam Ekran Deneyimi</h4>
                    <p class="text-sm text-slate-400 mb-4 leading-relaxed">
                        Gelişmiş arama, filtreleme ve liste görünümleri için tam ekran Şehir Portalı arayüzüne geçiş yapın.
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
    <script>
        let initialZoom = 12;
        let currentMinZoom = 11; 
        let isFlying = false;    
        let currentCategory = 'all'; 
        
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
        let effectTriggered = false;

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
                'BILECIK': 'BİLECİK', 'USAK': 'UŞAK', 'AYDIN': 'AYDIN', 'DENIZLI': 'DENİZLI',
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
                'KORFEZI': 'KÖRFEZİ', 'ADASI': 'ADASI', 'ADALARI': 'ADALARI'
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
            e.popup.update();
            setTimeout(() => { e.popup.update(); }, 50);
            let marker = e.popup._source;
            if (marker) {
                secimleriSifirla();
                setTimeout(() => {
                    if (marker.getElement()) {
                        let iconEl = marker.getElement().querySelector('.custom-marker-icon, .quake-marker-icon');
                        if (iconEl) {
                            iconEl.classList.remove('border-white', 'border-red-500', 'border-2');
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
                toastGoster('Canlı Yayın Kapalı', 'KVKK gereği Şehir Kameraları yayını durdurulmuştur, ancak konumları haritada gösterilmektedir.');
            }
            
            heroMarkers.eachLayer(function(marker) {
                let outerEl = marker.getElement();
                let innerEl = outerEl ? outerEl.querySelector('.hero-marker') : null;
                
                let shouldShow = (category === 'all' && marker.category !== 'kamera') || (marker.category === category);
                
                if (shouldShow) {
                    marker.isFilteredOut = false; 
                    if (outerEl && innerEl && effectTriggered) {
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
                let targetZoom = (category === 'deprem') ? 6 : initialZoom;
                let targetCenter = (category === 'deprem') ? L.latLng(39.2, 35.0) : L.latLng(38.7205, 35.4826);
                let currentCenter = heroMap.getCenter();
                
                let isSameLocation = (heroMap.getZoom() === targetZoom) && 
                                     (Math.abs(currentCenter.lat - targetCenter.lat) < 0.01) && 
                                     (Math.abs(currentCenter.lng - targetCenter.lng) < 0.01);
                                     
                if (!isSameLocation) {
                    heroMap.stop();
                    isFlying = true;
                    heroMap.flyTo(targetCenter, targetZoom, { duration: 1.2, easeLinearity: 0.25 });
                }
                
                if(category === 'deprem') {
                    currentMinZoom = 5;
                } else {
                    currentMinZoom = 11;
                }
            }
        }
        
        heroMap.on('moveend', function() {
            isFlying = false;
        });

        function addPortalMarker(lat, lng, rawIconHtml, popupContent, categoryClass) {
            let isVisible = effectTriggered && ((currentCategory === 'all' && categoryClass !== 'kamera') || categoryClass === currentCategory);
            let animState = isVisible ? 'scale-100 opacity-100' : 'scale-0 opacity-0';
            
            let iconHtml = rawIconHtml.replace('class="', `class="hero-marker marker-anim cat-${categoryClass} ${animState} `);
            let mIcon = L.divIcon({ html: iconHtml, className: 'hero-leaflet-marker', iconSize: [36,36], iconAnchor: [18,18] });
            let m = L.marker([lat, lng], {icon: mIcon}).addTo(heroMarkers);
            
            m.category = categoryClass;
            m.isFilteredOut = !isVisible;
            
            m.on('add', function() {
                let e = m.getElement();
                if(e) e.style.pointerEvents = (effectTriggered && !m.isFilteredOut) ? 'auto' : 'none';
            });

            if (popupContent) {
                m.bindPopup(popupContent, {className: 'minimal-popup', closeButton: false, offset: [0,-5], maxWidth: 280});
            }
        }

        function loadHeroData() {
            fetch('api/api_getir.php?endpoint=nobetci_eczane').then(r=>r.json()).then(data => {
                let eczaneler = Array.isArray(data) ? data : (data.features || []);
                eczaneler.forEach(eczane => {
                    let lat = parseFloat(eczane.latitude), lng = parseFloat(eczane.longitude);
                    if(!isNaN(lat) && !isNaN(lng)) {
                        let ad = toTitleCase(eczane.name || 'Eczane');
                        let tel = eczane.phone || 'Telefon yok';
                        let popupIcerik = `<div style="width: 240px;" class="p-3 w-64 box-border"><h3 class="font-bold text-[15px] text-red-600 border-b border-gray-100 pb-2 mb-2"><i class="fa-solid fa-staff-snake mr-1 text-red-400"></i> ${ad}</h3><div class="text-xs text-gray-600 mb-2"><p class="mb-1"><i class="fa-solid fa-location-dot w-4 text-gray-400"></i> ${toTitleCase(eczane.address || 'Adres yok')}</p><p><i class="fa-solid fa-phone w-4 text-gray-400"></i> ${tel}</p></div><a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-3 py-2 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                        addPortalMarker(lat, lng, '<div data-default-border="border-red-500" class="custom-marker-icon w-9 h-9 bg-white rounded-full flex items-center justify-center border-2 border-red-500 shadow-md"><img src="assets/icons/eczane.png" class="w-6 h-6 object-contain"></div>', popupIcerik, 'eczane');
                    }
                });
            }).catch(e => console.log(e));

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
                            
                            addPortalMarker(lat, lng, '<div class="custom-marker-icon w-8 h-8 bg-rose-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-building-columns text-sm"></i></div>', popupIcerik, 'tarihi');
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
                            let popupIcerik = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-shield-halved text-xl text-emerald-600"></i></div><h3 class="font-bold text-[14px] text-gray-800 leading-tight mb-2">${ad}</h3><span class="text-[10px] text-emerald-700 font-bold bg-emerald-100 px-2 py-1 rounded block mb-3">Kapasite: ${props.ALAN} m²</span><a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                            addPortalMarker(lat, lng, '<div class="custom-marker-icon w-8 h-8 bg-emerald-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-shield-halved text-sm"></i></div>', popupIcerik, 'afad');
                        }
                    });
                }
            }).catch(e => console.log(e));

            fetch('https://api.orhanaydogdu.com.tr/deprem/kandilli/live').then(r=>r.json()).then(data => {
                if(data && data.result) {
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
            
            currentMinZoom = 11; 
            
            const content = document.getElementById('hero-content');
            const overlay = document.getElementById('hero-overlay');
            const nav = document.getElementById('main-nav');
            const filterMenu = document.getElementById('hero-filter-menu');
            const heroWrapper = document.getElementById('hero-wrapper');
            const heroMapEl = document.getElementById('hero-map');
            
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
            
            setTimeout(() => {
                if(effectTriggered) {
                    filterMenu.classList.remove('opacity-0', '-translate-y-full', 'pointer-events-none');
                    filterMenu.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');
                    
                    filterHeroMap(currentCategory, false);
                }
            }, 2000);
        }

        function restore3DEffect() {
            if(!effectTriggered) return;
            effectTriggered = false;

            const content = document.getElementById('hero-content');
            const overlay = document.getElementById('hero-overlay');
            const nav = document.getElementById('main-nav');
            const filterMenu = document.getElementById('hero-filter-menu');
            const heroWrapper = document.getElementById('hero-wrapper');
            const heroMapEl = document.getElementById('hero-map');

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
            currentMinZoom = 11; 
            
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

        heroMap.on('dblclick', trigger3DEffect);
        heroMap.on('zoomstart', function() {
            if (!effectTriggered && heroMap.getZoom() >= initialZoom) {
                trigger3DEffect();
            }
        });
    </script>
</body>
</html>