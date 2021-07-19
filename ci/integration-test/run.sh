#!/bin/bash
set -x
set -e

cd `dirname "$0"`

export BUNDLE_VENDOR=glavweb
export BUNDLE_NAME=uploader-bundle
export BUNDLE_VERSION=$(git describe --tags `git rev-list --tags --max-count=1`)
export PACKAGE_NAME="$BUNDLE_VENDOR/$BUNDLE_NAME"
export IMAGE_NAME="$BUNDLE_VENDOR-$BUNDLE_NAME-test"

docker build -t $IMAGE_NAME --build-arg BUNDLE_VERSION=$BUNDLE_VERSION --build-arg PACKAGE_NAME=$PACKAGE_NAME .

docker run --tty -i --rm \
        -v `pwd`/../../:/usr/src/bundle \
        -v `pwd`/../../build/test:/usr/src/build \
        -e PACKAGE_NAME="$PACKAGE_NAME" \
        $IMAGE_NAME \
        ../scripts/run.sh