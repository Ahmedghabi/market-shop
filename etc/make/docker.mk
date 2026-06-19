.PHONY: start stop restart
start: ## Start local stack + run migrations + create super admin
	docker compose up -d
	docker compose run --rm app bin/console doctrine:migrations:migrate --no-interaction 2>/dev/null || true
	docker compose run --rm app bin/console app:create-super-admin 2>/dev/null || true

stop: ## Stop local stack
	docker compose down

restart: stop start ## Restart local stack
