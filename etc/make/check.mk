.PHONY: check lint phpstan cs-check
check: cs-check phpstan test ## Run all checks

lint: cs-check ## Alias for style checks

cs-check: ## Check PHP style
	docker compose run --rm app composer cs-check

phpstan: ## Run PHPStan
	docker compose run --rm app composer phpstan
