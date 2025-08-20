#!/usr/bin/env bash
set -euo pipefail

# setup-php.sh
# Installs PHP CLI with required extensions, Composer, and PHPUnit appropriate for the PHP version.
# Intended for OpenHands environments. Location: .openhands/tools/setup-php.sh
#
# Usage:
#   ./.openhands/tools/setup-php.sh [PHP_VERSION]
#   PHP_VERSION env var also supported. Defaults to system/default PHP version if not provided.
#
# Extensions installed: bcmath, gmp, sqlite3, mbstring, curl, xml (dom/xml provided by php-xml)
# PHPUnit mapping:
#   - PHP 7.4  -> PHPUnit 9.6
#   - PHP 8.1  -> PHPUnit 10
#   - PHP 8.2+ -> PHPUnit 11

PHP_VERSION_INPUT="${1:-${PHP_VERSION:-}}"
SUDO=""
if command -v sudo >/dev/null 2>&1; then
  SUDO="sudo"
fi

say() { echo "[setup-php] $*"; }

apt_install() {
  $SUDO apt-get update -y
  $SUDO DEBIAN_FRONTEND=noninteractive apt-get install -y "$@"
}

# Detect OS family (Debian/Ubuntu expected in OpenHands containers)
if [ -f /etc/debian_version ]; then
  say "Detected Debian/Ubuntu OS"
else
  say "Non-Debian OS detected; attempting best-effort install"
fi

# Ensure basic tooling
apt_install ca-certificates curl gnupg lsb-release >/dev/null 2>&1 || true

# Decide package naming scheme: versioned (php8.2-xyz) or unversioned (php-xyz)
PKG_PREFIX="php"  # default unversioned
PHP_VERSION_ACTUAL=""

if [ -n "$PHP_VERSION_INPUT" ]; then
  # If explicit version requested and versioned packages exist, use those
  if apt-cache policy "php${PHP_VERSION_INPUT}-cli" >/dev/null 2>&1; then
    if apt-cache policy "php${PHP_VERSION_INPUT}-cli" | grep -q Candidate:; then
      if apt-cache policy "php${PHP_VERSION_INPUT}-cli" | grep -q "Candidate: (none)"; then
        : # fall back to unversioned
      else
        PKG_PREFIX="php${PHP_VERSION_INPUT}"
        PHP_VERSION_ACTUAL="$PHP_VERSION_INPUT"
      fi
    fi
  fi
fi

# Base packages
BASE_PKGS=("${PKG_PREFIX}-cli")
# Extension mapping: dom/xml via php-xml
EXTS=(bcmath gmp sqlite3 mbstring curl xml)
for ext in "${EXTS[@]}"; do
  BASE_PKGS+=("${PKG_PREFIX}-${ext}")
done

say "Installing: ${BASE_PKGS[*]}"
apt_install "${BASE_PKGS[@]}"

# Determine actual PHP version available
if command -v php >/dev/null 2>&1; then
  PHP_VERSION_ACTUAL=${PHP_VERSION_ACTUAL:-$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')}
  say "PHP installed: $(php -v | head -n1)"
else
  say "ERROR: php command not found after installation" >&2
  exit 1
fi

# Install Composer if missing
if ! command -v composer >/dev/null 2>&1; then
  say "Installing Composer"
  curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
  $SUDO php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer >/dev/null
fi
say "Composer: $(composer -V | head -n1)"

# Determine PHPUnit major version based on PHP version
PHP_MAJOR=${PHP_VERSION_ACTUAL%%.*}
PHP_MINOR=${PHP_VERSION_ACTUAL#*.}
PHPUNIT_MAJOR="11"
if [ "$PHP_MAJOR" -eq 7 ] && [ "$PHP_MINOR" -eq 4 ]; then
  PHPUNIT_MAJOR="9"
elif [ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -eq 1 ]; then
  PHPUNIT_MAJOR="10"
else
  PHPUNIT_MAJOR="11"
fi

# Install phpunit PHAR
if [ "$PHPUNIT_MAJOR" = "9" ]; then
  PHPUNIT_URL="https://phar.phpunit.de/phpunit-9.phar"
elif [ "$PHPUNIT_MAJOR" = "10" ]; then
  PHPUNIT_URL="https://phar.phpunit.de/phpunit-10.phar"
else
  PHPUNIT_URL="https://phar.phpunit.de/phpunit-11.phar"
fi

say "Installing PHPUnit ${PHPUNIT_MAJOR} from ${PHPUNIT_URL}"
TMP_PHAR="/tmp/phpunit-${PHPUNIT_MAJOR}.phar"
curl -fsSL "$PHPUNIT_URL" -o "$TMP_PHAR"
$SUDO mv "$TMP_PHAR" /usr/local/bin/phpunit
$SUDO chmod +x /usr/local/bin/phpunit
say "PHPUnit: $(phpunit --version | head -n1)"

say "Done."
