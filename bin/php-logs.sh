#!/usr/bin/env bash

LOG="$(php -i | grep ^error_log | cut -f3 -d ' ')"

if [[ ${LOG} -ne "no" ]]; then
    tail -f ${LOG}
else
    echo "The PHP installation does not seem to be configured to log errors; is error_log set in the PHP ini file?"
fi
