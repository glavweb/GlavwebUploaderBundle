#!/bin/bash
set -x
set -e

cd `dirname "$0"`

export BUNDLE_VENDOR=glavweb
export BUNDLE_NAME=uploader-bundle
export PACKAGE_NAME="$BUNDLE_VENDOR/$BUNDLE_NAME"
export IMAGE_NAME="$BUNDLE_VENDOR-$BUNDLE_NAME-test"

docker build -t $IMAGE_NAME .

docker run --tty --rm \
        -v `pwd`/../../:/usr/src/bundle \
        -v `pwd`/../../build/test:/usr/src/build \
        -v `pwd`/../../build/.composer-cache:/root/.composer/cache \
        -e PACKAGE_NAME="$PACKAGE_NAME" \
        $IMAGE_NAME \
        ../scripts/run.sh