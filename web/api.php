<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

$DATA   = __DIR__ . '/data/';
$DATA   = realpath($DATA) . '/';
$ROOT   = __DIR__ . '/../';
$action = $_GET['action'] ?? 'status';

if (!is_dir($DATA)) {
    @mkdir($DATA, 0777, true);
}

switch ($action) {

    // ── Sistem Durumu ────────────────────────────────────────────
    case 'status':
        header('Content-Type: application/json');
        $f = $DATA . 'status.json';
        echo file_exists($f) ? file_get_contents($f) : json_encode(['error' => 'Sistem baslatilmadi']);
        break;

    // ── Canlı Frame (JPEG) ────────────────────────────────────────────────
    case 'frame':
        $f = $DATA . 'frame.jpg';
        // Windows'ta PHP dosya stat önbelleğini temizle
        clearstatcache(true, $f);
        if (!file_exists($f) || filesize($f) < 100) {
            header('HTTP/1.1 404 Not Found');
            exit;
        }
        // Dosyayı tampona al — Python ile eşzamanlı yazma kilidi sorunu olmaz
        $imgdata = @file_get_contents($f);
        if ($imgdata === false || strlen($imgdata) < 100) {
            header('HTTP/1.1 503 Service Unavailable');
            exit;
        }
        header('Content-Type: image/jpeg');
        header('Content-Length: ' . strlen($imgdata));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $imgdata;
        break;

    // ── Alarm Listesi ────────────────────────────────────────────
    case 'alerts':
        header('Content-Type: application/json');
        $log = $DATA . 'logs/alert_log.json';
        if (!file_exists($log)) { echo '[]'; break; }
        $all = json_decode(file_get_contents($log), true) ?? [];
        echo json_encode(array_values(array_slice(array_reverse($all), 0, 20)));
        break;

    // ── Alarm Listesini Temizle ───────────────────────────────────
    case 'clear_alerts':
        header('Content-Type: application/json');
        $log_dir = $DATA . 'logs/';
        $log     = $log_dir . 'alert_log.json';
        if (is_dir($log_dir)) {
            $files = glob($log_dir . '*.jpg');
            foreach ($files as $file) {
                if (is_file($file)) @unlink($file);
            }
        }
        if (file_exists($log)) {
            file_put_contents($log, json_encode([]));
        }
        echo json_encode(['ok' => true]);
        break;

    // ── Tekil Alarm Silme ─────────────────────────────────────────
    case 'delete_alert':
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['error' => 'POST required']); break; }
        $in  = json_decode(file_get_contents('php://input'), true) ?? [];
        $img = $in['image'] ?? '';
        $log = $DATA . 'logs/alert_log.json';
        if (file_exists($log)) {
            $all = json_decode(file_get_contents($log), true) ?? [];
            $all = array_filter($all, function($item) use ($img) {
                return $item['screenshot'] !== $img;
            });
            file_put_contents($log, json_encode(array_values($all)));
        }
        if ($img) {
            $path = $ROOT . 'web/' . ltrim($img, '/');
            if (file_exists($path)) @unlink($path);
        }
        echo json_encode(['ok' => true]);
        break;

    // ── Ayarları Güncelle ─────────────────────────────────────────
    case 'settings':
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['error' => 'POST required']); break; }
        $in  = json_decode(file_get_contents('php://input'), true) ?? [];
        $sf  = $DATA . 'settings.json';
        $cur = file_exists($sf) ? (json_decode(file_get_contents($sf), true) ?? []) : [];
        $cur = array_merge($cur, $in);
        file_put_contents($sf, json_encode($cur));
        echo json_encode(['ok' => true, 'settings' => $cur]);
        break;

    // ── Fotoğraf Yükle + Analiz Et (FastAPI) ─────────────────────
    case 'upload':
        header('Content-Type: application/json');
        if (!isset($_FILES['file'])) { echo json_encode(['error' => 'Dosya yok']); break; }

        $fastapi_url = 'http://localhost:8001/analyze';
        $ch = curl_init($fastapi_url);
        $cfile = new CURLFile($_FILES['file']['tmp_name'], $_FILES['file']['type'], $_FILES['file']['name']);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => ['file' => $cfile],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $result    = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($result === false || $http_code !== 200) {
            $tmp      = tempnam(sys_get_temp_dir(), 'sar_') . '.jpg';
            $out_json = tempnam(sys_get_temp_dir(), 'sar_') . '.json';
            move_uploaded_file($_FILES['file']['tmp_name'], $tmp);
            $py  = realpath($ROOT . 'venv/Scripts/python.exe');
            $scr = realpath($ROOT . 'src/analyze_photo_cli.py');
            shell_exec('"' . $py . '" "' . $scr . '" "' . $tmp . '" "' . $out_json . '" 2>&1');
            $result = file_exists($out_json) ? file_get_contents($out_json) : json_encode(['error' => 'Python calistirilamadi']);
            @unlink($tmp); @unlink($out_json);
        }
        echo $result ?: json_encode(['error' => 'Sonuc okunamadi']);
        break;

    // ── Uçak Fotoğrafı Analiz Et (FastAPI) ──────────────────────
    case 'upload_plane':
        header('Content-Type: application/json');
        if (!isset($_FILES['file'])) { echo json_encode(['error' => 'Dosya yok']); break; }

        $fastapi_url = 'http://localhost:8001/analyze_plane';
        $ch = curl_init($fastapi_url);
        $cfile = new CURLFile($_FILES['file']['tmp_name'], $_FILES['file']['type'], $_FILES['file']['name']);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => ['file' => $cfile],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $result    = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($result === false || $http_code !== 200) {
            $tmp      = tempnam(sys_get_temp_dir(), 'sar_') . '.jpg';
            $out_json = tempnam(sys_get_temp_dir(), 'sar_') . '.json';
            move_uploaded_file($_FILES['file']['tmp_name'], $tmp);
            $py  = realpath($ROOT . 'venv/Scripts/python.exe');
            $scr = realpath($ROOT . 'src/analyze_plane_cli.py');
            shell_exec('"' . $py . '" "' . $scr . '" "' . $tmp . '" "' . $out_json . '" 2>&1');
            $result = file_exists($out_json) ? file_get_contents($out_json) : json_encode(['error' => 'Python calistirilamadi']);
            @unlink($tmp); @unlink($out_json);
        }
        echo $result ?: json_encode(['error' => 'Sonuc okunamadi']);
        break;

    // ── Motoru Başlat ─────────────────────────────────────────────
    case 'start_engine':
        header('Content-Type: application/json');
        $py       = realpath($ROOT . 'venv/Scripts/python.exe');
        $scr      = realpath($ROOT . 'src/detector.py');
        $log_file = $DATA . 'engine_log.txt';
        $pid_file = $DATA . 'detector.pid';

        if (!$py || !file_exists($py)) {
            echo json_encode(['error' => 'Python bulunamadı: ' . $ROOT . 'venv/Scripts/python.exe']);
            break;
        }
        if (!$scr || !file_exists($scr)) {
            echo json_encode(['error' => 'Detector script bulunamadı: ' . $ROOT . 'src/detector.py']);
            break;
        }

        // ── PID ile mevcut detector'ı durdur ────────────────────────
        if (file_exists($pid_file)) {
            $old_pid = (int)trim(file_get_contents($pid_file));
            if ($old_pid > 0) {
                exec("taskkill /PID {$old_pid} /F 2>nul");
                sleep(2);
            }
            @unlink($pid_file);
        }

        // Yedek: detector.py çalışan python process'lerini kill et
        exec('wmic process where (name="python.exe" or name="python3.exe") get processid,commandline 2>nul', $wmic_lines);
        foreach ($wmic_lines as $wline) {
            if (strpos($wline, 'detector.py') !== false) {
                preg_match('/\d+\s*$/', trim($wline), $wm);
                if (!empty($wm[0])) exec('taskkill /PID ' . (int)$wm[0] . ' /F 2>nul');
            }
        }
        sleep(1);

        // Eski geçici dosyaları temizle
        @unlink($DATA . 'frame.jpg');
        @unlink($DATA . 'frame.jpg.tmp');
        @unlink($DATA . 'detector.lock');
        @file_put_contents($log_file, '');

        // Settings güncelle
        $sf  = $DATA . 'settings.json';
        $cur = file_exists($sf) ? (json_decode(file_get_contents($sf), true) ?? []) : [];
        $cur['running'] = true;
        $cur['paused']  = false;
        file_put_contents($sf, json_encode($cur));

        // Başlangıç durumu
        file_put_contents($DATA . 'status.json', json_encode([
            'status' => 'starting',
            'msg'    => 'Kamera açılıyor...',
            'fps'    => 0,
            'paused' => false,
        ]));

        // ── Arka planda detector'ı başlat (Windows: cmd /c start) ──
        // popen ile cmd.exe üzerinden başlatıyoruz — exec("start /B") doğrudan çalışmaz
        $cmd = 'cmd /c start "" /B "' . $py . '" -u "' . $scr . '" > "' . $log_file . '" 2>&1';
        popen($cmd, 'r');

        echo json_encode(['ok' => true, 'msg' => 'Yapı zeka motoru başlatıldı', 'cmd' => $cmd]);
        break;

    // ── Motor Log (debug) ─────────────────────────────────────────
    case 'engine_log':
        header('Content-Type: text/plain; charset=utf-8');
        $log_file = $DATA . 'engine_log.txt';
        if (!file_exists($log_file)) { echo 'Log dosyası yok.'; break; }
        $lines = file($log_file);
        // Son 80 satırı döndür
        echo implode('', array_slice($lines, -80));
        break;

    // ── Motoru Durdur ─────────────────────────────────────────────
    case 'stop_engine':
        header('Content-Type: application/json');
        $sf  = $DATA . 'settings.json';
        $cur = file_exists($sf) ? (json_decode(file_get_contents($sf), true) ?? []) : [];
        $cur['running'] = false;
        file_put_contents($sf, json_encode($cur));

        // ── 1. Yöntem: settings.json'a 'running: false' yaz (detector bunu okuyunca durur)
        // Detector'a dur sinyali ver, kapatmasını bekle
        sleep(2);

        // ── 2. Yöntem: PID dosyasıyla kesin kill ─────────────────────
        $pid_file = $DATA . 'detector.pid';
        if (file_exists($pid_file)) {
            $pid = (int)trim(file_get_contents($pid_file));
            if ($pid > 0) {
                // Önce nazikçe sor (settings.json'u zaten set ettik)
                // 2s bekledi, hala var mı?
                exec("taskkill /PID {$pid} /F 2>nul", $out, $ret);
                if ($ret === 0) {
                    // Başarılı kill
                } else {
                    // PID kalmamış — zaten kapanmış
                }
            }
            @unlink($pid_file);
        }

        // ── 3. Yöntem: Python süreçlerini tarar (yedek) ───────────────
        exec('wmic process where (name="python.exe" or name="python3.exe") get processid,commandline 2>nul', $lines);
        foreach ($lines as $line) {
            if (strpos($line, 'detector.py') !== false) {
                preg_match('/\d+\s*$/', trim($line), $m);
                if (!empty($m[0])) {
                    exec('taskkill /PID ' . (int)$m[0] . ' /F 2>nul');
                }
            }
        }

        // Dosyaları temizle
        @unlink($DATA . 'status.json');
        @unlink($DATA . 'frame.jpg');
        @unlink($DATA . 'frame.jpg.tmp');
        @unlink($DATA . 'detector.lock');

        echo json_encode(['ok' => true, 'msg' => 'Yapı zeka motoru durduruldu']);
        break;

    // ── SpiderFoot Güncelle ───────────────────────────────────────
    case 'update_spiderfoot':
        header('Content-Type: application/json');
        $py  = realpath($ROOT . 'venv/Scripts/python.exe');
        $scr = realpath($ROOT . 'src/update_spiderfoot.py');
        
        if (!$py || !file_exists($py)) {
            echo json_encode(['error' => 'Python bulunamadı']);
            break;
        }
        
        $output = shell_exec('"' . $py . '" "' . $scr . '" 2>&1');
        $json = json_decode($output, true);
        
        if ($json) {
            echo json_encode($json);
        } else {
            echo json_encode(['error' => 'Güncelleme betiği hata verdi', 'output' => $output]);
        }
        break;

    default:
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Bilinmeyen istek: ' . $action]);
}
