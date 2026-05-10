@echo off
echo ===================================================
echo  Gazebo AI Sistemi Baslatiliyor...
echo ===================================================
echo.

SET PHP_EXE=php
IF EXIST "C:\xampp\php\php.exe" (
    SET PHP_EXE="C:\xampp\php\php.exe"
)

SET VENV_PY=%~dp0venv\Scripts\python.exe

echo [1/3] FastAPI AI Sunucusu Port 8001 baslatiliyor (arka planda)...
start "Gazebo FastAPI" /B "%VENV_PY%" -m uvicorn src.api_server:app --host 0.0.0.0 --port 8001

echo [2/3] SpiderFoot OSINT Sunucusu Port 5001 baslatiliyor (arka planda)...
pushd "%~dp0spiderfoot"
start "SpiderFoot OSINT" /B "..\venv\Scripts\python.exe" sf.py -l 127.0.0.1:5001
popd

echo [3/3] PHP Web Arayuzu Port 8000 baslatiliyor...
echo.
echo  Web Adresi : http://localhost:8000
echo  FastAPI    : http://localhost:8001/docs
echo.
echo  KAPATMAK ICIN BU PENCEREYI KAPATINIZ.
echo ===================================================

REM Tarayiciyi otomatik ac (2 saniye bekle ki FastAPI baslasin)
timeout /t 2 /nobreak >nul
start http://localhost:8000

REM PHP sunucusunu baslat (bu pencereyi bloklar)
%PHP_EXE% -S localhost:8000 -t web/

pause
