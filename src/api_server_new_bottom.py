# ─── Uçak Kontrol Kategorileri (Genişletilmiş) ────────────────────────────────
INSPECTION_CATEGORIES = [
    {
        "id": "govde", "name": "Gövde Bütünlüğü", "zone": "FUSELAGE",
        "items": [
            {"issue": "Gövde yüzeyinde çizik ve çarpma izi", "action": "P2 zımparalama + epoksi dolgu, primer + topcoat boya ile kapat", "priority": "yüksek", "time_h": 4},
            {"issue": "Panel perçinlerinde gevşeme", "action": "AN470 serisi yeni perçin çak, tork değerini kontrol et", "priority": "kritik", "time_h": 6},
            {"issue": "Kompozit yüzeyde derin çatlak", "action": "NDT ultrasonik muayene; karbon fiber yama ile lamine tamir", "priority": "kritik", "time_h": 14},
            {"issue": "Kapı contalarında yıpranma", "action": "Conta setini tamamen değiştir (Mil-P-25732 spec.)", "priority": "orta", "time_h": 2},
            {"issue": "Gövde alt kısmında göçük izleri", "action": "Strüktürel mühendis değerlendirmesi, gerekirse panel değişimi", "priority": "kritik", "time_h": 10},
            {"issue": "Fümaj kapağında çarpma izi", "action": "Çekme + şekillendirme, renk uyumu için bölgesel boya", "priority": "orta", "time_h": 3},
            {"issue": "Gövde-kanat birleşim noktasında boşluk", "action": "Tork kontrol + conta yenileme, sealant uygulaması", "priority": "yüksek", "time_h": 8},
            {"issue": "Cargo kapısı kilitleme sistemi arızası", "action": "Kilit mekanizmasını söküp revize et, gerekirse komple değiştir", "priority": "kritik", "time_h": 7},
        ],
    },
    {
        "id": "kanat", "name": "Kanat & Kontrol Yüzeyleri", "zone": "WING",
        "items": [
            {"issue": "Kanat yüzeyinde boya soyulması ve oksidasyon", "action": "Alodine kaplama + iki kat topcoat boya", "priority": "orta", "time_h": 5},
            {"issue": "Flap menteşesinde boşluk ve aşınma", "action": "AN4 pim + MS21042 somun değişimi, hareket serbestliği kontrolü", "priority": "kritik", "time_h": 8},
            {"issue": "Winglet dip noktasında yorulma çatlağı", "action": "X-ray muayenesi; OEM onaylı yama kiti uygula", "priority": "kritik", "time_h": 16},
            {"issue": "Kanat ön kenarında (leading edge) göçük", "action": "Çektirme aleti ile şekle getir, primer + boya", "priority": "yüksek", "time_h": 7},
            {"issue": "Aileron kontrol kablosu gerilim kaybı", "action": "Kablo gerilimini ölç, turnbuckle ile ayarla veya kablo değiştir", "priority": "kritik", "time_h": 5},
            {"issue": "Kanat yakıt tankı sızıntısı", "action": "Sızdırmazlık testi yap, PR1422 fuel sealant ile tamir", "priority": "kritik", "time_h": 12},
            {"issue": "Spoiler actuator referans kaybı", "action": "Actuator kalibrasyonu, potansiyometre kontrolü", "priority": "yüksek", "time_h": 4},
            {"issue": "Slat ray aşınması", "action": "Ray sistemini incele, gerekirse yenile ve yağla", "priority": "yüksek", "time_h": 6},
        ],
    },
    {
        "id": "inis_takimi", "name": "İniş Takımı Sistemi", "zone": "LANDING_GEAR",
        "items": [
            {"issue": "Lastik aşınma sınırı aşılmış", "action": "Yeni lastik takılacak, fren diski incelenecek", "priority": "kritik", "time_h": 3},
            {"issue": "Amortisör yağ sızıntısı", "action": "O-ring ve yağ keçesi değişimi, basınç testi", "priority": "kritik", "time_h": 5},
            {"issue": "İniş takımı kilitleme mekanizması yorulması", "action": "Kilit pimlerini ve yay gerilimini kontrol et", "priority": "yüksek", "time_h": 4},
            {"issue": "Fren sisteminde hava kabarcığı", "action": "Fren sistemini kanama et (bleeding)", "priority": "yüksek", "time_h": 2},
            {"issue": "Döner eksende çatlak oluşumu", "action": "Manyetik parçacık muayenesi; aksın değiştirilmesi", "priority": "kritik", "time_h": 9},
            {"issue": "Yönlendirme (steering) sistemi sızdırıyor", "action": "Hidrolik hattı ve silindir keçelerini değiştir", "priority": "yüksek", "time_h": 6},
            {"issue": "İniş takımı kapak menteşesi korozyonu", "action": "Menteşeyi sök, kum sineği ile temizle, kaplama yaptır", "priority": "orta", "time_h": 4},
            {"issue": "Anti-skid sensörü arızalı", "action": "Sensör bloğunu değiştir, fren kontrol ünitesini sıfırla", "priority": "yüksek", "time_h": 3},
        ],
    },
    {
        "id": "motor", "name": "Motor & İtki Sistemi", "zone": "ENGINE",
        "items": [
            {"issue": "Kompresör kanatçığında FOD hasarı", "action": "Borescope muayenesi; gerekirse stage değişimi", "priority": "kritik", "time_h": 20},
            {"issue": "Motor yağ seviyesi düşük ve kirli yağ", "action": "Yağ filtresi değişimi, SOAP analizi", "priority": "yüksek", "time_h": 2},
            {"issue": "Egzoz konisinde çatlak ve renk değişimi", "action": "Isıl görüntüleme ile sıcak nokta tespiti", "priority": "kritik", "time_h": 10},
            {"issue": "Yakıt manifold basınç düşüklüğü", "action": "Yakıt filtresi değişimi, manifold basınç ölçümü", "priority": "yüksek", "time_h": 3},
            {"issue": "Fan blade (ön pervanede) çentik", "action": "Blade profil ölçümü; limit dışıysa değişim", "priority": "kritik", "time_h": 8},
            {"issue": "EGT (egzoz gaz sıcaklığı) limit aşımı", "action": "Trimming testi yap, hot section muayenesi", "priority": "kritik", "time_h": 15},
            {"issue": "Motor montaj braketinde çatlak", "action": "MT muayenesi; strüktürel onay alınmadan uçuş yasak", "priority": "kritik", "time_h": 12},
            {"issue": "N2 rotor titreşim sapması", "action": "Balancing prosedürü uygula, rulman kontrolü", "priority": "yüksek", "time_h": 5},
        ],
    },
    {
        "id": "avionik", "name": "Avionik & Elektrik Sistemleri", "zone": "AVIONICS",
        "items": [
            {"issue": "Pitot tüpü kısmi tıkanıklığı", "action": "Pitot ısıtıcıyı test et, IAS gösterge kalibrasyonu", "priority": "kritik", "time_h": 2},
            {"issue": "Navigasyon ışığı arızası", "action": "LED modülü değişimi, sigorta kontrolü", "priority": "orta", "time_h": 1},
            {"issue": "Otopilot servo geri bildirimi zayıf", "action": "Servo kalibrasyonu, uçuş kontrol bilgisayarı reset", "priority": "yüksek", "time_h": 4},
            {"issue": "VHF anten empedans uyumsuzluğu", "action": "Anten konnektör korozyonunu temizle, empedansı ölç", "priority": "orta", "time_h": 2},
            {"issue": "ADS-B transponder hata kodu", "action": "TSO-C166b uyumluluğu kontrol et, yazılım güncelleme", "priority": "yüksek", "time_h": 3},
            {"issue": "FDR/CVR güç arızası", "action": "Akü ve bağlantı kontrolü; gerekirse kart değişimi", "priority": "kritik", "time_h": 4},
            {"issue": "HF haberleşme sisteminde parazit", "action": "Anten besleyici ve koaksiyel kabloyu incele", "priority": "orta", "time_h": 2},
            {"issue": "Yağmur sensörü hatalı okuma", "action": "Sensör bağlantısı ve pencere ısıtma devresi kontrolü", "priority": "düşük", "time_h": 1},
        ],
    },
    {
        "id": "yapi_boya", "name": "Yapısal Boya & Korozyon", "zone": "PAINT",
        "items": [
            {"issue": "Gövde altında korozyon başlangıcı (pitting)", "action": "Alodine 1201 ile işle, epoksi primer + renk katmanı", "priority": "yüksek", "time_h": 8},
            {"issue": "UV hasarından boya renk solması", "action": "Polishing + ince topcoat katmanı ekle", "priority": "düşük", "time_h": 6},
            {"issue": "Stringer civarında boya kabarcığı", "action": "Kabarcıklı alanı soy, nem bariyeri + yeniden boya", "priority": "yüksek", "time_h": 5},
            {"issue": "Zemin panelinde pas izleri", "action": "Paneli sök, kuru temizle, çinko fosfat koru", "priority": "orta", "time_h": 3},
            {"issue": "APU egzoz alanında ısı hasarı", "action": "Isıya dayanıklı boya (600°C) ile yeniden kaplama", "priority": "yüksek", "time_h": 7},
            {"issue": "Kanat kökünde galvanik korozyon", "action": "Titanyum kaplama, korozyon inhibitörü enjeksiyonu", "priority": "kritik", "time_h": 11},
            {"issue": "Kapı çerçevesinde sealant bozulması", "action": "Eski sealant sök, PR-1422 ile yeniden uygula", "priority": "orta", "time_h": 4},
            {"issue": "Radome yüzeyinde mikro çatlaklar", "action": "Honeycomb panel onarımı, su geçirmezlik testi", "priority": "yüksek", "time_h": 9},
        ],
    },
    {
        "id": "hidrolik", "name": "Hidrolik & Pnömatik Sistemler", "zone": "HYDRAULIC",
        "items": [
            {"issue": "Hidrolik sıvı renk değişimi (kirlenme)", "action": "Sistem kanamayı yap, yeni MIL-PRF-5606 sıvısı doldur", "priority": "yüksek", "time_h": 3},
            {"issue": "Fren akümülatör basınç düşüklüğü", "action": "Nitrojen şarjı yap, membran bütünlüğünü kontrol et", "priority": "yüksek", "time_h": 2},
            {"issue": "Pnömatik boru bağlantısında sızıntı", "action": "Sızdırıyor olan fittings değiştir, tork torquelama listesi uygula", "priority": "kritik", "time_h": 6},
            {"issue": "Hidrolik pompa Q-gürültüsü artışı", "action": "Pompa debi testi yap; gerekirse komple pompa değişimi", "priority": "yüksek", "time_h": 8},
            {"issue": "Bleed air sızdırmazlık arızası", "action": "Bleed duct joints kontrol et, yüksek basınç testi uygula", "priority": "kritik", "time_h": 7},
        ],
    },
    {
        "id": "yakit", "name": "Yakıt Sistemi", "zone": "FUEL",
        "items": [
            {"issue": "Yakıt tank iç yüzeyinde mikrobiyolojik büyüme", "action": "Biyosit ile tank temizliği, filtre değişimi", "priority": "yüksek", "time_h": 10},
            {"issue": "Yakıt pompa kavitasyon sesi", "action": "Pompa debi testi, inlet filtre kontrolü", "priority": "kritik", "time_h": 5},
            {"issue": "Çapraz besleme vanası kısmi açık kalıyor", "action": "Vana motoru ve konum geri bildirimini kontrol et", "priority": "yüksek", "time_h": 4},
            {"issue": "Yakıt miktarı ölçüm sensörü sapması", "action": "Kapasitif sensör kalibrasyonu yap", "priority": "orta", "time_h": 2},
            {"issue": "Dolum kapağı sızdırıyor", "action": "O-ring seti değiştir, kapak tork değerini doğrula", "priority": "orta", "time_h": 1},
        ],
    },
]

