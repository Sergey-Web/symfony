DOCKER_COMPOSE=docker compose

up: ## Create and start the services
	$(DOCKER_COMPOSE) up -d

down: ## Stop the services
	$(DOCKER_COMPOSE) down --remove-orphans

build: ## Build or rebuild the services
	$(DOCKER_COMPOSE) build --no-cache --pull

composer:
	$(DOCKER_COMPOSE) run --rm php-cli $(MAKECMDGOALS)

php: ## Run the command in the PHP container
	$(DOCKER_COMPOSE) exec php-fpm bash || true

symfony-console:
	$(DOCKER_COMPOSE) run --rm php-cli php bin/console $(filter-out $@,$(MAKECMDGOALS))

%:
	@:

test:
	$(DOCKER_COMPOSE) run --rm php-cli php bin/phpunit

xdebug-on:
	echo "zend_extension=xdebug" > docker/dev/php/conf.d/docker-php-ext-xdebug.ini
	docker compose restart php-fpm php-cli

xdebug-off:
	echo "; xdebug disabled" > docker/dev/php/conf.d/docker-php-ext-xdebug.ini
	docker compose restart php-fpm php-cli

cache-clear:
	$(DOCKER_COMPOSE) run --rm php-cli sh -c "php bin/console cache:clear && php bin/console doctrine:cache:clear-metadata && php bin/console doctrine:cache:clear-query && php bin/console doctrine:cache:clear-result"

migrate:
	$(DOCKER_COMPOSE) run --rm php-cli php bin/console doctrine:migrations:migrate -n