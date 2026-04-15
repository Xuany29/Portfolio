@echo off
set DB_NAME=FDN
set USER=root
set PASS=root
set PORT=3307

:: Create timestamp
for /f "tokens=2-4 delims=/ " %%a in ("%DATE%") do (
    set dd=%%a
    set mm=%%b
    set yyyy=%%c
)
set hh=%TIME:~0,2%
set hh=%hh: =0%
set nn=%TIME:~3,2%
set TIMESTAMP=%yyyy%-%mm%-%dd%_%hh%-%nn%

:: SQL files folder (relative to this script)
set SQL_FOLDER=%~dp0..\SQLFiles

:: Backup folder
set BACKUP_FOLDER=%~dp0..\backups

:: Backup data
mysqldump -u %USER% -p%PASS% -P %PORT% -h localhost --insert-ignore %DB_NAME% > "%BACKUP_FOLDER%\%DB_NAME%_%TIMESTAMP%.sql"

:: Append additional SQL files
echo Appending additional SQL files...
type "%SQL_FOLDER%\FDN.sql" >> "%BACKUP_FOLDER%\%DB_NAME%_%TIMESTAMP%.sql"
type "%SQL_FOLDER%\index.sql" >> "%BACKUP_FOLDER%\%DB_NAME%_%TIMESTAMP%.sql"
type "%SQL_FOLDER%\insert.sql" >> "%BACKUP_FOLDER%\%DB_NAME%_%TIMESTAMP%.sql"
type "%SQL_FOLDER%\usecase.sql" >> "%BACKUP_FOLDER%\%DB_NAME%_%TIMESTAMP%.sql"
type "%SQL_FOLDER%\Function.sql" >> "%BACKUP_FOLDER%\%DB_NAME%_%TIMESTAMP%.sql"

echo Backup completed: "%BACKUP_FOLDER%\%DB_NAME%_%TIMESTAMP%.sql"
pause

