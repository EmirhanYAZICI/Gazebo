"""
analyze_photo_cli.py — CLI Fotoğraf Analizi
============================================
PHP tarafından çağrılır, JSON döner.
Kullanım: python src/analyze_photo_cli.py goruntu.jpg
"""

import sys, os, json, cv2, base64
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

import config
from vision_engine import VisionEngine


def main():
    if len(sys.argv) < 3:
        print(json.dumps({'error': 'Dosya veya cikti yolu belirtilmedi'}))
        return

    path = sys.argv[1]
    out_path = sys.argv[2]
    if not os.path.exists(path):
        with open(out_path, 'w', encoding='utf-8') as f:
            json.dump({'error': f'Dosya bulunamadi: {path}'}, f)
        return

    img = cv2.imread(path)
    if img is None:
        with open(out_path, 'w', encoding='utf-8') as f:
            json.dump({'error': 'Goruntu okunamadi'}, f)
        return

    # YOLO ve VisionEngine loglarını gizle ki PHP sadece JSON alsın
    import io
    old_stdout = sys.stdout
    dummy_out = io.StringIO()
    sys.stdout = dummy_out
    
    try:
        vision = VisionEngine()
        detections = vision.detect(img, detect_animals=True, weapon_conf_override=0.10)
    finally:
        sys.stdout = old_stdout

    annotated = vision.draw_detections(img, detections)
    _, buf = cv2.imencode('.jpg', annotated, [cv2.IMWRITE_JPEG_QUALITY, 85])
    b64 = base64.b64encode(buf).decode()

    persons = [d for d in detections if d.class_id == 0]
    animals = [d for d in detections if d.class_id != 0]

    result = {
        'total_persons': len(persons),
        'armed'        : len([d for d in persons if d.has_weapon]),
        'unarmed'      : len([d for d in persons if not d.has_weapon]),
        'animals'      : len(animals),
        'threat'       : any(d.has_weapon for d in persons),
        'detections'   : [
            {
                'class_name'       : d.class_name,
                'confidence'       : round(d.confidence, 3),
                'has_weapon'       : d.has_weapon,
                'weapon_confidence': round(d.weapon_confidence, 3),
            }
            for d in detections
        ],
        'image': f'data:image/jpeg;base64,{b64}'
    }
    with open(out_path, 'w', encoding='utf-8') as f:
        json.dump(result, f)


if __name__ == '__main__':
    main()
