#!/bin/sh
set -e


mkdir -p var/cache/prod var/log public/uploads

chown -R www-data:www-data var public/uploads
chmod -R 775 var public/uploads

php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

php bin/console doctrine:migrations:migrate --no-interaction

exec php-fpm
