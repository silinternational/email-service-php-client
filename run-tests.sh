#!/usr/bin/env bash

# terminate script on error
set -e

# echo commands to output
set -x

# Try to install composer dev dependencies
composer install --no-interaction --no-scripts --no-plugins

# Run the feature tests
vendor/bin/behat --stop-on-failure
