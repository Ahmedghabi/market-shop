# Contributing

- Keep API contracts in `src/Dto`.
- Keep processors/providers thin and delegate business behavior to services.
- Add tests under `tests/units/src` mirroring `src` paths.
- Run `make check` before opening a merge request.
- Use the FrankenPHP `app` service for local PHP commands.
- Use OAuth2 Authorization Code + PKCE for frontend login; never use password grant in the SPA.
- Backend APIs must validate bearer JWTs against the configured JWKS issuer and audience.
