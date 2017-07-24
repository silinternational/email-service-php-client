#!/usr/bin/env bash

# Try to install composer dev dependencies
runny composer install --no-interaction --no-scripts --no-plugins

# Run the feature tests
vendor/bin/behat --stop-on-failure
