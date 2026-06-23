#!/bin/bash
set -x
set -e

composer require -n oneup/flysystem-bundle $PACKAGE_NAME:dev-main \
&& php bin/phpunit \
|| echo 'FAILED'

../scripts/copy.sh
