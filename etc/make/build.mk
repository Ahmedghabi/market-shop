.PHONY: install build
install: ## Install backend and frontend dependencies
	docker compose run --rm app composer install
	npm install

build: ## Build frontend assets
	npm run build
