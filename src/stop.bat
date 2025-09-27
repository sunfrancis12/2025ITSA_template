@echo off
echo 正在停止醫檢系統...

docker-compose down

echo 系統已停止
echo.
echo 按任意鍵結束...
pause >nul