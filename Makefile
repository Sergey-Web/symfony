DOCKER_COMPOSER = docker run --rm -it -u $(shell id -u):$(shell id -g) -v ./api:/app symfony-test-php-cli sh -c
DOCKER=docker compose
PHP=$(DOCKER) exec php

up: ## Create and start the services
	$(DOCKER) up -d

down: ## Stop the services
	$(DOCKER) down --remove-orphans

build: ## Build or rebuild the services
	$(DOCKER) build --no-cache --pull

php-cli:
	docker run --rm -it -u $(shell id -u):$(shell id -g) -v ./api:/app symfony-test-php-cli sh -c "$(COMMAND)"


composer:
	$(DOCKER) run --rm php-cli composer $(filter-out $@,$(MAKECMDGOALS))

%:
	@:

cli:
	$(DOCKER) run --rm php-cli sh -lc '$(CMD)'

composer-install:
	$(DOCKER_COMPOSER) "composer install"

xdebug-start: ## Start debug with Xdebug
	$(DOCKER) exec php-fpm sh -lc 'sed -i "s/^;zend_extension=/zend_extension=/g" /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini'
	$(DOCKER) restart php-fpm

xdebug-stop: ## Stop debug with Xdebug
	$(DOCKER) exec php-fpm sh -lc 'sed -i "s/^zend_extension=/;zend_extension=/g" /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini'
	$(DOCKER) restart php-fpm

php: ## Run the command in the PHP container
	$(DOCKER) exec php-fpm sh