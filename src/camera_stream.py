import cv2
import time
import numpy as np
from typing import Optional, Tuple
from threading import Thread, Lock, Event

import config


class CameraStream:
    """
    Threaded kamera akışı yöneticisi.
    
    Düzeltmeler v2:
    - CAP_DSHOW öncelikli (Windows'ta en stabil)
    - Warm-up: 5 frame'e düşürüldü (15'ten)
    - Thread hızı kameraya göre sınırlandırıldı (FPS cap)
    - stop_event ile clean shutdown
    - cap.release() force edildi
    - CPU kullanımı minimize edildi
    """

    # Windows kamera backend öncelik sırası — DirectShow en stabil
    _BACKENDS = [
        (cv2.CAP_DSHOW, "DirectShow"),
        (cv2.CAP_MSMF,  "MSMF"),
        (cv2.CAP_ANY,   "Generic"),
    ]
    _WARMUP_FRAMES  = 5    # Az warm-up — daha hızlı açılış
    _TARGET_FPS     = 30   # Thread'in bu FPS'i geçmemesi (CPU tasarrufu)
    _RECONNECT_WAIT = 3.0

    def __init__(self, source: str = None, video_path: str = None):
        self.source     = source or config.CAMERA_SOURCE
        self.video_path = video_path or config.CAMERA_VIDEO_PATH
        self.cap: Optional[cv2.VideoCapture] = None
        self._used_backend = "Bilinmiyor"

        # Threading
        self.frame: Optional[np.ndarray] = None
        self.lock       = Lock()
        self.stop_event = Event()          # ← Clean shutdown için Event
        self.thread: Optional[Thread] = None
        self.is_running = False

        # FPS istatistikleri
        self.frame_count  = 0
        self._fps_start   = time.time()
        self._fps_frames  = 0
        self._current_fps = 0.0

        # Reconnect
        self._consecutive_failures = 0
        self._last_reconnect       = 0.0

    # ------------------------------------------------------------------
    def start(self, index: int = None, resolution: Tuple[int, int] = None) -> bool:
        """Kamera akışını başlat."""
        if self.source == "webcam":
            primary_idx = index if index is not None else config.CAMERA_INDEX
            cam_res     = resolution if resolution is not None else config.CAMERA_RESOLUTION

            # En düşük çözünürlükten başla — açılış hızı için 720p max
            if cam_res[0] > 1280:
                cam_res = (1280, 720)
                print(f"[KAMERA] Çözünürlük 1280x720'ye düşürüldü (performans).")

            indices_to_try = [primary_idx]
            for i in [0, 1, 2]:
                if i not in indices_to_try:
                    indices_to_try.append(i)

            opened = False
            for idx in indices_to_try:
                for backend_id, backend_name in self._BACKENDS:
                    print(f"[KAMERA] Deneniyor → index={idx}, backend={backend_name}")
                    cap = cv2.VideoCapture(idx, backend_id)
                    if cap.isOpened():
                        # Çözünürlük, buffer ve FPS ayarları
                        cap.set(cv2.CAP_PROP_FRAME_WIDTH,  cam_res[0])
                        cap.set(cv2.CAP_PROP_FRAME_HEIGHT, cam_res[1])
                        cap.set(cv2.CAP_PROP_BUFFERSIZE,   1)   # Stale frame yok
                        cap.set(cv2.CAP_PROP_FPS, self._TARGET_FPS)
                        self.cap = cap
                        self._used_backend = backend_name
                        print(f"[KAMERA] ✅ İndeks {idx} ({backend_name}) açıldı.")
                        opened = True
                        break
                    else:
                        cap.release()
                if opened:
                    break

            if not opened:
                print("[KAMERA] ❌ Hiçbir kamera + backend kombinasyonu çalışmadı!")
                return False

            # Az warm-up
            print(f"[KAMERA] Kamera ısınıyor ({self._WARMUP_FRAMES} frame)...")
            for _ in range(self._WARMUP_FRAMES):
                self.cap.read()
            print("[KAMERA] ✅ Hazır.")

        elif self.source == "video":
            print(f"[KAMERA] Video: {self.video_path}")
            self.cap = cv2.VideoCapture(self.video_path)

        if self.cap is None or not self.cap.isOpened():
            return False

        # İlk frame
        ret, frame = self.cap.read()
        if ret and frame is not None:
            with self.lock:
                self.frame = frame

        self.is_running = True
        self.stop_event.clear()

        # Thread başlat
        self.thread = Thread(target=self._update, daemon=True, name="CameraThread")
        self.thread.start()

        w = int(self.cap.get(cv2.CAP_PROP_FRAME_WIDTH))
        h = int(self.cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
        print(f"[KAMERA] ✅ Akış başladı — {w}x{h} [{self._used_backend}]")
        return True

    # ------------------------------------------------------------------
    def _update(self):
        """Kameradan sürekli frame okuyan arka plan thread'i."""
        frame_interval = 1.0 / self._TARGET_FPS  # 30fps → ~33ms
        last_read_time = time.time()

        while not self.stop_event.is_set():
            loop_start = time.time()

            if self.cap is None or not self.cap.isOpened():
                break

            ret, frame = self.cap.read()

            if ret and frame is not None:
                with self.lock:
                    self.frame = frame
                self._fps_frames          += 1
                self._consecutive_failures = 0
                last_read_time = time.time()
            else:
                self._consecutive_failures += 1

                # 5 saniye görüntü yok → frame'i sıfırla
                if time.time() - last_read_time > 5.0:
                    with self.lock:
                        self.frame = None

                if self.source == "video":
                    self.cap.set(cv2.CAP_PROP_POS_FRAMES, 0)
                elif self._consecutive_failures >= 20:
                    now = time.time()
                    if now - self._last_reconnect > self._RECONNECT_WAIT:
                        print("[KAMERA] ⚠️ Yeniden bağlanıyor...")
                        self._last_reconnect = now
                        self._reconnect()
                        self._consecutive_failures = 0

            # FPS hesapla
            elapsed = time.time() - self._fps_start
            if elapsed >= 1.0:
                self._current_fps = self._fps_frames / elapsed
                self._fps_frames  = 0
                self._fps_start   = time.time()

            # FPS cap: kameranın doğal hızında çalış, fazla CPU harcama
            elapsed_loop = time.time() - loop_start
            sleep_time   = frame_interval - elapsed_loop
            if sleep_time > 0:
                time.sleep(sleep_time)

        print("[KAMERA] Thread sonlandı.")

    # ------------------------------------------------------------------
    def _reconnect(self):
        """Kamerayı kapatıp yeniden aç."""
        try:
            if self.cap is not None:
                self.cap.release()
                self.cap = None
            time.sleep(0.5)
            for backend_id, backend_name in self._BACKENDS:
                new_cap = cv2.VideoCapture(config.CAMERA_INDEX, backend_id)
                if new_cap.isOpened():
                    new_cap.set(cv2.CAP_PROP_BUFFERSIZE, 1)
                    self.cap = new_cap
                    self._used_backend = backend_name
                    print(f"[KAMERA] ✅ Yeniden bağlandı ({backend_name})")
                    return
                new_cap.release()
            print("[KAMERA] ❌ Yeniden bağlantı başarısız!")
        except Exception as e:
            print(f"[KAMERA] Reconnect hatası: {e}")

    # ------------------------------------------------------------------
    def get_frame(self) -> Optional[np.ndarray]:
        """En taze frame'i döndür."""
        with self.lock:
            if self.frame is not None:
                self.frame_count += 1
                return self.frame.copy()
        return None

    def stop(self):
        """Kamera akışını TÜM RESOURCE'LARI serbest bırakarak durdur."""
        print("[KAMERA] Durdurma sinyali gönderiliyor...")
        self.is_running = False
        self.stop_event.set()   # Thread'e dur sinyali

        # Thread'in bitmesini bekle (max 3 saniye)
        if self.thread and self.thread.is_alive():
            self.thread.join(timeout=3.0)
            if self.thread.is_alive():
                print("[KAMERA] ⚠️ Thread 3s içinde bitmedi — zorla devam ediliyor.")

        # VideoCapture'ı MUTLAKA serbest bırak
        if self.cap is not None:
            try:
                self.cap.release()
            except Exception as e:
                print(f"[KAMERA] Release hatası: {e}")
            finally:
                self.cap = None

        # Frame buffer'ı temizle
        with self.lock:
            self.frame = None

        print("[KAMERA] ✅ Tamamen kapatıldı. Kamera serbest bırakıldı.")

    @property
    def fps(self) -> float:
        return self._current_fps

    @property
    def resolution(self) -> Tuple[int, int]:
        if self.cap and self.cap.isOpened():
            w = int(self.cap.get(cv2.CAP_PROP_FRAME_WIDTH))
            h = int(self.cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
            return (w, h)
        return (0, 0)

    @property
    def backend(self) -> str:
        return self._used_backend
