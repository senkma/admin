.PHONY: help build up down restart logs shell db-shell composer install prepare-db migrate cache-clear test deploy

# Docker container names
PHP_CONTAINER := $(shell docker ps -qf "name=php")
DB_CONTAINER := $(shell docker ps -qf "name=db")

help: ## Show this help message
	@echo "Invoice System - Makefile commands:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

## Docker Commands
build: ## Build Docker containers
	cd .docker && docker compose build

up: ## Start Docker containers
	cd .docker && docker compose up -d

down: ## Stop Docker containers
	cd .docker && docker compose down

restart: down up ## Restart Docker containers

logs: ## Show logs from all containers
	cd .docker && docker compose logs -f

logs-php: ## Show PHP container logs
	docker logs -f $(PHP_CONTAINER)

logs-db: ## Show database container logs
	docker logs -f $(DB_CONTAINER)

## Shell Access
shell: ## Access PHP container shell
	docker exec -it $(PHP_CONTAINER) bash

db-shell: ## Access MySQL shell
	docker exec -it $(DB_CONTAINER) mysql -u app_user -p052431 app_db

## Composer & Dependencies
composer: ## Run composer install
	docker exec -it $(PHP_CONTAINER) composer install

composer-update: ## Run composer update
	docker exec -it $(PHP_CONTAINER) composer update

install: up composer prepare-db ## Full install (up + composer + prepare-db)

## Database Commands
prepare-db: ## Prepare database (create + schema update)
	docker exec -it $(PHP_CONTAINER) composer prepare-database

migrate: ## Run database migrations
	docker exec -it $(PHP_CONTAINER) php bin/console doctrine:migrations:migrate --no-interaction

migration-create: ## Create new migration
	docker exec -it $(PHP_CONTAINER) php bin/console make:migration

migration-status: ## Show migration status
	docker exec -it $(PHP_CONTAINER) php bin/console doctrine:migrations:status

db-diff: ## Show database schema diff
	docker exec -it $(PHP_CONTAINER) php bin/console doctrine:schema:update --dump-sql

db-update: ## Update database schema (force)
	docker exec -it $(PHP_CONTAINER) php bin/console doctrine:schema:update --force --complete

db-reset: ## Reset database (drop + create + migrate)
	docker exec -it $(PHP_CONTAINER) php bin/console doctrine:database:drop --force --if-exists
	docker exec -it $(PHP_CONTAINER) php bin/console doctrine:database:create
	docker exec -it $(PHP_CONTAINER) composer prepare-database

## Symfony Commands
cache-clear: ## Clear Symfony cache
	docker exec -it $(PHP_CONTAINER) php bin/console cache:clear

cache-warmup: ## Warmup Symfony cache
	docker exec -it $(PHP_CONTAINER) php bin/console cache:warmup

console: ## Open Symfony console
	docker exec -it $(PHP_CONTAINER) php bin/console

## Code Quality
cs-fix: ## Fix code style
	docker exec -it $(PHP_CONTAINER) php vendor/bin/php-cs-fixer fix

test: ## Run tests
	docker exec -it $(PHP_CONTAINER) php bin/phpunit

## Deployment
deploy: ## Deploy to production (composer deploy)
	docker exec -it $(PHP_CONTAINER) composer deploy

## Git helpers
git-status: ## Show git status
	git status

git-push: ## Add all, commit and push
	@read -p "Commit message: " msg; \
	git add . && git commit -m "$$msg" && git push

## Cleanup
clean: ## Clean cache and logs
	rm -rf var/cache/* var/log/*
	docker exec -it $(PHP_CONTAINER) php bin/console cache:clear

prune: ## Remove unused Docker resources
	docker system prune -f

## Production helpers (remote server)
prod-logs: ## Show production logs (SSH required)
	ssh ubuntu@89.168.97.22 "docker logs -f \$$(docker ps -qf 'name=php')"

prod-shell: ## Access production PHP shell
	ssh ubuntu@89.168.97.22 "docker exec -it \$$(docker ps -qf 'name=php') bash"

prod-migrate: ## Run migrations on production
	ssh ubuntu@89.168.97.22 "docker exec -it \$$(docker ps -qf 'name=php') php bin/console doctrine:migrations:migrate --no-interaction"

prod-cache-clear: ## Clear cache on production
	ssh ubuntu@89.168.97.22 "docker exec -it \$$(docker ps -qf 'name=php') php bin/console cache:clear"

prod-db-backup: ## Backup production database
	ssh ubuntu@89.168.97.22 "docker exec \$$(docker ps -qf 'name=db') mysqldump -u app_user -p052431 app_db" > backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "Backup created: backup_$(shell date +%Y%m%d_%H%M%S).sql"
