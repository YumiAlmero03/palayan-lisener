cd ..
timeout /t 30 /nobreak
php artisan schedule:run
php artisan connect:biometrics
php artisan biometrics:check_attendance
pause