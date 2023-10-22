BUILD_DIR := /tmp/prestashop
SRC_DIR := $(shell pwd)
help:
	@egrep "^#" Makefile
copy:
	mkdir apirone
	cp -rf ./controllers ./apirone/controllers
	cp -rf ./sql ./apirone/sql
	cp -rf ./translations ./apirone/translations
	cp -rf ./upgrade ./apirone/upgrade
	cp -rf ./upgrade ./apirone/upgrade
	cp -rf ./views ./apirone/views
	cp -f ./apirone.php ./apirone/apirone.php
	cp -f ./config.xml ./apirone/config.xml
	cp -f ./index.php ./apirone/index.php
	cp -f ./logo.png ./apirone/logo.png
	cp -f ./Readme.md ./apirone/Readme.md

copy-vendor:

	mkdir -p ./apirone/vendor/composer
	cp -rf ./vendor/composer ./apirone/vendor/composer
	cp -rf ./vendor/autoload.php ./apirone/vendor/autoload.php

	mkdir -p ./apirone/vendor/apirone/apirone-api-php
	cp -rf ./vendor/apirone/apirone-api-php/src ./apirone/vendor/apirone/apirone-api-php/src
	cp -rf ./vendor/apirone/apirone-api-php/composer.json ./apirone/vendor/apirone/apirone-api-php/composer.json
	cp -rf ./vendor/apirone/apirone-api-php/LICENSE ./apirone/vendor/apirone/apirone-api-php/LICENSE
	cp -rf ./vendor/apirone/apirone-api-php/README.md ./apirone/vendor/apirone/apirone-api-php/README.md

	mkdir -p ./apirone/vendor/apirone/apirone-sdk-php
	cp -rf ./vendor/apirone/apirone-sdk-php/src ./apirone/vendor/apirone/apirone-sdk-php/src
	cp -rf ./vendor/apirone/apirone-sdk-php/composer.json ./apirone/vendor/apirone/apirone-sdk-php/composer.json
	cp -rf ./vendor/apirone/apirone-sdk-php/LICENSE ./apirone/vendor/apirone/apirone-sdk-php/LICENSE
	cp -rf ./vendor/apirone/apirone-sdk-php/README.md ./apirone/vendor/apirone/apirone-sdk-php/README.md

build: clean copy copy-vendor

build-zip: clean copy copy-vendor
	zip -r apirone.zip ./apirone
	rm -rf $(PWD)/apirone

# clean: Remove artifact
clean:
	rm -f apirone.zip
	rm -rf apirone

.PHONY: help build clean copy copy-vendor
