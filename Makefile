.PHONY: init composer-update codesniffer phpstan phpunit

DC= docker-compose
DE= docker-compose exec php-dev

.env:
	sed -e "s|{DEV_UID}|$(shell id -u)|g" \
		-e "s|{DEV_GID}|$(shell id -u)|g" \
		.env.dist >> .env;

composer-update:
	$(DE) composer update

init: .env
	$(DC) pull
	$(DC) up -d --force-recreate
	$(DE) composer install

codesniffer:
	$(DE) ./vendor/bin/phpcs --standard=./ruleset.xml --colors -p src/ tests/

codefixer:
	$(DE) ./vendor/bin/phpcbf --standard=./ruleset.xml src/ tests/

phpstan:
	$(DE) ./vendor/bin/phpstan analyse -c phpstan.neon -l 7 src/ tests/

phpunit:
	$(DE) rm -rf ./temp/cache
	$(DE) ./vendor/bin/phpunit

test: codesniffer phpstan phpunit