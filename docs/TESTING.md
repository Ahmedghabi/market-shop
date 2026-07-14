# Testing guide

## Local prerequisites

Start the application and its database first:

```bash
docker compose up -d database redis app
```

For functional API tests, initialize the isolated `app_test` database. The
repository contains historical migrations that assume a pre-existing baseline,
so local test setup uses the current Doctrine mapping:

```bash
docker compose run --rm -e APP_ENV=test app \
  php bin/console doctrine:database:create --if-not-exists
docker compose run --rm -e APP_ENV=test app \
  php bin/console doctrine:schema:create --no-interaction
docker compose run --rm -e APP_ENV=test app \
  php bin/console doctrine:fixtures:load --no-interaction
```

The fixture credentials are:

- boutique admin: `owner.demo-hanooti@hanooti.local` / `password123`
- customer: `account.client0.demo-hanooti@example.test` / `password123`

## Test commands

```bash
# Fast unit suite
docker compose run --rm app composer test:unit

# API Platform functional CRUD and isolation tests
docker compose run --rm -e APP_ENV=test app composer test:functional

# Static Symfony checks
docker compose run --rm app composer cs-check
docker compose run --rm app php bin/console lint:container --env=test
docker compose run --rm app php bin/console doctrine:schema:validate --skip-sync --env=test

# API route smoke checks
tests/api/smoke.sh

# Frontend checks
npm run typecheck
npm run build

# Playwright E2E
npx playwright install chromium
BASE_URL=http://demo-hanooti.localhost:8082 \
E2E_ADMIN_EMAIL=super-admin@market-shop.local \
E2E_ADMIN_PASSWORD=123456 \
npm run e2e -- --project=chromium

# Coverage gate (currently intentionally fails until the application reaches 80%)
docker compose run --rm app composer test:coverage
```

## Coverage policy

Coverage is generated in `coverage/` and the CI gate is 80% line coverage.
Coverage includes `src/`, unit tests, and functional API tests. Smoke tests and
E2E tests provide behavioral coverage but are not counted as PHP line coverage.

## E2E policy

- Tests use semantic labels and stable route-level assertions.
- CI retries failed tests twice and stores HTML, JUnit, screenshots, videos, and
  traces.
- External payment and delivery providers must be mocked or configured against
  a sandbox. Tests must never use production credentials.
- Authenticated E2E tests require `E2E_ADMIN_EMAIL` and
  `E2E_ADMIN_PASSWORD`; without them only public journeys run.

## Isolation policy

Every functional test must use a unique resource name and clean up resources in
`finally` blocks. Cross-boutique tests must use a second boutique host and must
assert `403` or `404` for objects owned by another boutique.
