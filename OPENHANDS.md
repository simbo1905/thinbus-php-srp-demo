Quick setup to install dependencies and run Thinbus PHP SRP vendor tests

Note: The upstream library targets PHP 5.6/7.x and its PHPUnit (6.x) does not run on PHP 8.x. Use PHP 7.4 to install deps and run tests.

Option A — Debian/Ubuntu (Debian 12 tested) with PHP 7.4 via Sury
1) Add Sury PHP repo (for PHP 7.4):
   sudo apt-get update
   sudo apt-get install -y ca-certificates apt-transport-https lsb-release gnupg curl unzip git
   sudo mkdir -p /etc/apt/keyrings
   curl -fsSL https://packages.sury.org/php/apt.gpg | sudo gpg --dearmor -o /etc/apt/keyrings/sury-php.gpg
   echo "deb [signed-by=/etc/apt/keyrings/sury-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/sury-php.list
   sudo apt-get update

2) Install PHP 7.4 + required extensions:
   sudo apt-get install -y php7.4 php7.4-cli php7.4-xml php7.4-mbstring php7.4-curl php7.4-sqlite3 php7.4-bcmath php7.4-gmp

3) Install Composer for PHP 7.4 specifically (avoid the distro composer):
   curl -sS https://getcomposer.org/installer -o composer-setup.php
   php7.4 composer-setup.php --install-dir=/usr/local/bin --filename=composer74
   rm composer-setup.php
   export COMPOSER_ALLOW_SUPERUSER=1   # avoid interactive prompt when running as root

4) Install dependencies and run tests from repo root:
   php7.4 /usr/local/bin/composer74 update
   php7.4 ./vendor/phpunit/phpunit/phpunit --verbose ./vendor/simon_massey/thinbus-php-srp/test/ThinbusTest.php

Option B — Docker (works anywhere, no host PHP install needed)
1) Run inside an official PHP 7.4 container:
   docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:7.4-cli bash -lc "\
     apt-get update && apt-get install -y git unzip libzip-dev zlib1g-dev libgmp-dev && \
     docker-php-ext-install bcmath gmp && \
     curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
     COMPOSER_ALLOW_SUPERUSER=1 php /usr/local/bin/composer update && \
     php ./vendor/phpunit/phpunit/phpunit --verbose ./vendor/simon_massey/thinbus-php-srp/test/ThinbusTest.php"

Notes
- Do NOT use the distro "composer" with PHP 8.x for this repo; it will fail on PHP constraints. Use the composer phar invoked with php7.4 as shown.
- pear/math_biginteger is pulled by composer; enabling ext-bcmath or ext-gmp improves performance and avoids edge issues.
- SQLite is used by the demo; ensure php-sqlite3 is installed (already included above).
- If PHP 7.4 packages are unavailable for your distro, prefer Option B.
