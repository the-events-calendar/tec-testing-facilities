#!/usr/bin/env bash

# Get last PHP server PID
PHP_SERVER_PID="$(cat .php-server-pid)"

if [[ ${PHP_SERVER_PID} ]]; then
    kill ${PHP_SERVER_PID}
    rm .php-server-pid
fi
