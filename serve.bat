@echo off
set "PHP_DIR=C:\Users\navod\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe"
set "PATH=%PHP_DIR%;C:\Users\navod\AppData\Local\Programs\Composer;%PATH%"

"%PHP_DIR%\php.exe" -r "exit(extension_loaded('mongodb') ? 0 : 1);"
if errorlevel 1 (
  echo [ERROR] PHP mongodb extension is not loaded.
  echo Enable it in: %PHP_DIR%\php.ini
  echo Add line: extension=mongodb
  exit /b 1
)

echo Starting PizzaFlow with MongoDB-enabled PHP...
"%PHP_DIR%\php.exe" artisan serve %*
