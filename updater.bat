git restore *.*
Git pull origin encrypt-1.0 
php artisan migrate
composer install 

php artisan db:seed --class=BiometricsTruncateTableSeeder
pause