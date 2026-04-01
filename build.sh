#!/bin/bash
TAG=$([[ -d .git && -n $(git tag --points-at HEAD) ]] && echo $(git tag --points-at HEAD) || echo $(git rev-parse --short HEAD ))
if [[ -n "$1" ]]; then
  TAG=$1
fi

# Clear previous build if set
rm -rf ./apirone apirone.${TAG}.zip && mkdir ./apirone

cp -rf ./classes ./controllers ./translations ./upgrade ./views \
  ./apirone.php  ./config.xml  ./index.php ./logo.png ./README.md ./LICENSE.txt \
  -t ./apirone

cp -rf ./vendor ./apirone/vendor
cp ./index.php ./apirone/vendor/index.php

# Run php-cs-fixer
mkdir -p tmp
composer require -q -d tmp friendsofphp/php-cs-fixer
./tmp/vendor/bin/php-cs-fixer fix ./apirone
rm -rf ./tmp

zip -r apirone.${TAG}.zip ./apirone && rm -rf ./apirone
