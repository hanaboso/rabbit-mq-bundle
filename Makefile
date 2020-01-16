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
	$(DE) composer install --no-suggest

composer-update:
	$(DE) composer update --no-suggest

composer-outdated:
	$(DE) composer outdated

# Console
clear-cache:
	$(DE) rm -rf var/log
	$(DE) php tests/bin/console cache:clear --env=test
	$(DE) php tests/bin/console cache:warmup --env=test

# App dev
init-dev: docker-up-force composer-install

codesniffer:
	$(DE) vendor/bin/phpcs --standard=ruleset.xml src tests

phpstan:
	$(DE) vendor/bin/phpstan analyse -c phpstan.neon -l 8 src tests

phpunit:
	$(DE) vendor/bin/paratest -c vendor/hanaboso/php-check-utils/phpunit.xml.dist -p 1 --runner=WrapperRunner tests

phpcoverage:
	$(DE) vendor/bin/paratest -c vendor/hanaboso/php-check-utils/phpunit.xml.dist -p 1 --coverage-html var/coverage --whitelist src tests

phpcoverage-ci:
	$(DE) ./vendor/hanaboso/php-check-utils/bin/coverage.sh -c 100 -p 1

test: docker-up-force composer-install fasttest

fasttest: clear-cache codesniffer phpstan wait-for-server-start phpunit phpcoverage-ci

wait-for-server-start:
	$(DE) /bin/bash -c 'while [ $$(curl -s -o /dev/null -w "%{http_code}" http://guest:guest@rabbitmq:15672/api/overview) == 000 ]; do sleep 1; done'

benchmark: init-dev
	$(DE) tests/bin/console benchmark
	sleep 10
	$(DE) tests/bin/console rabbit_mq:consumer:my-consumer
