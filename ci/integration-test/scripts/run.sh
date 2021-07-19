#!/bin/bash
set -x
set -e

composer require -n $PACKAGE_NAME

php bin/phpunit

../scripts/copy.sh
