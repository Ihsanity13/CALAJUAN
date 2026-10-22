@echo off
REM ---- CALAJUAN database restore (into a TEST database) ----

set "PGBIN=C:\Program Files\PostgreSQL\18\bin"

set /p PGPASSWORD=Enter the postgres password: 
set /p BACKUPFILE=Path of the backup file (drag it into this window): 

REM Remove surrounding quotes if the path was pasted with them.
set BACKUPFILE=%BACKUPFILE:"=%

REM Start clean: delete the test database if it exists, then create an empty one.
REM The real "calajuan" database is never touched.
"%PGBIN%\dropdb.exe" --if-exists -h 127.0.0.1 -p 5432 -U postgres calajuan_restore_test
"%PGBIN%\createdb.exe" -h 127.0.0.1 -p 5432 -U postgres calajuan_restore_test

REM pg_restore reads the backup file and rebuilds everything inside the test database.
REM   -d           = the database to restore into
REM   --no-owner   = don't try to recreate the original owner
"%PGBIN%\pg_restore.exe" -h 127.0.0.1 -p 5432 -U postgres -d calajuan_restore_test --no-owner "%BACKUPFILE%"

if errorlevel 1 (
    echo RESTORE FAILED
    exit /b 1
)

echo Restore finished. Check the calajuan_restore_test database in pgAdmin.