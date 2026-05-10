<style scoped>
/* SCOPED CSS FOR OSINT MODULE */
.osint-wrapper {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
  background: var(--bg-dark); 
  font-family: 'Inter', sans-serif;
  color: var(--text-main);
  box-sizing: border-box;
  padding: 30px;
}

.osint-header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 30px;
  border-bottom: 1px solid rgba(196, 0, 29, 0.2);
  padding-bottom: 25px;
}

.osint-title {
  font-size: 1.2rem;
  font-weight: 800;
  letter-spacing: 2px;
  color: var(--primary-bright);
  text-shadow: 0 0 10px rgba(255, 31, 61, 0.5);
  display: flex;
  align-items: center;
  gap: 10px;
}

.osint-tabs {
  display: flex;
  gap: 10px;
}

.osint-tab-btn {
  background: rgba(196, 0, 29, 0.05);
  border: 1px solid rgba(196, 0, 29, 0.2);
  color: var(--text-muted);
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  letter-spacing: 1px;
}

.osint-tab-btn:hover {
  background: rgba(196, 0, 29, 0.1);
  color: #fff;
}

.osint-tab-btn.active {
  background: rgba(196, 0, 29, 0.15);
  border-color: var(--primary-bright);
  color: var(--primary-bright);
  box-shadow: 0 0 15px rgba(255, 31, 61, 0.3);
}

.osint-content-area {
  flex: 1;
  position: relative;
  display: flex;
  flex-direction: column;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 0 30px rgba(196, 0, 29, 0.1), inset 0 0 20px rgba(255, 31, 61, 0.05);
  border: 1px solid rgba(196, 0, 29, 0.15);
  background: var(--bg-panel);
}

.osint-iframe {
  width: 100%;
  height: 100%;
  border: none;
  display: none;
  border-radius: 12px;
}

.osint-iframe.active {
  display: block;
}

/* LOADING ANIMATION */
.osint-loader-container {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: var(--bg-panel);
  z-index: 10;
  transition: opacity 0.5s ease;
}

.osint-loader-container.hidden {
  opacity: 0;
  pointer-events: none;
}

.osint-spinner {
  width: 60px;
  height: 60px;
  border: 3px solid rgba(196, 0, 29, 0.1);
  border-top-color: var(--primary-bright);
  border-bottom-color: var(--primary);
  border-radius: 50%;
  animation: osint-spin 1.2s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
  margin-bottom: 20px;
  box-shadow: 0 0 20px rgba(196, 0, 29, 0.2);
}

@keyframes osint-spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.osint-loader-text {
  color: var(--primary-bright);
  font-size: 0.9rem;
  font-weight: 700;
  letter-spacing: 3px;
  text-shadow: 0 0 10px rgba(255, 31, 61, 0.5);
  animation: osint-pulse 1.5s ease-in-out infinite alternate;
}

@keyframes osint-pulse {
  from { opacity: 0.6; }
  to { opacity: 1; }
}
</style>

