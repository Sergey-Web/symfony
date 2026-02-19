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
	$(DOCKER_COMPOSE) exec php-fpm bash