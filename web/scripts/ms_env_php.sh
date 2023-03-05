#!/bin/bash

if [[ -n ${PHP_INI_DEVELOPMENT+development} ]]; then
    echo "Using development ini"
    ENV_TYPE="development"
else
    echo "Using production ini"
    ENV_TYPE="production"
fi
mv "$PHP_INI_DIR/php.ini-$ENV_TYPE" "$PHP_INI_DIR/php.ini"