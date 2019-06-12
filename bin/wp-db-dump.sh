#!/usr/bin/env bash

# cwd is the root folder.
set -o allexport; source .env.testing; set +o allexport;

vendor/bin/wp db export tests/_data/dump.sql --path=${WP_ROOT_FOLDER}
