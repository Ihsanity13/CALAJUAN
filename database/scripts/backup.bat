@echo off
REM ---- CALAJUAN database backup ----

REM Where PostgreSQL's tools live.
set "PGBIN=C:\Program Files\PostgreSQL\18\bin"

REM Ask for the password at run time, so it is never saved in this file.
set /p PGPASSWORD=Enter the postgres password: 

REM Create the backups folder if it doesn't exist yet.
if not exist "%~dp0backups" mkdir "%~dp0backups"

REM Build a timestamp like 20261015_143000 so every backup has its own file.
for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd_HHmmss"') do set STAMP=%%i

REM pg_dump writes the backup.
REM   -F c  = "custom" format: compressed, and restorable with pg_restore
REM   -f    = the output file
REM   the last word (calajuan) is the database being backed up
"%PGBIN%\pg_dump.exe" -h 127.0.0.1 -p 5432 -U postgres -F c -f "%~dp0backups\calajuan_%STAMP%.backup" calajuan

REM errorlevel is 0 when the command succeeded.
if errorlevel 1 (
    echo BACKUP FAILED
    exit /b 1
)

echo Backup saved: %~dp0backups\calajuan_%STAMP%.backup