#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var

	if [ "$APP_ENV" != 'prod' ]; then
		composer install --prefer-dist --no-progress --no-interaction
	fi

	if grep -q DATABASE_URL= .env; then
		echo "Waiting for database to be ready..."
		ATTEMPTS_LEFT_TO_REACH_DATABASE=60
		until [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ] || DATABASE_ERROR=$(php bin/console dbal:run-sql -q "SELECT 1" 2>&1); do
			if [ $? -eq 255 ]; then
				# If the Doctrine command exits with 255, an unrecoverable error occurred
				ATTEMPTS_LEFT_TO_REACH_DATABASE=0
				break
			fi
			sleep 1
			ATTEMPTS_LEFT_TO_REACH_DATABASE=$((ATTEMPTS_LEFT_TO_REACH_DATABASE - 1))
			echo "Still waiting for database to be ready... Or maybe the database is not reachable. $ATTEMPTS_LEFT_TO_REACH_DATABASE attempts left."
		done

		if [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ]; then
			echo "The database is not up or not reachable:"
			echo "$DATABASE_ERROR"
			exit 1
		else
			echo "The database is now ready and reachable"
		fi

		if [ "$( find ./migrations -iname '*.php' -print -quit )" ]; then
			php bin/console doctrine:migrations:migrate --no-interaction
		fi

		if [ "$APP_ENV" != 'prod' ]; then
			echo "Load fixtures"
			bin/console hautelook:fixtures:load --no-interaction

			echo "Waiting for Keycloak to be ready..."
			ATTEMPTS_LEFT_TO_REACH_KEYCLOAK=60
			until [ $ATTEMPTS_LEFT_TO_REACH_KEYCLOAK -eq 0 ] || CURL_ERROR=$(curl -s 'http://keycloak:8080/realms/demo/' 2>&1); do
				if [ $? -eq 255 ]; then
					# If the Doctrine command exits with 255, an unrecoverable error occurred
					ATTEMPTS_LEFT_TO_REACH_KEYCLOAK=0
					break
				fi
				sleep 1
				ATTEMPTS_LEFT_TO_REACH_KEYCLOAK=$((ATTEMPTS_LEFT_TO_REACH_KEYCLOAK - 1))
				echo "Still waiting for Keycloak to be ready... Or maybe Keycloak is not reachable. $ATTEMPTS_LEFT_TO_REACH_KEYCLOAK attempts left."
			done

			if [ $ATTEMPTS_LEFT_TO_REACH_KEYCLOAK -eq 0 ]; then
				echo "Keycloak is not up or not reachable:"
				echo "$CURL_ERROR"
				exit 1
			else
				echo "Keycloak is now ready and reachable"
			fi

			echo "Load Keycloak public key"
			echo "-----BEGIN PUBLIC KEY-----" > docker/keycloak/keycloak.crt
			curl -s 'http://keycloak:8080/realms/demo/' | grep -o '"public_key":"[^"]*' | grep -o '[^"]*$' >> docker/keycloak/keycloak.crt
			echo "-----END PUBLIC KEY-----" >> docker/keycloak/keycloak.crt
		fi
	fi
fi

exec docker-php-entrypoint "$@"
