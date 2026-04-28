#!/bin/bash
if [ ! -d './vendor' ]; then
    composer install --ignore-platform-reqs
else
    composer update --ignore-platform-reqs
fi

if [ ! -d './vendor/apirone/apirone-sdk-php/src/assets' ]; then
    exit
fi

cp ./index.php ./vendor/index.php

mkdir -p ./views/img/currencies
cp ./index.php ./views/img/index.php
cp ./index.php ./views/img/currencies/index.php
cp ./vendor/apirone/apirone-sdk-php/src/assets/img/*.svg ./views/img/
cp ./vendor/apirone/apirone-sdk-php/src/assets/img/currencies/*.svg ./views/img/currencies/

cp ./vendor/apirone/apirone-sdk-php/src/assets/style.min.css ./views/css/style.min.css

mkdir -p ./views/js
cp ./index.php ./views/js/index.php
cat ./.header_stamp.txt ./vendor/apirone/apirone-sdk-php/src/assets/script.min.js > ./views/js/script.min.js

rm -fr ./vendor/apirone/apirone-sdk-php/src/assets
