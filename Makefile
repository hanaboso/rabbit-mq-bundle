.PHONY: init composer-update codesniffer phpstan phpunit

DC= docker-compose
DE= docker-compose exec php-dev

.env:
	sed -e "s|{DEV_UID}|$(shell id -u)|g" \
		-e "s|{DEV_GID}|$(shell id -u)|g" \
		.env.dist >> .env;

docker-up-force: .env
	$(DC) pull
	$(DC) up -d --force-recreate --remove-orphans

composer-update:
	$(DE) composer update

composer-install:
	$(DE) composer install

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