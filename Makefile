.PHONY: build init help

build: ## Create apirone module artifact
	@ /bin/bash ./build.sh

init: ## Install or update vendor dependencies
	@ /bin/bash ./vendor_update.sh

help: ## This help screen
	@echo
	@echo 'Make targets:'
	@echo
	@cat $(realpath $(firstword $(MAKEFILE_LIST))) | \
		sed -n -E 's/^([^.][^: ]+)\s*:(([^=#]*##\s*(.*[^[:space:]])\s*)|[^=].*)$$/    \1	\4/p' | \
		expand -t15
	@echo
