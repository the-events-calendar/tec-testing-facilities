#!/usr/bin/env bash

# cwd is root folder
set -o allexport; source .env.testing; set +o allexport

nohup php -S localhost:${TEST_SITE_LOCALHOST_PORT} -t "${WP_ROOT_FOLDER}" > phpd.log 2>&1 &

PHP_SERVER_PID=$!

echo "${PHP_SERVER_PID}" > .php-server-pid

echo "WordPress served on http://localhost:${TEST_SITE_LOCALHOST_PORT}"