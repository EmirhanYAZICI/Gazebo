"""
detector.py — Headless Tespit Servisi (PHP için)
================================================
Web arayüzü arkasında sessizce çalışır.
frame.jpg + status.json yazar. PHP API bu dosyaları okur.

Düzeltmeler v2:
- PID dosyası: process'i PowerShell yerine PID ile kill et
- SIGTERM / atexit ile temiz kapanma
- Kamera MUTLAKA kapatılır (arka planda açık kalmaz)
- Skip frame sayısı ayarlandı (daha az CPU)
- Çözünürlük sınırlandırıldı
"""

import sys, os, time, json, cv2, signal, atexit
import tempfile
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from threading import Thread, Lock, Event

import config
from camera_stream import CameraStream
from vision_engine import VisionEngine
from alert_system import AlertSystem

# ─── Mutlak yollar ──────────────────────────────────────────────────────────
BASE_DIR      = os.path.dirname(os.path.abspath(__file__))
DATA_DIR      = os.path.abspath(os.path.join(BASE_DIR, '..', 'web', 'data'))
FRAME_PATH    = os.path.join(DATA_DIR, 'frame.jpg')
STATUS_PATH   = os.path.join(DATA_DIR, 'status.json')
SETTINGS_PATH = os.path.join(DATA_DIR, 'settings.json')
LOCK_PATH     = os.path.join(DATA_DIR, 'detector.lock')
PID_PATH      = os.path.join(DATA_DIR, 'detector.pid')   # ← YENİ: PID dosyası

print(f"[DEBUG] DATA_DIR   : {DATA_DIR}")
print(f"[DEBUG] FRAME_PATH : {FRAME_PATH}")
print(f"[DEBUG] PID        : {os.getpid()}")


# ─── Global kaynaklar (atexit/signal için) ──────────────────────────────────
_camera: CameraStream = None
_writer = None
_shutdown_event = Event()


def _cleanup():
    """Sistem kapanırken çağrılır — kamerayı VE tüm dosyaları temizle."""
    global _camera, _writer
    print("[DETECTOR] Temizlik yapılıyor...")

    # Stop async writer
    if _writer is not None:
        try:
            _writer.stop()
        except Exception:
            pass

    # Kamerayı kapat — EN KRİTİK ADIM
    if _camera is not None:
        try:
            _camera.stop()
        except Exception as e:
            print(f"[DETECTOR] Kamera kapatma hatası: {e}")
        _camera = None

    # Dosyaları temizle
    for p in [FRAME_PATH, STATUS_PATH, LOCK_PATH, PID_PATH]:
        try:
            if os.path.exists(p):
                os.unlink(p)
        except Exception:
            pass

    print("[DETECTOR] ✅ Tamamen kapatıldı.")


def _signal_handler(signum, frame):
    """SIGTERM / SIGINT geldiğinde temiz kapat."""
    print(f"[DETECTOR] Sinyal alındı: {signum}")
    _shutdown_event.set()


# atexit her durumda çalışır (normal çıkış, exception, kill)
atexit.register(_cleanup)

# Signal handler kaydet
signal.signal(signal.SIGTERM, _signal_handler)
try:
    signal.signal(signal.SIGBREAK, _signal_handler)   # Windows Ctrl+Break
except AttributeError:
    pass


# ─── Asenkron Frame Yazıcı ───────────────────────────────────────────────────
class AsyncFrameWriter:
    """Frame yazımını ayrı thread'de yapar — ana loop bloklanmaz."""
    def __init__(self, quality: int = 75):
        self._quality  = quality
        self._lock     = Lock()
        self._pending: bytes | None = None
        self._stop_ev  = Event()
        self._thread   = Thread(target=self._worker, daemon=True, name="FrameWriter")
        self._thread.start()

    def write(self, img: "cv2.Mat") -> None:
        _, buf = cv2.imencode('.jpg', img, [cv2.IMWRITE_JPEG_QUALITY, self._quality])
        data = buf.tobytes()
        with self._lock:
            self._pending = data

    def _worker(self) -> None:
        while not self._stop_ev.is_set():
            data = None
            with self._lock:
                if self._pending is not None:
                    data = self._pending
                    self._pending = None
            if data:
                try:
                    tmp = FRAME_PATH + '.tmp'
                    with open(tmp, 'wb') as f:
                        f.write(data)
                    os.replace(tmp, FRAME_PATH)
                except Exception:
                    pass
            time.sleep(0.008)   # ~120 yazma/sn maksimum

    def stop(self):
        self._stop_ev.set()
        self._thread.join(timeout=2.0)


# ─── Yardımcı Fonksiyonlar ───────────────────────────────────────────────────
def read_settings():
    for _ in range(3):
        try:
            if not os.path.exists(SETTINGS_PATH):
                return {'running': True, 'paused': False}
            with open(SETTINGS_PATH, 'r') as f:
                data = json.load(f)
                return data if isinstance(data, dict) else {'running': True}
        except Exception:
            time.sleep(0.05)
    return {'running': True}


def write_status(data: dict):
    try:
        tmp = STATUS_PATH + '.tmp'
        with open(tmp, 'w') as f:
            json.dump(data, f)
        os.replace(tmp, STATUS_PATH)
    except Exception:
        pass


def write_pid():
    """PID'i dosyaya yaz — PHP bu dosyayı okuyarak process'i kill eder."""
    try:
        with open(PID_PATH, 'w') as f:
            f.write(str(os.getpid()))
    except Exception:
        pass


