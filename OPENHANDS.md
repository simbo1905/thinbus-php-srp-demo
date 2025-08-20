Exact steps I used on Debian 12 to run the vendor tests (PHP 7.4)

1) Add Sury PHP repo and install PHP 7.4 + required extensions
   sudo apt-get update
   sudo apt-get install -y ca-certificates apt-transport-https lsb-release gnupg curl unzip git
   sudo mkdir -p /etc/apt/keyrings
   curl -fsSL https://packages.sury.org/php/apt.gpg | sudo gpg --dearmor -o /etc/apt/keyrings/sury-php.gpg
   echo "deb [signed-by=/etc/apt/keyrings/sury-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/sury-php.list
   sudo apt-get update
   sudo apt-get install -y php7.4 php7.4-cli php7.4-xml php7.4-mbstring php7.4-curl php7.4-sqlite3 php7.4-bcmath php7.4-gmp

2) Install a composer phar and run it with PHP 7.4
   curl -sS https://getcomposer.org/installer -o composer-setup.php
   php7.4 composer-setup.php --install-dir=/usr/local/bin --filename=composer74
   rm composer-setup.php

3) From the repo root, install deps and run tests
   COMPOSER_ALLOW_SUPERUSER=1 php7.4 /usr/local/bin/composer74 update
   php7.4 ./vendor/phpunit/phpunit/phpunit --verbose ./vendor/simon_massey/thinbus-php-srp/test/ThinbusTest.php
