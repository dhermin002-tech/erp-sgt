@echo off
setlocal

set PHP=C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe

echo.
echo ============================================
echo  SGT KayTech -- Captures visuelles automatiques
echo ============================================
echo.

if "%1"=="" (
    echo [TOUTES LES PAGES]
    echo Lancement de la capture complete...
    %PHP% artisan dusk --filter test_capture_toutes_les_pages
) else (
    echo [PAGE CIBLEE] : %1
    set DUSK_PAGE=%1
    %PHP% artisan dusk --filter test_capture_page_ciblee
)

echo.
echo Screenshots disponibles dans :
echo   tests\Browser\screenshots\
echo.
echo Copier-coller une image dans le chat Claude pour correction visuelle.
echo.

:: Ouvrir le dossier automatiquement
explorer tests\Browser\screenshots

endlocal