_PRIORITY_META = {
    "kritik" : (55, 95,  (20,  20, 220),  "KRİTİK"),
    "yüksek" : (28, 54,  (30, 120, 255),  "YÜKSEK"),
    "orta"   : (10, 27,  (0,  200, 220),  "ORTA"),
    "düşük"  : (2,   9,  (0,  200,  80),  "DÜŞÜK"),
}

_DAMAGE_LEVELS = [
    (90, 100, "KRİTİK HASAR",   "no_go"),
    (60,  89, "AĞIR HASAR",     "no_go"),
    (35,  59, "ORTA HASAR",     "conditional"),
    (15,  34, "HAFİF HASAR",    "limited"),
    ( 0,  14, "MİNİMAL HASAR",  "go"),
]


# ─── Çizim Fonksiyonları ─────────────────────────────────────────────────────

def _draw_corners(img: np.ndarray, x1, y1, x2, y2, color: tuple, length: int = 18, thick: int = 2):
    """Köşe L-şekli çizer. Kutunun içine hiçbir metin yazılmaz."""
    bright = tuple(min(255, c + 80) for c in color)
    segs = [
        ((x1, y1), (x1 + length, y1)), ((x1, y1), (x1, y1 + length)),
        ((x2, y1), (x2 - length, y1)), ((x2, y1), (x2, y1 + length)),
        ((x1, y2), (x1 + length, y2)), ((x1, y2), (x1, y2 - length)),
        ((x2, y2), (x2 - length, y2)), ((x2, y2), (x2, y2 - length)),
    ]
    for p1, p2 in segs:
        cv2.line(img, p1, p2, bright, thick + 3, cv2.LINE_AA)
        cv2.line(img, p1, p2, color,  thick,     cv2.LINE_AA)

    for px, py in [(x1, y1), (x2, y1), (x1, y2), (x2, y2)]:
        cv2.circle(img, (px, py), 3, bright, -1, cv2.LINE_AA)


