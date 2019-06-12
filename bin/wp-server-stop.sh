#!/usr/bin/env bash

# Get last PHP server PID

if [[ ! -f .php-server-pid ]]; then
    echo ".php-server-pid file not found; is the server running?"
    exit 0
fi

PHP_SERVER_PID="$(cat .php-server-pid)"

if [[ ${PHP_SERVER_PID} ]]; then
    kill ${PHP_SERVER_PID}
    rm .php-server-pid
fi
