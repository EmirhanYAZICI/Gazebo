# 📖 Gazebo AI & Analiz Sistemi Kullanım Rehberi

Gazebo, yapay zeka destekli güvenlik izleme, uçak yapısal hasar analizi ve OSINT (açık kaynak istihbaratı) araçlarını tek bir arayüzde toplayan profesyonel bir kontrol panelidir.

## 1. Sistemin Başlatılması
- Masaüstündeki `baslat_web.bat` dosyasına çift tıklayın.
- Arka planda 3 ana servis çalışacaktır:
  1. **AI Analiz Sunucusu (FastAPI):** Fotoğraf analizlerini yapar.
  2. **SpiderFoot OSINT:** Veri toplama motorudur.
  3. **PHP Web Sunucusu:** Gördüğünüz bu arayüzü sunar.
- Tarayıcınızda otomatik olarak `http://localhost:8000` adresi açılacaktır.

---

## 2. Canlı İzleme (Güvenlik)
Bu modül, kameranızdan gelen görüntüyü gerçek zamanlı analiz eder.
- **Nasıl Başlatılır?** Sağ menüden "BAŞLAT" butonuna basın.
- **Tespitler:**
  - **Yeşil Kutu:** Güvenli kişi.
  - **Kırmızı Kutu:** Silahlı tehdit algılanan kişi.
  - **Mavi Kutu:** Hayvan veya nesne.
- **Hassasiyet Ayarı:** Sistem çok fazla yanlış alarm veriyorsa sürgüyü sağa (Kesin) çekin. Hiçbir şeyi kaçırmasın istiyorsanız sola (Geniş) çekin.

---

## 3. Yapay Zeka (Fotoğraf Analizi)
İki farklı analiz modu mevcuttur:

### A. Güvenlik Modu (İnsan/Silah)
- Elinizdeki bir fotoğrafı yükleyerek içindeki insanları ve silahları taratabilirsiniz.
- Sonuçlar sağ panelde detaylı bir liste ve "Confidence" (Güven) oranıyla sunulur.

### B. Ön Uçuş Modu (Uçak Hasar Analizi)
- Bir uçağın dış yüzey fotoğrafını yüklediğinizde sistem:
  - Yüzey çizikleri, perçin gevşemeleri, korozyon ve çatlakları tarar.
  - Bir **"Hasar Skoru"** çıkarır.
  - Hangi parçanın ne kadar sürede tamir edilebileceğini (saat cinsinden) hesaplar.

---

## 4. Palantir (SpiderFoot Analizi)
SpiderFoot motorunu kullanarak bir hedef hakkında kapsamlı bilgi toplamanızı sağlar.
- **Yeni Analiz:** "YENİ ANALİZ" sekmesine gidin.
- **Hedef Belirleme:** 
  - **IP:** Sunucu veya kişi konum tespiti için.
  - **Domain:** Şirket altyapısını ve sızıntıları görmek için.
  - **E-posta:** Sosyal medya hesapları ve veri sızıntılarını bulmak için.
- **Modüller (Use Cases):** 
  - *Footprint:* Tüm dijital izleri sürer.
  - *Investigate:* Tehdit avcılığı yapar.
- **Sistem Çalışma Mantığı:** Palantir, girdiğiniz veriyi dünya çapındaki 200'den fazla API ve veri kaynağında (Shodan, Whois, Google, Twitter vb.) sorgular. Tarama işlemi verinin derinliğine göre 5-15 dakika sürebilir. Sonuçları "Tarama Geçmişi"nden takip edebilirsiniz.
- **Ayarlar:** Tarama ayarlarını ve görselleştirme seçeneklerini bu sekmeden yönetebilirsiniz.

---

## 5. Yapılandırma ve Ayarlar
- **Kamera Seçimi:** Eğer birden fazla kameranız varsa (Dahili kamera, USB kamera), indeksi 0'dan başlayarak değiştirebilirsiniz.
- **Çözünürlük:** Sistemin hızı bilgisayarınızın gücüne göre 640x480 veya 1080p olarak ayarlanabilir.
- **Sesli Uyarı:** Silah algılandığında hoparlörden siren sesi gelmesini sağlar.

---

##  sıkça Sorulan Sorular (SSS)
- **Soru:** "No Signal" yazısı gitmiyor?
  - **Cevap:** Kameranızın başka bir uygulama (Zoom, Skype vb.) tarafından kullanılmadığından emin olun.
- **Soru:** Analiz sonuçları çok yavaş geliyor?
  - **Cevap:** `src/api_server.py` dosyasının terminalde açık olduğundan ve hata vermediğinden emin olun.
- **Soru:** SpiderFoot açılmıyor?
  - **Cevap:** İlk açılışta veritabanı kurulması 10-20 saniye sürebilir, biraz bekleyin.

---

## 7. Kullanılan Teknolojiler ve Kaynaklar
Sistem tamamen açık kaynaklı profesyonel kütüphaneler üzerine inşa edilmiştir:

- **AI Motoru (YOLOv8):** [Ultralytics](https://github.com/ultralytics/ultralytics) - Nesne tespiti için dünyanın en hızlı motoru.
- **Palantir Motoru (SpiderFoot):** [SpiderFoot](https://github.com/smicallef/spiderfoot) - OSINT ve dijital ayak izi analizi.
- **Backend Sunucusu (FastAPI):** [FastAPI](https://github.com/tiangolo/fastapi) - Yüksek performanslı Python API framework.
- **3D Başlangıç Ekranı:** [Three.js](https://github.com/mrdoob/three.js) - Tarayıcı tabanlı 3D render motoru.

### Veriler Nereden Geliyor?
Palantir modülü, tarama başlattığınızda aşağıdaki gibi 200'den fazla kaynağa aynı anda sorgu gönderir:
- **Altyapı Verileri:** Whois, Shodan, Censys, BGPView.
- **Arama Motorları:** Google, Bing, Baidu, DuckDuckGo.
- **Sosyal Medya & Sızıntılar:** Twitter, GitHub, Pastebin, HaveIBeenPwned.
- **Tehdit İstihbaratı:** AlienVault, VirusTotal, AbuseIPDB.

---
*Geliştiriciler: Emirhan Yazıcı & Oğuzhan Yazıcı*