def _draw_legend_strip(img: np.ndarray, findings: list):
    """Görüntünün sol kenarına renk-kodlu hasar göstergeleri çizer."""
    h, w = img.shape[:2]
    strip_w = 28
    overlay = img.copy()
    cv2.rectangle(overlay, (0, 0), (strip_w, h), (5, 5, 10), -1)
    cv2.addWeighted(overlay, 0.75, img, 0.25, 0, img)
    cv2.line(img, (strip_w, 0), (strip_w, h), (50, 50, 60), 1)

    seg_h = (h - 16) / max(len(findings), 1)
    for i, f in enumerate(findings):
        color = _PRIORITY_META.get(f["priority"], (80, 80, 80, "?"))[2]
        y_top = int(8 + i * seg_h)
        y_bot = int(8 + (i + 1) * seg_h) - 4

        overlay2 = img.copy()
        cv2.rectangle(overlay2, (4, y_top), (strip_w - 4, y_bot), color, -1)
        cv2.addWeighted(overlay2, 0.65, img, 0.35, 0, img)
        cv2.rectangle(img, (4, y_top), (strip_w - 4, y_bot), tuple(min(255, c + 60) for c in color), 1)

        cx = (4 + strip_w - 4) // 2
        cy = (y_top + y_bot) // 2 + 4
        cv2.putText(img, str(i + 1), (cx - 5, cy),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.32, (240, 240, 240), 1, cv2.LINE_AA)


