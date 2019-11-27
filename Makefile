.PHONY: init composer-update codesniffer phpstan phpunit

DC= docker-compose
DE= docker-compose exec -T php-dev

.env:
	sed -e "s|{DEV_UID}|$(shell id -u)|g" \
		-e "s|{DEV_GID}|$(shell id -u)|g" \
		-e "s/{SSH_AUTH}/$(shell if [ "$(shell uname)" = "Linux" ]; then echo "\/tmp\/.ssh-auth-sock"; else echo '\/tmp\/.nope'; fi)/g" \
		.env.dist >> .env;

# Docker
docker-up-force: .env
	$(DC) pull
	$(DC) up -d --force-recreate --remove-orphans

docker-down-clean: .env
	$(DC) down -v

# Composer
composer-install:
	$(DE) composer install --ignore-platform-reqs

composer-update:
	$(DE) composer update --ignore-platform-reqs

composer-outdated:
	$(DE) composer outdated

# Console
clear-cache:
	$(DE) rm -rf var/cache

# App dev
init-dev: docker-up-force composer-install

codesniffer:
	$(DE) ./vendor/bin/phpcs --standard=./ruleset.xml --colors -p src/ tests/

phpstan:
	$(DE) ./vendor/bin/phpstan analyse -c ./phpstan.neon -l 7 src/ tests/

phpunit:
	$(DE) ./vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p 4 --runner=WrapperRunner tests

test: docker-up-force composer-install fasttest

fasttest: clear-cache codesniffer phpstan phpunit