def acquire_lock() -> bool:
    """Çift process başlatmayı önle."""
    try:
        if os.path.exists(PID_PATH):
            with open(PID_PATH, 'r') as f:
                old_pid = int(f.read().strip())
            try:
                import psutil
                if psutil.pid_exists(old_pid):
                    print(f"[DETECTOR] ⚠️ Zaten çalışıyor (PID={old_pid}). Önce onu kapat...")
                    # Eski process'i kill et, sonra devam et
                    try:
                        os.kill(old_pid, signal.SIGTERM)
                        time.sleep(1.5)
                    except Exception:
                        pass
            except ImportError:
                pass

        write_pid()
        return True
    except Exception as e:
        print(f"[DETECTOR] Lock hatası: {e}")
        return True


# ─── Ana Çalışma Fonksiyonu ─────────────────────────────────────────────────
def run():
    global _camera, _writer

    os.makedirs(DATA_DIR, exist_ok=True)

    if not acquire_lock():
        return

    write_status({
        'status': 'starting',
        'msg'   : 'Kamera açılıyor...',
        'fps'   : 0,
        'paused': False,
    })

    s = read_settings()

    # Çözünürlük ayarı — performans için 720p'ye sınırla
    cam_idx = s.get('camera_index', None)
    res_str = s.get('resolution', '1280x720')
    try:
        w_r, h_r = map(int, res_str.split('x'))
        res = (min(w_r, 1280), min(h_r, 720))   # Max 720p
    except Exception:
        res = (1280, 720)

    # Kamerayı başlat
    _camera = CameraStream()
    if not _camera.start(index=cam_idx, resolution=res):
        err_msg = (
            f"Kamera açılamadı! "
            f"(index: {cam_idx if cam_idx is not None else config.CAMERA_INDEX}, "
            f"çözünürlük: {res[0]}x{res[1]}). "
            f"Kameranın başka bir uygulama tarafından kullanılmadığından emin olun."
        )
        print(f"[HATA] {err_msg}")
        write_status({'status': 'error', 'error': err_msg})
        return

    # AI modellerini yükle
    write_status({'status': 'starting', 'msg': 'Yapay zeka yükleniyor...', 'fps': 0, 'paused': False})
    vision = VisionEngine()
    alert  = AlertSystem(cooldown_sec=10.0)

    # Asenkron frame yazıcı
    _writer = AsyncFrameWriter(quality=75)

    frame_count     = 0
    start_time      = time.time()
    detections      = []
    last_detections = []
    has_threat      = False
    skip_frames     = 2   # Her 3. karede AI çalıştır (CPU tasarrufu)

    print("[DETECTOR] ✅ Başlatıldı — yayın başlıyor...")

    try:
        while not _shutdown_event.is_set():
            s = read_settings()

            # Durdurma komutu geldi mi?
            if not s.get('running', True):
                print("[DETECTOR] Durduruldu (settings.json 'running: false').")
                break

            # Duraklatıldı mı?
            if s.get('paused', False):
                write_status({
                    'fps': 0, 'frame_count': frame_count,
                    'total_persons': 0, 'total_weapons': 0,
                    'alert_count': alert.alert_count,
                    'threat_active': False, 'paused': True,
                    'status': 'live', 'camera_backend': _camera.backend,
                })
                time.sleep(0.1)
                continue

            frame = _camera.get_frame()
            if frame is None:
                time.sleep(0.033)
                continue

            frame_count += 1

            # Yapay zekayı sadece belirli karelerde çalıştır
            if frame_count % (skip_frames + 1) == 0:
                weapon_conf = s.get('weapon_conf', None)
                detections  = vision.detect(frame, weapon_conf_override=weapon_conf)
                last_detections = detections

                has_threat = False
                for det in detections:
                    if det.has_weapon:
                        has_threat = True
                        annotated_save = vision.draw_detections(frame.copy(), detections)
                        alert.trigger(annotated_save, det, "WebUI")

                if not has_threat:
                    alert.clear_active()
            else:
                detections = last_detections

            # Görüntüye tespitleri çiz
            display = vision.draw_detections(frame, detections)

            if has_threat:
                dh, dw = display.shape[:2]
                cv2.rectangle(display, (0, 0), (dw - 1, dh - 1), (0, 0, 255), 8)

            # Boyutu küçült — yayın için 720p yeterli
            dh, dw = display.shape[:2]
            if dw > 1280:
                new_h = int(dh * (1280 / dw))
                display = cv2.resize(display, (1280, new_h), interpolation=cv2.INTER_AREA)

            # Async disk yazma
            _writer.write(display)

            # Durum güncelle
            current_persons = sum(1 for d in detections if d.class_id == config.YOLO_PERSON_CLASS_ID)
            current_weapons = sum(1 for d in detections if d.has_weapon)

            elapsed = time.time() - start_time
            write_status({
                'fps'               : round(frame_count / elapsed, 1) if elapsed > 0 else 0,
                'frame_count'       : frame_count,
                'total_persons'     : current_persons,
                'total_weapons'     : current_weapons,
                'alert_count'       : alert.alert_count,
                'threat_active'     : has_threat,
                'cooldown_remaining': round(alert.get_cooldown_remaining(), 1),
                'paused'            : False,
                'status'            : 'live',
                'camera_backend'    : _camera.backend,
            })

    except KeyboardInterrupt:
        print("[DETECTOR] Ctrl+C alındı.")
    except Exception as e:
        print(f"[DETECTOR] Hata: {e}")
        write_status({'status': 'error', 'error': str(e)})
    # _cleanup() atexit tarafından otomatik çağrılacak


if __name__ == '__main__':
    run()
