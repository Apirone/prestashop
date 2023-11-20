
##
# Makefile to help build module artifact
#
# Built on list_targets-Makefile:
#
#     https://gist.github.com/zaytseff/3c874a02b6e3db16c3ffa8406600060c
#

.PHONY: help build build-zip zip clear clean copy copy-vendor fix init targets assets vendor

ME := $(realpath $(firstword $(MAKEFILE_LIST)))

help: targets ## This help screen

build: assets clean copy copy-vendor fix ## Create apirone module artifact

fix: ## php-cs-fixer artifact runner
	@if [ ! -d './apirone' ]; then \
		echo 'Run make build before'; \
		exit 1; \
	fi

	@mkdir -p tools/php-cs-fixer
	@composer require -q -d tools/php-cs-fixer friendsofphp/php-cs-fixer
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix ./apirone
	@rm -rf ./tools

zip: ## Create artifact archive
	@if [ ! -d './apirone' ]; then \
		echo 'Run make build before'; \
		exit 1; \
	else \
		rm -rf ./apirone.zip; \
		zip -r apirone.zip ./apirone; \
	fi
build-zip: build zip clean ## Clean, build and zip

clear: clean ## see clean

clean: ## Remove artifact
	rm -f apirone.zip
	rm -rf apirone

targets:
	@echo
	@echo "Make targets:"
	@echo
	@cat $(ME) | \
	sed -n -E 's/^([^.][^: ]+)\s*:(([^=#]*##\s*(.*[^[:space:]])\s*)|[^=].*)$$/    \1	\4/p' | \
	sort -u | \
	expand -t15
	@echo

init: vendor assets ## Install vendor & update assets

# Install vendor dependencies	
vendor:
	composer install

# Update assets from apirone-sdk-php library
assets:
	rm -rf ./views/js/*.js
	rm -rf ./views/img/*.svg
	rm -rf ./views/css/*.css
	# cp ./vendor/apirone/apirone-sdk-php/src/assets/js/script.min.js ./views/js/front.js
	cat ./.header_stamp.txt ./vendor/apirone/apirone-sdk-php/src/assets/js/script.min.js > ./views/js/front.js
	cp ./vendor/apirone/apirone-sdk-php/src/assets/css/styles.min.css ./views/css/front.css
	cp ./vendor/apirone/apirone-sdk-php/src/assets/css/icons/*.svg ./views/img
	cp ./vendor/apirone/apirone-sdk-php/src/assets/css/icons/crypto/*.svg ./views/img
	sed -i 's|icons/crypto/|../img/|g' ./views/css/front.css
	sed -i 's|icons/|../img/|g' ./views/css/front.css

#Copy module fiiles into apirone folder
copy:
	mkdir apirone
	cp -rf ./controllers ./apirone/controllers
	cp -rf ./translations ./apirone/translations
	cp -rf ./upgrade ./apirone/upgrade
	cp -rf ./upgrade ./apirone/upgrade
	cp -rf ./views ./apirone/views
	cp -f ./apirone.php ./apirone/apirone.php
	cp -f ./config.xml ./apirone/config.xml
	cp -f ./index.php ./apirone/index.php
	cp -f ./logo.png ./apirone/logo.png
	cp -f ./Readme.md ./apirone/Readme.md

#Copy vendor libraries into apirone/vendor folder
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

	cp ./index.php ./apirone/vendor/index.php

