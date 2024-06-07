git restore *.*
Git pull origin encrypt-1.0 
php artisan migrate
php artisan db:seed --class=BiometricsTruncateTableSeeder
composer install 

pause