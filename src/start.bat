@echo off
echo 正在啟動醫檢系統...
echo.

:: 檢查 Docker 是否運行
docker --version >nul 2>&1
if errorlevel 1 (
    echo 錯誤：未找到 Docker，請先安裝 Docker Desktop
    pause
    exit /b 1
)

:: 檢查 Docker Compose 是否可用
docker-compose --version >nul 2>&1
if errorlevel 1 (
    echo 錯誤：未找到 Docker Compose
    pause
    exit /b 1
)

echo 開始建置並啟動容器...
docker-compose up -d

if errorlevel 1 (
    echo 錯誤：容器啟動失敗
    pause
    exit /b 1
)

echo.
echo 系統啟動成功！
echo.
echo 請開啟瀏覽器訪問：http://localhost
echo.
echo 測試帳號資訊請參考 test_accounts.md 檔案
echo.
echo 按任意鍵結束...
pause >nul