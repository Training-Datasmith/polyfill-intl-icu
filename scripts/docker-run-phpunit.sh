#!/usr/bin/env bash
set -euo pipefail

PHP_IMAGE="${1:?PHP image digest reference required}"
PHPUNIT_URL="${2:?PHPUnit PHAR URL required}"
PHPUNIT_SHA="${3:?PHPUnit SHA256 required}"
ORDER_ARGS="${4:-}"

COMPOSER_URL="https://getcomposer.org/download/2.2.25/composer.phar"
COMPOSER_SHA="8b3f41253363f0645402d1951d6e7f02adedeef29c16de6074763e463e25c23f"

sudo docker run --rm -v "/workspace:/src" -w /src "${PHP_IMAGE}" bash -lc "
set -euo pipefail
curl -fsSL -o composer.phar '${COMPOSER_URL}'
echo '${COMPOSER_SHA}  composer.phar' | sha256sum -c -
php composer.phar dump-autoload --dev --no-interaction
curl -fsSL -o phpunit.phar '${PHPUNIT_URL}'
echo '${PHPUNIT_SHA}  phpunit.phar' | sha256sum -c -
php -r 'if (extension_loaded(\"intl\")) { fwrite(STDERR, \"intl must not be loaded\\n\"); exit(1); }'
php phpunit.phar --configuration phpunit.xml.dist ${ORDER_ARGS}
"
