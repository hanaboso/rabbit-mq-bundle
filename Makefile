.PHONY: init-dev test

DC= docker-compose
DE= docker-compose exec -T php-dev

ALIAS?=alias
Darwin:
	sudo ifconfig lo0 $(ALIAS) $(shell awk '$$1 ~ /^DEV_IP/' .env | sed -e "s/^DEV_IP=//")
Linux:
	@echo 'skipping ...'
.lo0-up:
	-@make `uname`
.lo0-down:
	-@make `uname` ALIAS='-alias'
.env:
	sed -e "s/{DEV_UID}/$(shell if [ "$(shell uname)" = "Linux" ]; then echo $(shell id -u); else echo '1001'; fi)/g" \
		-e "s/{DEV_GID}/$(shell if [ "$(shell uname)" = "Linux" ]; then echo $(shell id -g); else echo '1001'; fi)/g" \
		.env.dist > .env; \

# Docker
docker-up-force: .env .lo0-up
	$(DC) pull
	$(DC) up -d --force-recreate --remove-orphans

docker-down-clean: .env .lo0-down
	$(DC) down -v

# Composer
composer-install:
	$(DE) composer install
	$(DE) composer update --dry-run roave/security-advisories

composer-update:
	$(DE) composer update
	$(DE) composer update --dry-run roave/security-advisories
	$(DE) composer normalize

composer-outdated:
	$(DE) composer outdated

# Console
clear-cache:
	$(DE) rm -rf var
	$(DE) php tests/bin/console cache:warmup --env=test

# App dev
init-dev: docker-up-force composer-install

phpcodesniffer:
	$(DE) ./vendor/bin/phpcs --parallel=$$(nproc) --standard=./ruleset.xml src tests

phpcodesnifferfix:
	$(DE) vendor/bin/phpcbf --parallel=$$(nproc) --standard=./ruleset.xml src tests

phpstan:
	$(DE) ./vendor/bin/phpstan analyse -c ./phpstan.neon -l 8 src tests

phpunit:
	$(DE) ./vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p 1 --runner=WrapperRunner tests

phpcoverage:
	$(DE) ./vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p 1 --coverage-html var/coverage --cache-directory var/cache/coverage --coverage-filter src tests

phpcoverage-ci:
	$(DE) ./vendor/hanaboso/php-check-utils/bin/coverage.sh -p 1 -c 96

test: docker-up-force composer-install fasttest

fasttest: clear-cache phpcodesniffer phpstan wait-for-server-start phpunit phpcoverage-ci

wait-for-server-start:
	$(DE) /bin/bash -c 'while [ $$(curl -s -o /dev/null -w "%{http_code}" http://guest:guest@rabbitmq:15672/api/overview) == 000 ]; do sleep 1; done'

benchmark: init-dev
	$(DE) tests/bin/console benchmark
	sleep 10
	$(DE) tests/bin/console rabbit_mq:consumer:my-consumer
