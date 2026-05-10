# Copyright (c) 2026 Emirhan Yazıcı. All Rights Reserved.
"""
vision_engine.py — YOLOv8 Görüntü İşleme Motoru
==================================================
İki aşamalı tespit sistemi:
  Aşama 1: İnsan tespiti (COCO pretrained)
  Aşama 2: Silah tespiti (Fine-tuned model)

Görsel İyileştirmeler:
  - Glow efektli HUD köşe çerçeveleri (kalın + parlak)
  - Dolu arka planlı, her zaman okunabilir etiketler
  - Tehdit durumunda gradient kırmızı banner
  - Alt kısımda gradient istatistik barı (kişi / silahlı / hayvan)
  - Silah bbox'ı yanıp söner gibi çift çizim
"""

import cv2
import numpy as np
import time
import os
from dataclasses import dataclass, field
from typing import List, Optional, Tuple
from datetime import datetime
from ultralytics import YOLO

import config


@dataclass
class Detection:
    """Tek bir tespit sonucunu temsil eder."""
    class_id: int
    class_name: str
    confidence: float
    bbox: Tuple[int, int, int, int]
    has_weapon: bool = False
    weapon_confidence: float = 0.0
    weapon_bbox: Optional[Tuple[int, int, int, int]] = None
    timestamp: datetime = field(default_factory=datetime.now)

    @property
    def center(self) -> Tuple[int, int]:
        x1, y1, x2, y2 = self.bbox
        return (int((x1 + x2) / 2), int((y1 + y2) / 2))

    @property
    def threat_level(self) -> str:
        return "YUKSEK" if self.has_weapon else "DUSUK"

    def __str__(self):
        w = f" ⚠️ SİLAHLI (%{self.weapon_confidence*100:.0f})" if self.has_weapon else ""
        return (f"[{self.class_name}] Güven: %{self.confidence*100:.1f} "
                f"Konum: {self.center}{w}")


