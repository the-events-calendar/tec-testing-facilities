#!/usr/bin/env bash

# cwd is the root folder.
set -o allexport; source .env.testing; set +o allexport;

vendor/bin/wp config  create --path="${WP_ROOT_FOLDER}" \
    --dbname=${TEST_SITE_DB_NAME} \
    --dbuser=${TEST_SITE_DB_USER} \
    --dbpass=${TEST_SITE_DB_PASSWORD} \
    --dbhost=${TEST_SITE_DB_HOST} \
    --dbprefix=${TEST_SITE_DB_PREFIX} \
    --skip-salts \
    --skip-check \
    --force=y \

vendor/bin/wp core install --path="${WP_ROOT_FOLDER}" \
    --url=${TEST_SITE_WP_URL} \
    --title=Test \
    --admin_user=${TEST_SITE_ADMIN_USERNAME} \
    --admin_password=${TEST_SITE_ADMIN_PASSWORD} \
    --admin_email=${TEST_SITE_ADMIN_EMAIL} \
    --skip-email=y

rm -rf "${WP_ROOT_FOLDER}/wp-content/plugins"

parent_dir="$(php -r 'echo dirname( __DIR__ );')"
ln -s "${parent_dir}" "${WP_ROOT_FOLDER}/wp-content/plugins"
