.PHONY: db-migrate db-diff
db-migrate: ## Run database migrations
	docker compose run --rm app bin/console doctrine:migrations:migrate --no-interaction

db-diff: ## Generate a migration diff
	docker compose run --rm app bin/console doctrine:migrations:diff
