#!/usr/bin/env sh
set -ev

mkdir --parents "${HOME}/bin"

composer install --no-interaction --prefer-dist
composer require --no-interaction --dev phpunit/phpunit 
composer update --prefer-dist --no-interaction --prefer-stable


# Coveralls client install
wget https://github.com/satooshi/php-coveralls/releases/download/v1.0.1/coveralls.phar --output-document="${HOME}/bin/coveralls"
chmod u+x "${HOME}/bin/coveralls"

# Ugly hack
echo "memory_limit=-1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini

# To be removed when this issue will be resolved: https://github.com/composer/composer/issues/5355
if [ "${COMPOSER_FLAGS}" = '--prefer-lowest' ]; then
    composer update --prefer-dist --no-interaction --prefer-stable --quiet
fi
composer update --prefer-dist --no-interaction --prefer-stable ${COMPOSER_FLAGS}