class VisionEngine:
    """
    İki aşamalı YOLOv8 tespit motoru.
    Aşama 1 — İnsan/Hayvan Tespiti (COCO pretrained)
    Aşama 2 — Silah Tespiti (Fine-tuned model, sadece insan crop)
    """

    # ── Renk paleti (BGR) ──────────────────────────────────────────────────
    _COLOR_HUMAN   = (50, 220, 80)      # Yeşil
    _COLOR_THREAT  = (30,  30, 230)     # Kırmızı
    _COLOR_WEAPON  = (20, 140, 255)     # Turuncu
    _COLOR_ANIMAL  = (200, 130,  20)    # Mavi-turuncu

    def __init__(self):
        print(f"[VISION] İnsan modeli yükleniyor: {config.YOLO_PERSON_MODEL}")
        self.person_model = YOLO(config.YOLO_PERSON_MODEL)
        print("[VISION] ✅ İnsan modeli hazır")

        self.weapon_model = None
        weapon_path = config.YOLO_WEAPON_MODEL
        if os.path.exists(weapon_path):
            print(f"[VISION] Silah modeli yükleniyor: {weapon_path}")
            self.weapon_model = YOLO(weapon_path)
            print("[VISION] ✅ Silah modeli hazır")
        else:
            print(f"[VISION] ⚠️ Silah modeli bulunamadı: {weapon_path}")
            print("[VISION]    Sadece insan tespiti aktif")

        self.total_detections = 0
        self.total_weapons    = 0

    # ──────────────────────────────────────────────────────────────────────
    def detect(self, frame: np.ndarray, detect_animals: bool = False,
               weapon_conf_override: float = None) -> List[Detection]:
        """İki aşamalı tespiti çalıştır."""
        detections         = []
        classes_to_detect  = [config.YOLO_PERSON_CLASS_ID]
        if detect_animals:
            classes_to_detect.extend(range(14, 24))

        results = self.person_model.predict(
            source=frame,
            classes=classes_to_detect,
            conf=config.YOLO_PERSON_CONFIDENCE,
            imgsz=320,
            verbose=False,
            half=False,
        )

        if not results or results[0].boxes is None:
            return detections

        boxes = results[0].boxes
        names = self.person_model.names
        for box in boxes:
            x1, y1, x2, y2 = map(int, box.xyxy[0].tolist())
            conf   = float(box.conf[0])
            cls_id = int(box.cls[0])
            
            # COCO Sınıf İsimlerini Türkçeleştir
            translations = {
                "person": "Insan", "bicycle": "Bisiklet", "car": "Otomobil", "motorcycle": "Motosiklet",
                "airplane": "Ucak", "bus": "Otobus", "train": "Tren", "truck": "Kamyon", "boat": "Tekne",
                "traffic light": "Trafik Isigi", "fire hydrant": "Yangin Muslugu", "stop sign": "Dur Isareti",
                "parking meter": "Parkometre", "bench": "Bank", "bird": "Kus", "cat": "Kedi", "dog": "Kopek",
                "horse": "At", "sheep": "Koyun", "cow": "Inek", "elephant": "Fil", "bear": "Ayi",
                "zebra": "Zebra", "giraffe": "Zurafa", "backpack": "Sirt Cantasi", "umbrella": "Semsiye",
                "handbag": "El Cantasi", "tie": "Kravat", "suitcase": "Valiz", "frisbee": "Frisbi",
                "skis": "Kayak", "snowboard": "Snowboard", "sports ball": "Top", "kite": "Ucurtma",
                "baseball bat": "Beyzbol Sopasi", "baseball glove": "Beyzbol Eldiveni", "skateboard": "Kaykay",
                "surfboard": "Sorf Tahtasi", "tennis racket": "Tenis Raketi", "bottle": "Sise",
                "wine glass": "Kadeh", "cup": "Fincan", "fork": "Çatal", "knife": "Bicak", "spoon": "Kasik",
                "bowl": "Kase", "banana": "Muz", "apple": "Elma", "sandwich": "Sandvic", "orange": "Portakal",
                "broccoli": "Brokoli", "carrot": "Havuc", "hot dog": "Sosisli", "pizza": "Pizza",
                "donut": "Donut", "cake": "Pasta", "chair": "Sandalye", "couch": "Kanepe",
                "potted plant": "Saksi Bitkisi", "bed": "Yatak", "dining table": "Yemek Masasi",
                "toilet": "Tuvalet", "tv": "Televizyon", "laptop": "Dizustu Bilgisayar", "mouse": "Fare",
                "remote": "Kumanda", "keyboard": "Klavye", "cell phone": "Telefon", "microwave": "Mikrodalga",
                "oven": "Firin", "toaster": "Ekmek Kizartma Makinesi", "sink": "Evye", "refrigerator": "Buzdolabi",
                "book": "Kitap", "clock": "Saat", "vase": "Vazo", "scissors": "Makas", "teddy bear": "Oyuncak Ayi",
                "hair drier": "Sac Kurutma Makinesi", "toothbrush": "Dis Fircasi"
            }
            
            raw_name = names[cls_id].lower()
            cls_name = translations.get(raw_name, raw_name.capitalize())

            detection = Detection(
                class_id=cls_id, class_name=cls_name,
                confidence=conf, bbox=(x1, y1, x2, y2),
            )

            if cls_id == config.YOLO_PERSON_CLASS_ID and self.weapon_model is not None:
                detection = self._check_weapon(frame, detection, weapon_conf_override)

            detections.append(detection)
            if cls_id == config.YOLO_PERSON_CLASS_ID:
                self.total_detections += 1

        return detections

    # ──────────────────────────────────────────────────────────────────────
    def _check_weapon(self, frame: np.ndarray, detection: Detection,
                      weapon_conf_override: float = None) -> Detection:
        x1, y1, x2, y2 = detection.bbox
        h, w = frame.shape[:2]
        pad  = config.PERSON_CROP_PADDING

        cx1 = max(0, x1 - pad)
        cy1 = max(0, y1 - pad)
        cx2 = min(w, x2 + pad)
        cy2 = min(h, y2 + pad)

        person_crop = frame[cy1:cy2, cx1:cx2]
        if person_crop.size == 0:
            return detection

        conf_to_use = weapon_conf_override if weapon_conf_override is not None else config.YOLO_WEAPON_CONFIDENCE

        weapon_results = self.weapon_model.predict(
            source=person_crop, conf=conf_to_use,
            imgsz=224, verbose=False,
        )

        if weapon_results and weapon_results[0].boxes is not None:
            wb = weapon_results[0].boxes
            if len(wb) > 0:
                best_idx = wb.conf.argmax()
                wx1, wy1, wx2, wy2 = map(int, wb.xyxy[best_idx].tolist())
                detection.has_weapon       = True
                detection.weapon_confidence = float(wb.conf[best_idx])
                detection.weapon_bbox      = (cx1 + wx1, cy1 + wy1, cx1 + wx2, cy1 + wy2)
                self.total_weapons         += 1

        return detection

    # ──────────────────────────────────────────────────────────────────────
    # Çizim yardımcıları
    # ──────────────────────────────────────────────────────────────────────
    def _draw_hud_corners(self, img: np.ndarray, x1, y1, x2, y2,
                          color: tuple, length: int = 25, thickness: int = 3):
        """Glow efektli HUD köşe çizgileri çizer."""
        # Glow katmanı — soluk, kalın (parıltı hissi)
        glow = tuple(min(255, c + 80) for c in color)
        segs = [
            ((x1, y1), (x1 + length, y1)),  ((x1, y1), (x1, y1 + length)),
            ((x2, y1), (x2 - length, y1)),  ((x2, y1), (x2, y1 + length)),
            ((x1, y2), (x1 + length, y2)),  ((x1, y2), (x1, y2 - length)),
            ((x2, y2), (x2 - length, y2)),  ((x2, y2), (x2, y2 - length)),
        ]
        for p1, p2 in segs:
            cv2.line(img, p1, p2, glow, thickness + 4, cv2.LINE_AA)  # glow
            cv2.line(img, p1, p2, color, thickness, cv2.LINE_AA)      # ana

    def _draw_label(self, img: np.ndarray, overlay: np.ndarray,
                    x1, y1, x2, h_img, w_img,
                    text: str, color: tuple, font_scale: float = 0.55):
        """Dolu arka planlı, her zaman okunabilir etiket çizer."""
        font = cv2.FONT_HERSHEY_DUPLEX
        (tw, th), _ = cv2.getTextSize(text, font, font_scale, 1)
        pad = 6

        if y1 - th - pad * 2 < 0:
            bg_y1, bg_y2 = y1, y1 + th + pad * 2
            ty = bg_y1 + th + pad
        else:
            bg_y2, bg_y1 = y1, y1 - th - pad * 2
            ty = bg_y2 - pad

        bx2 = min(x1 + tw + pad * 2, w_img - 1)
        cv2.rectangle(overlay, (x1, bg_y1), (bx2, bg_y2), color, -1)
        cv2.putText(img, text, (x1 + pad, ty), font, font_scale,
                    (255, 255, 255), 1, cv2.LINE_AA)

    def _draw_threat_banner(self, img: np.ndarray, h: int, w: int):
        """Tehdit varsa üst kısımda gradient kırmızı banner çizer."""
        overlay = img.copy()
        cv2.rectangle(overlay, (0, 0), (w, 55), (20, 0, 160), -1)
        cv2.addWeighted(overlay, 0.7, img, 0.3, 0, img)
        cv2.putText(img, "!! TEHDIT ALGILANDI !!",
                    (w // 2 - 230, 38),
                    cv2.FONT_HERSHEY_DUPLEX, 1.0,
                    (255, 255, 255), 2, cv2.LINE_AA)
        # Kırmızı alt kenarlık
        cv2.line(img, (0, 55), (w, 55), (0, 0, 255), 2, cv2.LINE_AA)

    def _draw_stats_bar(self, img: np.ndarray, h: int, w: int,
                        persons: int, weapons: int, animals: int):
        """Alt kısımda gradient istatistik barı çizer."""
        overlay = img.copy()
        cv2.rectangle(overlay, (0, h - 44), (w, h), (0, 0, 0), -1)
        cv2.addWeighted(overlay, 0.6, img, 0.4, 0, img)
        # Üst kenarlık
        cv2.line(img, (0, h - 44), (w, h - 44), (50, 50, 50), 1, cv2.LINE_AA)

        font  = cv2.FONT_HERSHEY_DUPLEX
        scale = 0.55
        y     = h - 14

        # Kişi
        cv2.putText(img, f"INSAN: {persons}",
                    (16, y), font, scale, (80, 220, 80), 1, cv2.LINE_AA)
        # Silahlı
        armed_color = (60, 60, 255) if weapons > 0 else (120, 120, 120)
        cv2.putText(img, f"  SILAHLI: {weapons}",
                    (160, y), font, scale, armed_color, 1, cv2.LINE_AA)
        # Hayvan
        cv2.putText(img, f"  HAYVAN: {animals}",
                    (340, y), font, scale, (50, 170, 255), 1, cv2.LINE_AA)

    # ──────────────────────────────────────────────────────────────────────
    def draw_detections(self, frame: np.ndarray,
                        detections: List[Detection]) -> np.ndarray:
        """Tespitleri frame üzerine çiz (Premium HUD Tarzı)."""
        annotated = frame.copy()
        overlay   = frame.copy()
        h, w      = frame.shape[:2]

        has_threat  = any(d.has_weapon for d in detections)
        n_persons   = sum(1 for d in detections if d.class_id == config.YOLO_PERSON_CLASS_ID)
        n_weapons   = sum(1 for d in detections if d.has_weapon)
        n_animals   = sum(1 for d in detections if d.class_id != config.YOLO_PERSON_CLASS_ID)

        for det in detections:
            x1, y1, x2, y2 = det.bbox
            x1, y1 = max(0, x1), max(0, y1)
            x2, y2 = min(w, x2), min(h, y2)

            if det.has_weapon:
                color  = self._COLOR_THREAT
                label  = f"SILAHLI INSAN  %{det.confidence*100:.0f}"
            elif det.class_id == config.YOLO_PERSON_CLASS_ID:
                color  = self._COLOR_HUMAN
                label  = f"Insan  %{det.confidence*100:.0f}"
            else:
                color  = self._COLOR_ANIMAL
                label  = f"{det.class_name}  %{det.confidence*100:.0f}"

            # Ana HUD çerçevesi
            self._draw_hud_corners(annotated, x1, y1, x2, y2, color,
                                   length=28, thickness=3)
            # Etiket
            self._draw_label(annotated, overlay, x1, y1, x2, h, w,
                             label, color, font_scale=0.55)

            # Silah bbox
            if det.has_weapon and det.weapon_bbox:
                wx1, wy1, wx2, wy2 = det.weapon_bbox
                wx1, wy1 = max(0, wx1), max(0, wy1)
                wx2, wy2 = min(w, wx2), min(h, wy2)
                # Çift çizim → yanıp söner etkisi
                self._draw_hud_corners(annotated, wx1, wy1, wx2, wy2,
                                       self._COLOR_WEAPON, length=14, thickness=2)
                self._draw_hud_corners(annotated, wx1 - 2, wy1 - 2, wx2 + 2, wy2 + 2,
                                       (0, 80, 200), length=10, thickness=1)
                cv2.putText(annotated,
                            f"SILAH  %{det.weapon_confidence*100:.0f}",
                            (wx1, max(wy1 - 8, 15)),
                            cv2.FONT_HERSHEY_DUPLEX, 0.45,
                            self._COLOR_WEAPON, 1, cv2.LINE_AA)

        # Alpha blending (şeffaf etiket arka planları)
        cv2.addWeighted(overlay, 0.35, annotated, 0.65, 0, annotated)

        # Tehdit banner'ı
        if has_threat:
            self._draw_threat_banner(annotated, h, w)

        # İstatistik barı
        self._draw_stats_bar(annotated, h, w, n_persons, n_weapons, n_animals)

        return annotated
