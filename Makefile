.PHONY: init composer-update codesniffer phpstan phpunit

IMAGE=cmhanaboso.azurecr.io/clever-monitor/rabbit-mq-ext-dev
BASE=cmhanaboso.azurecr.io/php-base/php:php-7.2
DC= docker-compose
DE= docker-compose exec php-dev

.env:
	sed -e "s|{DEV_UID}|$(shell id -u)|g" \
		-e "s|{DEV_GID}|$(shell id -u)|g" \
		.env.dist >> .env;

dev-build: .env
	docker pull $(BASE)
	cd docker/dev && docker build -t $(IMAGE):dev .
	cd docker/dev && docker push $(IMAGE):dev

docker-up-force: .env
	$(DC) pull
	$(DC) up -d --force-recreate --remove-orphans

composer-update:
	$(DE) composer update --ignore-platform-reqs

composer-install:
	$(DE) composer install --ignore-platform-reqs

init: docker-up-force composer-install

codesniffer:
	$(DE) ./vendor/bin/phpcs --standard=./ruleset.xml --colors -p src/ tests/

codefixer:
	$(DE) ./vendor/bin/phpcbf --standard=./ruleset.xml src/ tests/

phpstan:
	$(DE) ./vendor/bin/phpstan analyse -c phpstan.neon -l 7 src/

phpunit:
	$(DE) rm -rf ./temp/cache
	$(DE) ./vendor/bin/phpunit

test: docker-up-force composer-install codesniffer phpstan phpunit