var map = L.map('map', {zoomControl: false, preferCanvas: true}).setView([38.7205, 35.4826], 12);
L.control.zoom({position: 'bottomright'}).addTo(map);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
}).addTo(map);

setTimeout(() => { map.invalidateSize(); }, 100);
window.addEventListener('resize', () => { map.invalidateSize(); });

var markerGroup = L.layerGroup().addTo(map);
var aramaMarker = null;
var currentRequestId = 0;

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

function animasyonluMarkerEkle(lat, lng, iconHtml, customId, popupIcerik) {
    let animHtml = iconHtml.replace('class="', 'class="marker-anim scale-0 opacity-0 ');
    let animIcon = L.divIcon({ html: animHtml, className: '', iconSize: [36, 36], iconAnchor: [18, 18] });
    let marker = L.marker([lat, lng], {icon: animIcon, customId: customId});
    if (popupIcerik) marker.bindPopup(popupIcerik);
    marker.addTo(markerGroup);

    requestAnimationFrame(() => {
        setTimeout(() => {
            let el = marker.getElement();
            if (el) {
                let inner = el.querySelector('.marker-anim');
                if (inner) {
                    inner.classList.remove('scale-0', 'opacity-0');
                    inner.classList.add('scale-100', 'opacity-100');
                }
            }
        }, 30);
    });
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
        setTimeout(bitir, 1150);
        map.flyTo(hedefKoordinat, hedefZoom, { duration: 1.0, easeLinearity: 0.25 });
    } else {
        map.flyTo(hedefKoordinat, hedefZoom, { duration: 0.6 });
        callback();
    }
}

