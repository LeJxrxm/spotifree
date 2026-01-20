#!/bin/sh
set -e


mkdir -p var/cache/prod var/log public/uploads

chown -R www-data:www-data var public/uploads
chmod -R 775 var public/uploads

if [ ! -f config/jwt/private.pem ] || [ ! -f config/jwt/public.pem ]; then
  php bin/console lexik:jwt:generate-keypair --skip-if-exists
fi

php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

php bin/console doctrine:migrations:migrate --no-interaction

exec php-fpm
