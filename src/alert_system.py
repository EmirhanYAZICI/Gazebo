"""
alert_system.py — Alarm ve Bildirim Sistemi
=============================================
Silah tespiti olduğunda alarm verir.
Cooldown ile sürekli foto çekmesinin önüne geçer.
"""

import cv2
import os
import json
import time
import winsound
from datetime import datetime
from typing import Optional

import config


class AlertSystem:
    """
    Tehdit algılama alarm sistemi.
    Cooldown sayesinde her frame'de değil,
    yalnızca yeni bir tehdit başladığında alarm verir.
    """

    def __init__(self, cooldown_sec: float = 10.0):
        """
        Args:
            cooldown_sec: İki alarm arasındaki minimum süre (saniye).
                          Bu süre dolmadan ikinci alarm çalmaz.
        """
        self.cooldown_sec = cooldown_sec
        self.alert_count = 0
        self._last_alert_time = 0.0      # Son alarm zamanı
        self.is_alert_active = False     # Şu an aktif tehdit var mı?

        self.log_dir = config.LOG_DIR
        os.makedirs(self.log_dir, exist_ok=True)
        self.log_file = os.path.join(self.log_dir, "alert_log.json")
        self.alerts = self._load_existing_log()

        print(f"[ALARM] Sistem hazir — Cooldown: {cooldown_sec}s — Log: {self.log_dir}")

    def trigger(self, frame, detection, gps_str: str = "Bilinmiyor") -> bool:
        """
        Alarm tetikle. Cooldown süresindeyse tetiklenmez.

        Returns:
            True  → Alarm tetiklendi (yeni tehdit)
            False → Cooldown süresi dolmadı, alarm atlandı
        """
        now = time.time()
        elapsed = now - self._last_alert_time

        if elapsed < self.cooldown_sec:
            # Cooldown: Görsel olarak aktif tehdit göster ama foto/log alma
            self.is_alert_active = True
            return False

        # ── Yeni tehdit — Alarm tetikle ──
        self._last_alert_time = now
        self.is_alert_active = True
        self.alert_count += 1
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")

        print("\n" + "=" * 60)
        print("  SILAHLI KISI TESPIT EDILDI")
        print(f"  Zaman  : {timestamp}")
        print(f"  Guven  : %{detection.weapon_confidence * 100:.1f}")
        print(f"  Konum  : {gps_str}")
        print(f"  Toplam : {self.alert_count}. alarm")
        print("=" * 60 + "\n")

        # Sesli uyarı
        if config.ALERT_SOUND:
            try:
                winsound.Beep(2500, 400)
                winsound.Beep(2500, 400)
            except Exception:
                pass

        # Ekran görüntüsü — sadece yeni tehdit başında, her frame'de değil
        saved_path = None
        web_path = None
        if config.ALERT_SCREENSHOT and frame is not None:
            filename = f"alert_{timestamp}_{self.alert_count}.jpg"
            saved_path = os.path.join(self.log_dir, filename)
            web_path = f"data/logs/{filename}"
            cv2.imwrite(saved_path, frame)
            print(f"[ALARM] Goruntu kaydedildi: {saved_path}")

        # JSON log
        if config.ALERT_LOG:
            alert_data = {
                "id": self.alert_count,
                "timestamp": timestamp,
                "person_confidence": round(detection.confidence, 3),
                "weapon_confidence": round(detection.weapon_confidence, 3),
                "person_bbox": list(detection.bbox),
                "weapon_bbox": list(detection.weapon_bbox) if detection.weapon_bbox else None,
                "gps": gps_str,
                "screenshot": web_path,
            }
            self.alerts.append(alert_data)
            self._save_log()

        return True

    def clear_active(self):
        """Aktif tehdit durumunu sıfırla (kişi frame'den çıkınca)."""
        self.is_alert_active = False

    def get_cooldown_remaining(self) -> float:
        """Bir sonraki alarma kaç saniye kaldığını döner."""
        elapsed = time.time() - self._last_alert_time
        return max(0.0, self.cooldown_sec - elapsed)

    def get_summary(self) -> str:
        remaining = self.get_cooldown_remaining()
        cd = f" | Sonraki alarm: {remaining:.0f}s" if remaining > 0 else ""
        return f"Toplam Alarm: {self.alert_count}{cd}"

    def _load_existing_log(self) -> list:
        """Mevcut log dosyasını yükle."""
        if os.path.exists(self.log_file):
            try:
                with open(self.log_file, "r", encoding="utf-8") as f:
                    return json.load(f)
            except Exception:
                pass
        return []

    def _save_log(self):
        """Alarm logunu JSON dosyasına yaz."""
        try:
            with open(self.log_file, "w", encoding="utf-8") as f:
                json.dump(self.alerts, f, ensure_ascii=False, indent=2)
        except Exception as e:
            print(f"[ALARM] Log yazma hatasi: {e}")