<div class="osint-wrapper" id="osint-wrapper">
  <div class="osint-header">
    <div class="osint-title">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--primary); vertical-align: middle;">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M3 17a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
        <path d="M3 7a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
        <path d="M19 17a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
        <path d="M19 7a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
        <path d="M11 3a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
        <path d="M11 21a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
        <path d="M12 21l-8 -4v-10l8 -4l8 4v10l-8 4" />
        <path d="M9.1 15a2.1 2.1 0 0 1 -.648 -4.098c.282 -1.648 1.319 -2.902 3.048 -2.902c1.694 0 2.906 1.203 3.23 2.8h.17a2.1 2.1 0 0 1 .202 4.19l-.202 .01h-5.8" />
        <path d="M4 7l4.323 2.702" />
        <path d="M16.413 14.758l3.587 2.242" />
        <path d="M4 17l3.529 -2.206" />
        <path d="M14.609 10.37l5.391 -3.37" />
        <path d="M12 3v5" />
        <path d="M12 15v6" />
      </svg>
      <span style="margin-left:10px; vertical-align: middle;">PALANTİR</span>
    </div>
    <div style="flex: 1;"></div>
    <div class="osint-tabs">
      <button class="osint-tab-btn active" data-osint-target="osint-frame-new">YENİ ANALİZ</button>
      <button class="osint-tab-btn" data-osint-target="osint-frame-scans">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
          <path d="M10 14a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
          <path d="M14 4l0 4l-6 0l0 -4" />
        </svg> VARLIK KAYDI
      </button>
      <button class="osint-tab-btn" data-osint-target="osint-frame-viz">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M12 6l0 -3" />
          <path d="M16.25 7.75l2.15 -2.15" />
          <path d="M18 12l3 0" />
          <path d="M16.25 16.25l2.15 2.15" />
          <path d="M12 18l0 3" />
          <path d="M7.75 16.25l-2.15 2.15" />
          <path d="M6 12l-3 0" />
          <path d="M7.75 7.75l-2.15 -2.15" />
        </svg> AYARLAR
      </button>
      <button class="osint-tab-btn" onclick="document.getElementById('osint-help').style.display='block'" style="background:#444; color:#fff; border:none;">YARDIM (?)</button>
      <button class="osint-tab-btn" id="btn-update-sf" style="background:var(--primary); color:#fff; border:none; display: flex; align-items: center; gap: 5px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" />
          <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" />
        </svg> GÜNCELLE
      </button>
    </div>
  </div>

  <div id="osint-help" style="display:none; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.1); border-radius:16px; padding:35px; margin-bottom:25px; position:relative; color:#ccc; box-shadow: 0 10px 40px rgba(0,0,0,0.4); backdrop-filter: blur(10px);">
     <button onclick="this.parentElement.style.display='none'" style="position:absolute; right:20px; top:20px; background:rgba(255,255,255,0.05); border:none; color:#888; cursor:pointer; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition:0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">&times;</button>
     <h3 style="color:#fff; font-size:1.2rem; margin-bottom:25px; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:15px; letter-spacing:1px; font-weight:700;">🕸️ PALANTİR (SPIDERFOOT) KAPSAMLI KULLANIM REHBERİ</h3>
     
     <div style="display:grid; grid-template-columns: 1fr 1fr; gap:40px; font-size:0.85rem; line-height:1.8;">
        <div style="background:rgba(255,255,255,0.01); padding:20px; border-radius:12px; border:1px solid rgba(255,255,255,0.03);">
           <strong style="color:var(--primary); font-size:0.9rem; display:block; margin-bottom:12px; letter-spacing:1px;">1. TARAMA BAŞLATMA (NEW SCAN)</strong>
           <p style="margin-bottom:15px; color:rgba(255,255,255,0.6);">Sistem, hedef hakkında derinlemesine istihbarat toplamak için 200+ modül kullanır:</p>
           <ul style="padding-left:18px; margin-top:5px; color:rgba(255,255,255,0.8);">
              <li style="margin-bottom:8px;"><b>Scan Name:</b> Projenize bir isim verin.</li>
              <li style="margin-bottom:8px;"><b>Scan Target:</b> Şunları analiz edebilirsiniz:
                 <div style="margin:8px 0; padding:10px; background:rgba(0,0,0,0.3); border-radius:8px; font-family:monospace; color:var(--cyan); border: 1px solid rgba(0,240,255,0.1);">
                    - Alan Adı (örn: hedefsite.com)<br>
                    - IP Adresi (örn: 157.240.24.35)<br>
                    - E-posta (örn: admin@site.com)<br>
                    - Bitcoin Adresi, ASN, Telefon...
                 </div>
              </li>
              <li><b>Scan Type:</b> 
                <br>• <i>Footprint:</i> Geniş çaplı dijital varlık tespiti.
                <br>• <i>Investigate:</i> Veri sızıntısı ve güvenlik açığı odaklı.
                <br>• <i>Passive:</i> Hedefe hiç dokunmadan açık kaynaklardan veri çekme.
              </li>
           </ul>
        </div>
        <div style="background:rgba(255,255,255,0.01); padding:20px; border-radius:12px; border:1px solid rgba(255,255,255,0.03);">
           <strong style="color:var(--primary); font-size:0.9rem; display:block; margin-bottom:12px; letter-spacing:1px;">2. VERİLERİ ANLAMLANDIRMA</strong>
           <p style="margin-bottom:15px; color:rgba(255,255,255,0.6);">Tarama bittikten sonra "Varlık Kaydı" kısmına girin:</p>
           <ul style="padding-left:18px; margin-top:5px; color:rgba(255,255,255,0.8);">
              <li style="margin-bottom:10px;"><b>Data View:</b> Tüm bulguları kategorize edilmiş halde (E-postalar, DNS kayıtları, API anahtarları vb.) görün.</li>
              <li style="margin-bottom:10px;"><b>Graph View:</b> Veriler arasındaki bağlantıları görsel bir ağ haritası olarak inceleyin.</li>
              <li style="margin-bottom:10px;"><b>Search:</b> Belirli bir kelimeyi veya IP'yi tüm sonuçlar içinde arayın.</li>
              <li><b>Export:</b> Sonuçları PDF, CSV veya JSON formatında raporlayın.</li>
           </ul>
           <div style="margin-top:20px; padding:15px; background:rgba(255,170,0,0.05); border:1px solid rgba(255,170,0,0.2); border-radius:8px; color:#ffaa00; font-size:0.75rem;">
              ⚠️ DİKKAT: Shodan, VirusTotal gibi servislerden veri çekmek için "Ayarlar" kısmından kendi API anahtarlarınızı girmeniz önerilir.
           </div>
        </div>
     </div>

     <div style="margin-top:30px; padding:25px; background:rgba(0,240,255,0.02); border:1px solid rgba(0,240,255,0.1); border-radius:12px; font-size:0.85rem; line-height:1.7;">
        <strong style="color:var(--cyan); display:block; margin-bottom:10px; letter-spacing:1px;">🛠️ TEKNİK DETAYLAR VE GÜNCELLEME SÜRECİ</strong>
        <p style="color:rgba(255,255,255,0.7);">
          Bu modül, arka planda izole edilmiş bir SpiderFoot 4.0 motoru üzerinde çalışır. <b>"GÜNCELLE"</b> butonuna bastığınızda sistem şu işlemleri gerçekleştirir:
          <br>1. Ana repodan en güncel kodları (git pull) çeker.
          <br>2. Yeni eklenen modülleri ve veri kaynaklarını sisteme entegre eder.
          <br>3. Eksik Python kütüphanelerini (requirements.txt) otomatik olarak kurar.
          <br>4. Güvenlik yamalarını uygulayarak sistemin kararlı çalışmasını sağlar.
        </p>
     </div>
  </div>

  <div class="osint-content-area">
    <div class="osint-loader-container" id="osint-loader">
      <div class="osint-spinner"></div>
      <div class="osint-loader-text">PALANTİR SİSTEMİ HAZIRLANIYOR...</div>
    </div>
    
    <iframe id="osint-frame-new" class="osint-iframe active" src="about:blank" data-src="http://127.0.0.1:5001/newscan"></iframe>
    <iframe id="osint-frame-scans" class="osint-iframe" src="about:blank" data-src="http://127.0.0.1:5001/"></iframe>
    <iframe id="osint-frame-viz" class="osint-iframe" src="about:blank" data-src="http://127.0.0.1:5001/opts"></iframe>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.osint-tab-btn');
  const frames = document.querySelectorAll('.osint-iframe');
  const loader = document.getElementById('osint-loader');
  const updateBtn = document.getElementById('btn-update-sf');
  
  // Tab Switching
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      if(tab.id === 'btn-update-sf') return;
      tabs.forEach(t => t.classList.remove('active'));
      frames.forEach(f => f.classList.remove('active'));
      
      tab.classList.add('active');
      const targetId = tab.getAttribute('data-osint-target');
      if(targetId) {
          const targetFrame = document.getElementById(targetId);
          targetFrame.classList.add('active');
          // Reload if blank
          if(targetFrame.src === 'about:blank') {
              targetFrame.src = targetFrame.getAttribute('data-src');
          }
      }
    });
  });

  // Update Logic
  updateBtn.addEventListener('click', async () => {
    if(!confirm('SpiderFoot sistemi güncellenecek. Emin misiniz?')) return;
    
    updateBtn.disabled = true;
    updateBtn.innerHTML = 'GÜNCELLENİYOR...';
    
    try {
      const resp = await fetch('api.php?action=update_spiderfoot');
      const data = await resp.json();
      
      if(data.success) {
        alert('Güncelleme başarıyla tamamlandı!\n' + (data.git_pull.output || 'Sistem zaten güncel.'));
      } else {
        alert('Güncelleme sırasında hata oluştu: ' + (data.error || 'Bilinmeyen hata'));
        console.error(data);
      }
    } catch (e) {
      alert('Sistem bağlantı hatası!');
    } finally {
      updateBtn.disabled = false;
      updateBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg> GÜNCELLE';
    }
  });

  // Check if SpiderFoot is up
  let checkInterval;
  const checkSpiderFoot = async () => {
    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 2000);
      
      await fetch('http://127.0.0.1:5001/', { 
        mode: 'no-cors',
        signal: controller.signal
      });
      
      clearTimeout(timeoutId);
      clearInterval(checkInterval);
      
      frames.forEach(f => {
        if (f.src === 'about:blank' || f.src === window.location.href) {
          f.src = f.getAttribute('data-src');
        }
      });
      
      setTimeout(() => {
        loader.classList.add('hidden');
      }, 1000);
      
    } catch (e) {
      console.log("SpiderFoot bekleniyor...");
    }
  };

  let isChecking = false;
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting && !isChecking) {
        isChecking = true;
        checkInterval = setInterval(checkSpiderFoot, 3000);
        checkSpiderFoot(); 
      } else if (!entry.isIntersecting && isChecking) {
        clearInterval(checkInterval);
        isChecking = false;
      }
    });
  });
  
  const osintWrapper = document.getElementById('osint-wrapper');
  if (osintWrapper) {
    observer.observe(osintWrapper);
  }
});
</script>
