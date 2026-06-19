.PHONY: console
console: ## Run Symfony console, e.g. make console c='debug:router'
	docker compose run --rm app bin/console $(c)
