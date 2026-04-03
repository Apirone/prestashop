# TAG := $(shell test -d .git && git tag --points-at HEAD) || "dev"

.PHONY: build init assets vendor help

build: ## Create apirone module artifact.
	@ /bin/bash ./build.sh

init: vendor assets ## Install vendor & update assets

vendor: ## Install or update vendor dependencies
	@if [ ! -d './vendor' ]; then \
		composer install --ignore-platform-reqs; \
	else \
		composer update --ignore-platform-reqs; \
	fi

assets: ## Update assets from apirone-sdk-php library
	@echo "Updating assets..."
	@rm -rf ./views/js/*.js && rm -rf ./views/img/*.svg && rm -rf ./views/css/front.css
	@cat ./.header_stamp.txt ./vendor/apirone/apirone-sdk-php/src/assets/js/script.min.js > ./views/js/front.js
	@cp ./vendor/apirone/apirone-sdk-php/src/assets/css/styles.min.css ./views/css/front.css
	@cp ./vendor/apirone/apirone-sdk-php/src/assets/css/icons/*.svg ./views/img
	@cp ./vendor/apirone/apirone-sdk-php/src/assets/css/icons/crypto/*.svg ./views/img
	@sed -i 's|icons/crypto/|../img/|g' ./views/css/front.css
	@sed -i 's|icons/|../img/|g' ./views/css/front.css
	@echo 'Done'

help: ## This help screen
	@echo
	@echo 'Make targets:'
	@echo
	@cat $(realpath $(firstword $(MAKEFILE_LIST))) | \
		sed -n -E 's/^([^.][^: ]+)\s*:(([^=#]*##\s*(.*[^[:space:]])\s*)|[^=].*)$$/    \1	\4/p' | \
		expand -t15
	@echo
