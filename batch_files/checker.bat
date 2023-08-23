cd ..
timeout /t 30 /nobreak
php artisan connect:biometrics
php artisan schedule:run
pause