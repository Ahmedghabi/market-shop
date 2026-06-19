.PHONY: install-hooks
install-hooks: ## Install git hooks
	cp etc/git/pre-commit .git/hooks/pre-commit
	cp etc/git/commit-msg .git/hooks/commit-msg
	chmod +x .git/hooks/pre-commit .git/hooks/commit-msg
