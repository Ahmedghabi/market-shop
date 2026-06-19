.PHONY: gen-entity
gen-entity: ## Generate Doctrine entity interactively
	docker compose run --rm app bin/console make:entity
