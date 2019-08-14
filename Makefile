.PHONY: init composer-update codesniffer phpstan phpunit

IMAGE=dkr.hanaboso.net/hanaboso/rabbit-mq-bundle/dev:dev
BASE=dkr.hanaboso.net/hanaboso/php-base:php-7.3
DC= docker-compose
DE= docker-compose exec php-dev

.env:
	sed -e "s|{DEV_UID}|$(shell id -u)|g" \
		-e "s|{DEV_GID}|$(shell id -u)|g" \
		.env.dist >> .env;

# Docker
dev-build: .env
	docker pull $(BASE)
	cd docker/dev && docker build -t $(IMAGE) .
	docker push $(IMAGE)

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
	$(DE) sudo rm -rf var/cache

# App dev
init-dev: docker-up-force composer-install

codesniffer:
	$(DE) ./vendor/bin/phpcs --standard=./ruleset.xml --colors -p src/ tests/

phpstan:
	$(DE) ./vendor/bin/phpstan analyse -c ./phpstan.neon -l 7 src/ tests/

phpunit:
	$(DE) ./vendor/bin/phpunit -c phpunit.xml.dist --colors --stderr tests

test: docker-up-force composer-install fasttest

fasttest: clear-cache codesniffer phpstan phpunit
