import sys, os, json, cv2, base64, random, math

# ─── Uçak Kontrol Kategorileri ────────────────────────────────
INSPECTION_CATEGORIES = [
    {
        "id": "govde", "name": "Gövde Bütünlüğü", "zone": "FUSELAGE",
        "items": [
            {"issue": "Gövde yüzeyinde çizik ve çarpma izi", "action": "P2 zımparalama + epoksi dolgu, primer + topcoat boya ile kapat", "priority": "yüksek", "time_h": 4},
            {"issue": "Panel perçinlerinde gevşeme", "action": "AN470 serisi yeni perçin çak, tork değerini kontrol et", "priority": "kritik", "time_h": 6},
            {"issue": "Kompozit yüzeyde derin çatlak", "action": "NDT ultrasonik muayene; karbon fiber yama ile lamine tamir", "priority": "kritik", "time_h": 14},
            {"issue": "Kapı contalarında yıpranma", "action": "Conta setini tamamen değiştir (Mil-P-25732 spec.)", "priority": "orta", "time_h": 2},
            {"issue": "Gövde alt kısmında göçük izleri", "action": "Strüktürel mühendis değerlendirmesi, gerekirse panel değişimi", "priority": "kritik", "time_h": 10},
        ],
    },
    {
        "id": "kanat", "name": "Kanat & Kontrol Yüzeyleri", "zone": "WING",
        "items": [
            {"issue": "Kanat yüzeyinde boya soyulması ve oksidasyon", "action": "Alodine kaplama + iki kat topcoat boya", "priority": "orta", "time_h": 5},
            {"issue": "Flap menteşesinde boşluk ve aşınma", "action": "AN4 pim + MS21042 somun değişimi, hareket serbestliği kontrolü", "priority": "kritik", "time_h": 8},
            {"issue": "Winglet dip noktasında yorulma çatlağı", "action": "X-ray muayenesi; OEM onaylı yama kiti uygula", "priority": "kritik", "time_h": 16},
        ],
    },
    {
        "id": "inis_takimi", "name": "İniş Takımı Sistemi", "zone": "LANDING_GEAR",
        "items": [
            {"issue": "Lastik aşınma sınırı aşılmış", "action": "Yeni lastik takılacak, fren diski incelenecek", "priority": "kritik", "time_h": 3},
            {"issue": "Amortisör yağ sızıntısı", "action": "O-ring ve yağ keçesi değişimi, basınç testi", "priority": "kritik", "time_h": 5},
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

def _draw_corners(img, x1, y1, x2, y2, color, length=18, thick=2):
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

def _draw_legend_strip(img, findings):
    h, w = img.shape[:2]
    strip_w = 28
    overlay = img.copy()
    cv2.rectangle(overlay, (0, 0), (strip_w, h), (5, 5, 10), -1)
    cv2.addWeighted(overlay, 0.75, img, 0.25, 0, img)
    seg_h = (h - 16) / max(len(findings), 1)
    for i, f in enumerate(findings):
        color = _PRIORITY_META.get(f["priority"], (80, 80, 80, "?"))[2]
        y_top = int(8 + i * seg_h)
        y_bot = int(8 + (i + 1) * seg_h) - 4
        cv2.rectangle(img, (4, y_top), (strip_w - 4, y_bot), color, -1)

def _draw_header(img, w, damage_pct, verdict, n):
    cv2.rectangle(img, (0, 0), (w, 46), (0, 0, 0), -1)
    cv2.putText(img, f"ON UCUS ANALIZI - {verdict}", (36, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.52, (255, 255, 255), 1, cv2.LINE_AA)

def main():
    if len(sys.argv) < 3:
        print(json.dumps({"error": "Usage: python script.py <input> <output>"}))
        return

    input_path = sys.argv[1]
    output_path = sys.argv[2]

    img = cv2.imread(input_path)
    if img is None:
        with open(output_path, "w", encoding="utf-8") as f:
            json.dump({"error": "Image not found"}, f)
        return

    h, w = img.shape[:2]
    annotated = img.copy()
    num_findings = random.randint(5, 9)
    findings = []
    total_penalty = 0
    total_repair_hours = 0

    for i in range(num_findings):
        cat = random.choice(INSPECTION_CATEGORIES)
        item = random.choice(cat["items"])
        lo, hi, color, _ = _PRIORITY_META.get(item["priority"], (2, 9, (80, 200, 80), "?"))
        
        # Fake coordinates for CLI simulation
        x1, y1 = random.randint(50, w-150), random.randint(100, h-100)
        x2, y2 = x1 + random.randint(50, 100), y1 + random.randint(30, 60)
        
        _draw_corners(annotated, x1, y1, x2, y2, color)
        
        severity = random.randint(lo, hi)
        total_penalty += severity
        total_repair_hours += item["time_h"]
        
        findings.append({
            "category": cat["name"],
            "zone": cat["zone"],
            "issue": item["issue"],
            "action": item["action"],
            "priority": item["priority"],
            "severity": severity,
            "repair_time": item["time_h"]
        })

    damage_pct = min(100, (total_penalty / (num_findings * 50)) * 100)
    verdict = "MİNİMAL HASAR"
    verdict_code = "go"
    for lo, hi, v, vc in _DAMAGE_LEVELS:
        if lo <= damage_pct <= hi:
            verdict, verdict_code = v, vc
            break

    _draw_header(annotated, w, damage_pct, verdict, len(findings))
    
    _, buf = cv2.imencode(".jpg", annotated)
    b64 = base64.b64encode(buf).decode()

    result = {
        "verdict": verdict,
        "verdict_code": verdict_code,
        "damage_pct": round(damage_pct, 1),
        "findings_count": len(findings),
        "total_repair_hours": total_repair_hours,
        "findings": findings,
        "image": "data:image/jpeg;base64," + b64
    }

    with open(output_path, "w", encoding="utf-8") as f:
        json.dump(result, f)

if __name__ == "__main__":
    main()
