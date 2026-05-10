"""
config.py — Merkezi Konfigürasyon Dosyası
==========================================
Tüm sistem ayarları burada tanımlanır.
İnsan + Silah Tespiti odaklı yapılandırma.
"""

import os
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

# ─── Kamera Ayarları ───
CAMERA_SOURCE = "webcam"       # "webcam" | "video"
CAMERA_INDEX = 0               # Webcam indeksi
CAMERA_VIDEO_PATH = os.path.join(BASE_DIR, "assets", "test_videos", "drone_test.mp4")
CAMERA_RESOLUTION = (1280, 720) # Daha akıcı performans için 720p idealdir
CAMERA_FPS = 30


# ─── YOLOv8 Ayarları — İnsan Tespiti ───
YOLO_PERSON_MODEL = os.path.join(BASE_DIR, "yolov8n.pt")   # COCO pretrained — person sınıfı
YOLO_PERSON_CONFIDENCE = 0.30      # Hassasiyeti artırdık (el hareketlerini yakalayabilmesi için)
YOLO_PERSON_CLASS_ID = 0           # COCO: person = 0


# ─── YOLOv8 Ayarları — Silah Tespiti ───
# Roboflow veya HuggingFace'den indirilecek fine-tuned model
YOLO_WEAPON_MODEL = os.path.join(BASE_DIR, "models", "weights", "best.pt")
YOLO_WEAPON_CONFIDENCE = 0.60      # Aşırı korumalı mod (Sadece çok eminse silah der)
YOLO_WEAPON_CLASSES = {
    0: "Silah",       # Gun
}


# ─── Tespit Sistemi Ayarları ───
DETECTION_MODE = "two_stage"   # "two_stage" | "single"
# two_stage: Önce insan bul → sonra silah ara
# single: Tek modelle hem insan hem silah

# İki aşamalı tespit parametreleri
PERSON_CROP_PADDING = 20       # Crop sırasında piksel padding
MIN_PERSON_SIZE = (30, 60)     # Minimum insan boyutu (px)


# ─── Alarm / Uyarı Ayarları ───
ALERT_ON_WEAPON = True         # Silah tespitinde alarm ver
ALERT_SOUND = True             # Sesli uyarı
ALERT_SCREENSHOT = True        # Tespit anında ekran görüntüsü kaydet
ALERT_LOG = True               # Logla


# ─── Loglama Ayarları ───
LOG_DIR = os.path.join(BASE_DIR, "web", "data", "logs")
SAVE_DETECTION_FRAMES = True
LOG_FORMAT = "[{timestamp}] {class_name} — Güven: %{confidence:.1f} — Konum: {location}"
