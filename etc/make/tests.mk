.PHONY: test
test: ## Run unit tests
	docker compose run --rm app composer test