def _draw_header(img: np.ndarray, w: int, damage_pct: float, verdict: str, n: int):
    ov = img.copy()
    cv2.rectangle(ov, (0, 0), (w, 46), (0, 0, 0), -1)
    cv2.addWeighted(ov, 0.70, img, 0.30, 0, img)
    cv2.line(img, (0, 46), (w, 46), (40, 40, 50), 1, cv2.LINE_AA)

    font = cv2.FONT_HERSHEY_SIMPLEX
    cv2.putText(img, "ON UCUS KONTROL ANALIZI",
                (36, 30), font, 0.52, (180, 180, 200), 1, cv2.LINE_AA)

    sc = (20, 200, 80) if damage_pct < 15 else \
         (0, 200, 220) if damage_pct < 35 else \
         (0, 140, 255) if damage_pct < 60 else \
         (20, 20, 220)
    right_txt = f"HASAR: %{damage_pct:.0f}  |  {n} BULGU"
    (tw, _), _ = cv2.getTextSize(right_txt, font, 0.48, 1)
    cv2.putText(img, right_txt, (w - tw - 10, 30), font, 0.48, sc, 1, cv2.LINE_AA)


def _grid_positions(img_w: int, img_h: int, count: int, left_margin: int = 34):
    usable_w = img_w - left_margin
    cols = min(count, 3)
    rows = math.ceil(count / cols)
    cell_w = usable_w // cols
    cell_h = (img_h - 50) // rows
    positions = []
    for i in range(count):
        row, col = divmod(i, cols)
        jx = random.randint(-cell_w // 10, cell_w // 10)
        jy = random.randint(-cell_h // 10, cell_h // 10)
        cx = left_margin + col * cell_w + cell_w // 2 + jx
        cy = 50 + row * cell_h + cell_h // 2 + jy
        bw = max(90,  int(cell_w * 0.70))
        bh = max(55,  int(cell_h * 0.65))
        positions.append((cx, cy, bw, bh))
    return positions


# ─── Endpoint: Uçak Ön Uçuş Kontrol Sistemi ────────────────────────────────
@app.post("/analyze_plane")
async def analyze_plane(file: UploadFile = File(...)):
    raw       = await file.read()
    img       = read_image_from_upload(raw)
    h, w      = img.shape[:2]
    annotated = img.copy()

    num_findings       = random.randint(5, 9)
    positions          = _grid_positions(w, h, num_findings)
    findings           = []
    total_penalty      = 0.0
    total_repair_hours = 0

    cats_pool = list(INSPECTION_CATEGORIES)
    random.shuffle(cats_pool)

    for i in range(num_findings):
        if cats_pool:
            cat = cats_pool[i % len(cats_pool)]
        else:
            cat = random.choice(INSPECTION_CATEGORIES)

        item = random.choice(cat["items"])
        lo, hi, color, _ = _PRIORITY_META.get(item["priority"], (2, 9, (80, 200, 80), "?"))
        severity = random.randint(lo, hi)

        cx, cy, bw, bh = positions[i]
        x1 = max(34, cx - bw // 2)
        y1 = max(50, cy - bh // 2)
        x2 = min(w - 1, cx + bw // 2)
        y2 = min(h - 1, cy + bh // 2)

        _draw_corners(annotated, x1, y1, x2, y2, color, length=18, thick=2)

        prio = item["priority"]
        if prio == "kritik":
            total_penalty += severity * 1.8
        elif prio == "yüksek":
            total_penalty += severity * 1.2
        else:
            total_penalty += severity * 0.7

        total_repair_hours += item["time_h"]
        findings.append({
            "category"   : cat["name"],
            "zone"       : cat["zone"],
            "issue"      : item["issue"],
            "action"     : item["action"],
            "priority"   : prio,
            "severity"   : severity,
            "repair_time": item["time_h"],
            "box_index"  : i + 1,
        })

    _draw_legend_strip(annotated, findings)

    max_penalty  = num_findings * 95 * 1.8
    damage_pct   = min(100.0, round((total_penalty / max_penalty) * 100, 1))

    verdict, verdict_code = "MİNİMAL HASAR", "go"
    for lo, hi, v, vc in _DAMAGE_LEVELS:
        if lo <= damage_pct <= hi:
            verdict, verdict_code = v, vc
            break

    _draw_header(annotated, w, damage_pct, verdict, len(findings))

    findings.sort(key=lambda x: {"kritik": 0, "yüksek": 1, "orta": 2, "düşük": 3}.get(x["priority"], 9))

    cnt = {p: sum(1 for f in findings if f["priority"] == p)
           for p in ("kritik", "yüksek", "orta", "düşük")}

    return {
        "verdict"           : verdict,
        "verdict_code"      : verdict_code,
        "damage_pct"        : damage_pct,
        "takeoff_score"     : damage_pct,
        "critical_damage"   : cnt["kritik"] > 0,
        "findings_count"    : len(findings),
        "critical_count"    : cnt["kritik"],
        "high_count"        : cnt["yüksek"],
        "medium_count"      : cnt["orta"],
        "low_count"         : cnt["düşük"],
        "total_repair_hours": total_repair_hours,
        "findings"          : findings,
        "image"             : img_to_base64(annotated),
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run("api_server:app", host="0.0.0.0", port=8001, reload=False)
