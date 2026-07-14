#!/usr/bin/env sh

set -eu

BASE_URL=${BASE_URL:-http://localhost:8082}
SHOP_HOST=${SHOP_HOST:-demo-hanooti.localhost}

request_status() {
    path=$1
    shift
    curl -sS -o /dev/null -w '%{http_code}' "$@" "${BASE_URL}${path}"
}

assert_status() {
    expected=$1
    path=$2
    shift 2
    actual=$(request_status "$path" "$@")
    if [ "$actual" != "$expected" ]; then
        printf 'FAIL %s expected=%s actual=%s\n' "$path" "$expected" "$actual" >&2
        exit 1
    fi
    printf 'PASS %s %s\n' "$actual" "$path"
}

public_paths='
/api/products
/api/categories
/api/brands
/api/cart
/api/reviews
/api/promotions
/api/coupons
/api/delivery-rules
/api/payment-methods
/api/cms/pages
/api/announcements
/api/filters
/api/media
/api/sponsors
/api/themes
/api/settings
/api/menus
/api/frontend-bootstrap
/api/routes
/api/reference/countries
/api/reference/governorates
/api/reference/localities
/api/products/feed.xml
/products/feed.xml'

for path in $public_paths; do
    assert_status 200 "$path" -H "Host: ${SHOP_HOST}"
done

assert_status 401 /api/admin/subscription-plans -H "Host: ${SHOP_HOST}"
assert_status 401 /api/admin/platform-modules -H "Host: ${SHOP_HOST}"
assert_status 401 /api/admin/webhooks -H "Host: ${SHOP_HOST}"
assert_status 401 /api/account/orders -H "Host: ${SHOP_HOST}"

assert_status 401 /api/auth/login \
    -H 'Content-Type: application/json' \
    -d '{"email":"invalid@example.test","password":"invalid"}'

if [ -n "${ADMIN_EMAIL:-}" ] && [ -n "${ADMIN_PASSWORD:-}" ]; then
    token=$(curl -sS \
        -H 'Content-Type: application/json' \
        -d "{\"email\":\"${ADMIN_EMAIL}\",\"password\":\"${ADMIN_PASSWORD}\"}" \
        "${BASE_URL}/api/auth/login" | python3 -c 'import json, sys; print(json.load(sys.stdin)["accessToken"])')

    assert_status 200 /api/admin/subscription-plans \
        -H "Authorization: Bearer ${token}"
fi

printf 'API smoke tests passed.\n'