function kaynakGuncelle(endpoint) {
    let kaynak = "";
    if(endpoint === 'tumu') kaynak = "Tüm Açık Veri Kaynakları";
    else if(endpoint === 'kaybis' || endpoint === 'kaski_su_kesinti' || endpoint === 'sarj_istasyonlari' || endpoint === 'spor_tesisleri') kaynak = "Veri Sağlanamıyor";
    else if(endpoint === 'nobetci_eczane' || endpoint === 'wifi_noktalari') kaynak = "Açık Veri Portalı";
    else if(endpoint === 'kameralar') kaynak = "Erciyes A.Ş. & Kayseri BB";
    else if(endpoint === 'deprem') kaynak = "Kandilli Rasathanesi API";
    else if(endpoint === 'toplanma_alanlari') kaynak = "AFAD & Açık Veri Portalı";
    else if(endpoint === 'gezilecek_yerler') kaynak = "İl Kültür Turizm Müd.";
    else if(endpoint === 'pazar_yerleri' || endpoint === 'otoparklar') kaynak = "Kayseri BB Açık Veri";
    
    document.getElementById('active-source').innerText = "Harita: " + kaynak;

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
        icon.classList.add('border-white', 'border-2');
    });
    document.querySelectorAll('.list-item').forEach(item => {
        item.classList.remove('bg-yellow-100', 'border-l-4', 'border-yellow-500');
        item.classList.add('border-b', 'border-gray-200', 'bg-white');
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
        setTimeout(() => { div.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 50);
    }
    markerGroup.eachLayer(function(marker) {
        let latlng = marker.getLatLng();
        if(latlng.lat === markerLat && latlng.lng === markerLng) {
            map.flyTo([markerLat, markerLng], zoomLvl, { duration: 0.9 });
            setTimeout(() => { marker.openPopup(); }, 200);
            setTimeout(() => {
                if (marker.getElement()) {
                    let iconEl = marker.getElement().querySelector('.custom-marker-icon, .quake-marker-icon');
                    if (iconEl) {
                        iconEl.classList.remove('border-white', 'border-2');
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
                    iconEl.classList.remove('border-white', 'border-2');
                    iconEl.classList.add('border-yellow-500', 'border-4', 'scale-125', 'z-50');
                }
            }
        }, 100);
        let div = document.getElementById(marker.options.customId);
        if (div) {
            div.classList.remove('border-b', 'border-gray-200', 'bg-white', 'border-gray-100'); 
            div.classList.add('bg-yellow-100', 'border-l-4', 'border-yellow-500');
            div.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
    aramaMarker.bindPopup(popupIcerik).openPopup();
    map.flyTo([lat, lon], 15, { duration: 1.0 });
}

function kapatTumPaneller() {
    ['quake-panel', 'afet-panel', 'muze-panel', 'otopark-panel', 'pazar-panel', 'eczane-panel', 'wifi-panel', 'kamera-panel'].forEach(id => {
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

function tumHizmetleriGoster(reqId) {
    akiciGecisYap([38.7205, 35.4826], 11, () => {
        if (reqId !== currentRequestId) return;

        fetch('api/api_getir.php?endpoint=nobetci_eczane').then(r=>r.json()).then(data => {
            if (reqId !== currentRequestId) return;
            let eczaneler = Array.isArray(data) ? data : (data.features || []);
            eczaneler.forEach((eczane, index) => {
                let lat = parseFloat(eczane.latitude), lng = parseFloat(eczane.longitude);
                if(!isNaN(lat) && !isNaN(lng)) {
                    let popup = `<div style="width: 240px;" class="p-3 w-64 box-border"><h3 class="font-bold text-[15px] text-red-600 border-b border-gray-100 pb-2 mb-2"><i class="fa-solid fa-staff-snake mr-1 text-red-400"></i> ${toTitleCase(eczane.name || 'Eczane')}</h3><div class="text-xs text-gray-600 mb-2"><p class="mb-1"><i class="fa-solid fa-location-dot w-4 text-gray-400"></i> ${toTitleCase(eczane.address || '')}</p><p><i class="fa-solid fa-phone w-4 text-gray-400"></i> ${eczane.phone || 'Telefon yok'}</p></div><a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white text-center px-3 py-2 rounded-md text-xs font-bold hover:bg-blue-700 mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                    animasyonluMarkerEkle(lat, lng, '<div class="custom-marker-icon w-9 h-9 bg-white rounded-full flex items-center justify-center border-2 border-red-500 shadow-md"><img src="assets/icons/eczane.png" class="w-6 h-6 object-contain"></div>', 'all_ecz_' + index, popup);
                }
            });
        }).catch(e=>{});

        fetch('api/api_getir.php?endpoint=wifi_noktalari').then(r=>r.json()).then(data => {
            if (reqId !== currentRequestId) return;
            if(data && data.features) {
                data.features.forEach((item, index) => {
                    if (item.geometry && item.geometry.coordinates) {
                        let c = item.geometry.coordinates;
                        let popup = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><h3 class="font-bold text-[14px] text-gray-800 mb-1">${toTitleCase(item.properties?.ADI || 'Wi-Fi')}</h3><span class="text-[10px] bg-green-500 text-white px-2 py-0.5 rounded-full font-bold">ÜCRETSİZ Wİ-Fİ</span><a href="https://www.google.com/maps/dir/?api=1&destination=${c[1]},${c[0]}" target="_blank" class="block w-full bg-blue-600 !text-white text-center px-2 py-1.5 rounded-md text-xs font-bold mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
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
                        let popup = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><h3 class="font-bold text-[13px] text-gray-800 mb-3">${toTitleCase(item.properties?.ADI || 'Otopark')}</h3><a href="https://www.google.com/maps/dir/?api=1&destination=${c[1]},${c[0]}" target="_blank" class="block w-full bg-blue-600 !text-white text-center px-2 py-1.5 rounded-md text-xs font-bold shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                        animasyonluMarkerEkle(c[1], c[0], '<div class="custom-marker-icon w-8 h-8 bg-gray-800 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md font-bold">P</div>', 'all_park_' + index, popup);
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
                        let popup = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><h3 class="font-bold text-[14px] text-gray-800 mb-1">${toTitleCase(item.properties?.ADI || 'Pazar Yeri')}</h3><span class="text-[11px] bg-sky-500 text-white px-2 py-1 rounded-full font-bold block my-1">${toTitleCase(item.properties?.PAZARGUNAD || 'Belirsiz')}</span><a href="https://www.google.com/maps/dir/?api=1&destination=${c[1]},${c[0]}" target="_blank" class="block w-full bg-blue-600 !text-white text-center px-2 py-1.5 rounded-md text-xs font-bold mt-2 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
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
                        let popup = `<div style="width: 220px;" class="p-2 text-center box-border"><h3 class="font-bold text-[14px] text-rose-700 mb-2">${duzenleMetin(toTitleCase(item.title))}</h3><a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white text-center px-2 py-1.5 rounded text-xs font-bold mt-2 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                        animasyonluMarkerEkle(lat, lng, '<div class="custom-marker-icon w-8 h-8 bg-rose-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-building-columns text-sm"></i></div>', 'all_muze_' + index, popup);
                    }
                });
            }
        }).catch(e=>{});

        fetch('assets/data/toplanma-alanlari_alan-3829284.geojson.txt').then(r=>r.json()).then(data => {
            if (reqId !== currentRequestId) return;
            if(data && data.features) {
                data.features.slice(0, 50).forEach((item, index) => {
                    let p = item.properties || {}, lat = parseFloat(p.ENLEM), lng = parseFloat(p.BOYLAM);
                    if(!isNaN(lat) && !isNaN(lng)) {
                        let popup = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><h3 class="font-bold text-[14px] text-gray-800 mb-2">${toTitleCase(p.ADI)}</h3><span class="text-[10px] text-emerald-700 font-bold bg-emerald-100 px-2 py-1 rounded block mb-2">Kapasite: ${p.ALAN} m²</span><a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white text-center px-2 py-1.5 rounded-md text-xs font-bold mt-2 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                        animasyonluMarkerEkle(lat, lng, '<div class="custom-marker-icon w-8 h-8 bg-emerald-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-shield-halved text-sm"></i></div>', 'all_afet_' + index, popup);
                    }
                });
            }
        }).catch(e=>{});

        fetch('https://api.orhanaydogdu.com.tr/deprem/kandilli/live').then(r=>r.json()).then(data => {
            if (reqId !== currentRequestId) return;
            if(data && data.result) {
                data.result.slice(0, 40).forEach((deprem, index) => {
                    let c = deprem.geojson.coordinates, mag = parseFloat(deprem.mag).toFixed(1);
                    let popup = `<div style="width: 190px;" class="p-2 w-48 text-center box-border"><h3 class="font-bold text-[14px] text-purple-700 mb-1">${toTitleCase(deprem.title)}</h3><p class="text-[11px] text-gray-600">Şiddet: <b class="text-purple-600">${mag}</b> | Derinlik: <b>${deprem.depth} km</b></p></div>`;
                    animasyonluMarkerEkle(c[1], c[0], `<div class="quake-marker-icon w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md font-bold text-sm">${mag}</div>`, 'all_dep_' + index, popup);
                });
            }
        }).catch(e=>{});
    });
}

function veriCekVeGoster(endpoint) {
    currentRequestId++;
    const reqId = currentRequestId;

    markerGroup.clearLayers();
    if(aramaMarker) map.removeLayer(aramaMarker);
    kapatTumPaneller();
    kaynakGuncelle(endpoint);

    if (endpoint === 'tumu') {
        tumHizmetleriGoster(reqId);
        return;
    }

    if (endpoint === 'kaybis' || endpoint === 'kaski_su_kesinti' || endpoint === 'sarj_istasyonlari' || endpoint === 'spor_tesisleri') {
        toastGoster('Veri Sağlanamıyor', 'İlgili kurum tarafından bu hizmet için aktif API bağlantısı sunulmamaktadır.');
        return; 
    }

    let url = '';
    let listId = '';

    if (endpoint === 'deprem') {
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
                div.className = "list-item p-4 border-b border-gray-200 hover:bg-slate-50 cursor-pointer transition-all duration-300 bg-white";
                div.innerHTML = `<div class="flex items-center gap-3"><div class="w-10 h-10 bg-slate-100 rounded flex items-center justify-center shrink-0"><i class="fa-solid fa-video-slash text-slate-600 text-lg"></i></div><div><h3 class="text-sm font-bold text-gray-800 leading-tight">${k.ad}</h3><span class="text-[10px] text-red-500 mt-1 block font-medium">Yayın Kapalı</span></div></div>`;
                list.appendChild(div);
                
                div.onclick = () => { window.aktifListeOgesi(k.id, k.lat, k.lng, 15); };
            });
        });
        return;
    }

    if (!url) return;

    let veriPromise = fetch(url).then(response => response.json());

    akiciGecisYap(hedefKoordinat, hedefZoom, () => {
        if (reqId !== currentRequestId) return;

        veriPromise.then(data => {
            if (reqId !== currentRequestId) return;

            let listDOM = listId ? document.getElementById(listId) : null;
            if(listDOM) listDOM.innerHTML = '';
            let fragment = document.createDocumentFragment();
            
            if(endpoint === 'deprem') {
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
                            div.className = "list-item p-4 border-b border-gray-100 hover:bg-purple-50 cursor-pointer transition-all duration-300 bg-white";
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
                        
                        let popupIcerik = `<div style="width: 240px;" class="p-3 w-64 box-border"><h3 class="font-bold text-[15px] text-red-600 border-b border-gray-100 pb-2 mb-2"><i class="fa-solid fa-staff-snake mr-1 text-red-400"></i> ${ad}</h3><div class="text-xs text-gray-600 mb-2"><p class="mb-1"><i class="fa-solid fa-location-dot w-4 text-gray-400"></i> ${toTitleCase(eczane.address || 'Adres yok')}</p><p><i class="fa-solid fa-phone w-4 text-gray-400"></i> ${tel}</p></div><a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-3 py-2 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                        
                        animasyonluMarkerEkle(lat, lng, '<div class="custom-marker-icon w-9 h-9 bg-white rounded-full flex items-center justify-center border-2 border-red-500 shadow-md"><img src="assets/icons/eczane.png" class="w-6 h-6 object-contain"></div>', elementId, popupIcerik);
                        
                        if(listDOM) {
                            let div = document.createElement('div');
                            div.id = elementId;
                            div.className = "list-item p-4 border-b border-gray-200 hover:bg-red-50 cursor-pointer transition-all duration-300 bg-white";
                            div.innerHTML = `<div class="flex items-center gap-3"><div class="w-10 h-10 bg-red-100 rounded flex items-center justify-center shrink-0"><i class="fa-solid fa-staff-snake text-red-600 text-lg"></i></div><div><h3 class="text-sm font-bold text-gray-800 leading-tight">${ad}</h3><span class="text-[10px] text-gray-500 mt-1 block"><i class="fa-solid fa-phone mr-1"></i>${tel}</span></div></div>`;
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
                            
                            let popupIcerik = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-wifi text-xl text-green-600"></i></div><h3 class="font-bold text-[14px] text-gray-800 leading-tight mb-1">${ad}</h3><span class="text-[10px] bg-green-500 text-white px-2 py-0.5 rounded-full font-bold">KBB ÜCRETSİZ İNTERNET</span><a href="https://www.google.com/maps/dir/?api=1&destination=${coords[1]},${coords[0]}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                            
                            animasyonluMarkerEkle(coords[1], coords[0], '<div class="custom-marker-icon w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-wifi text-sm"></i></div>', elementId, popupIcerik);
                            
                            if(listDOM) {
                                let div = document.createElement('div');
                                div.id = elementId;
                                div.className = "list-item p-4 border-b border-gray-200 hover:bg-green-50 cursor-pointer transition-all duration-300 bg-white";
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
                            
                            let popupIcerik = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-shield-halved text-xl text-emerald-600"></i></div><h3 class="font-bold text-[14px] text-gray-800 leading-tight mb-2">${ad}</h3><span class="text-[10px] text-emerald-700 font-bold bg-emerald-100 px-2 py-1 rounded block mb-3">Kapasite: ${props.ALAN} m²</span><a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors mt-3 shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;

                            animasyonluMarkerEkle(lat, lng, '<div class="custom-marker-icon w-8 h-8 bg-emerald-600 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md"><i class="fa-solid fa-shield-halved text-sm"></i></div>', elementId, popupIcerik);
                            
                            if(listDOM) {
                                let div = document.createElement('div');
                                div.id = elementId;
                                div.className = "list-item p-4 border-b border-gray-100 hover:bg-emerald-50 cursor-pointer transition-all duration-300 bg-white";
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
                                div.className = "list-item p-4 border-b border-gray-200 hover:bg-rose-50 cursor-pointer transition-all duration-300 bg-white";
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
                                div.className = "list-item p-4 border-b border-gray-200 hover:bg-sky-50 cursor-pointer transition-all duration-300 bg-white";
                                div.innerHTML = `<div class="flex items-center gap-3"><div class="w-10 h-10 bg-sky-100 rounded flex items-center justify-center shrink-0"><i class="fa-solid fa-basket-shopping text-sky-600 text-lg"></i></div><div><h3 class="text-sm font-bold text-gray-800 leading-tight">${ad}</h3><span class="text-xs text-sky-600 font-medium mt-1 block"><i class="fa-regular fa-calendar-days mr-1"></i>${gun}</span></div></div>`;
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

                            let popupIcerik = `<div style="width: 190px;" class="p-3 w-48 text-center box-border"><div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fa-solid fa-square-parking text-2xl text-gray-800"></i></div><h3 class="font-bold text-[13px] text-gray-800 leading-tight mb-3">${ad}</h3><a href="https://www.google.com/maps/dir/?api=1&destination=${coords[1]},${coords[0]}" target="_blank" class="block w-full bg-blue-600 !text-white border border-blue-700 text-center px-2 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 hover:!text-white transition-colors shadow-md"><i class="fa-solid fa-location-arrow mr-1"></i> Yol Tarifi Al</a></div>`;
                            
                            animasyonluMarkerEkle(coords[1], coords[0], '<div class="custom-marker-icon w-8 h-8 bg-gray-800 text-white rounded-full flex items-center justify-center border-2 border-white shadow-md font-bold font-sans">P</div>', elementId, popupIcerik);
                            
                            if(listDOM) {
                                let div = document.createElement('div');
                                div.id = elementId;
                                div.className = "list-item p-4 border-b border-gray-200 hover:bg-gray-100 cursor-pointer transition-all duration-300 bg-white";
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

if (hedefHizmet) {
    if (hedefHizmet === 'kaybis' || hedefHizmet === 'kaski_su_kesinti' || hedefHizmet === 'kameralar' || hedefHizmet === 'sarj_istasyonlari' || hedefHizmet === 'spor_tesisleri') {
        gosterKullanilamayanHizmetler();
    }
    veriCekVeGoster(hedefHizmet);
} else {
    veriCekVeGoster('tumu');
}

havaDurumuCek();