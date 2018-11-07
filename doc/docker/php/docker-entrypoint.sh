#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/console' ]; then
	mkdir -p app/cache
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX app/cache app/logs
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX app/cache app/logs

	while ! exec 6<>/dev/tcp/db/27017; do
	    echo "$(date) - still trying to connect to mongo"
	    sleep 1
        done

	if [ "$APP_ENV" != 'prod' ]; then
		composer install --prefer-dist --no-progress --no-suggest --no-interaction
	fi

	if [ "$APP_ENV" != 'prod' ]; then
	    bin/console doctrine:mongodb:schema:create
	fi
fi

exec docker-php-entrypoint "$@"
