DC = docker compose

.PHONY: up down restart logs test migrate pint hooks dev

## First-time setup: start containers, generate app key, run migrations
setup: up
	$(DC) exec app php artisan key:generate --ansi
	$(DC) exec app php artisan migrate

## Start all containers in detached mode
up:
	$(DC) up -d --build
	@echo ""
	@echo "  Containers are up. Run 'make dev' in a separate terminal to start the Vite dev server."
	@echo ""

## Stop and remove containers
down:
	$(DC) down

## Restart all containers
restart:
	$(DC) restart

## Tail logs (all services, or pass service=app)
logs:
	$(DC) logs -f $(service)

## Run database migrations inside the app container
migrate:
	$(DC) exec app php artisan migrate

## Run the full test suite inside the app container (pass args="--filter=name" to filter)
test:
	$(DC) exec app php artisan test $(args)

## Run Laravel Pint (fix mode) inside the app container
pint:
	$(DC) exec app vendor/bin/pint

## Start the Vite dev server locally (required when running outside Docker)
dev:
	npm run dev

## Install git hooks from .hooks/ into .git/hooks/
hooks:
	@mkdir -p .git/hooks
	@for hook in .hooks/*; do \
		[ -f "$$hook" ] || continue; \
		name=$$(basename "$$hook"); \
		cp "$$hook" ".git/hooks/$$name"; \
		chmod +x ".git/hooks/$$name"; \
		echo "Installed $$name"; \
	done
