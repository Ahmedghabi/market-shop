#!/usr/bin/env sh
newman run tests/postman/health.collection.json -e tests/postman/env.dev.json
