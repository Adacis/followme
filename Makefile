app_name := followme
appstore_build_directory := $(CURDIR)/build/artifacts/appstore

.PHONY: all build check composer test appstore source clean
all: build
build: check

# The browser code is native JavaScript; no bundle/compiler is required.
check:
	node --check js/script.js
	node --check js/admin.js

composer:
	composer install --prefer-dist --no-interaction

test:
	vendor/bin/phpunit -c phpunit.xml

appstore:
	mkdir -p $(appstore_build_directory)
	tar czf $(appstore_build_directory)/$(app_name).tar.gz \
		--exclude-vcs --exclude='$(app_name)/build' \
		--exclude='$(app_name)/vendor' --exclude='$(app_name)/node_modules' \
		--exclude='$(app_name)/tests' --exclude='$(app_name)/.github' \
		--exclude='$(app_name)/.*' --exclude='$(app_name)/composer.*' \
		--exclude='$(app_name)/package*.json' --exclude='$(app_name)/phpunit*.xml' \
		--exclude='$(app_name)/Makefile' --exclude='$(app_name)/_travis.yml' \
		--exclude='$(app_name)/css/jam-master' \
		-C .. $(app_name)

source: appstore
	mkdir -p build/artifacts/source
	git archive --format=tar.gz --prefix=$(app_name)/ HEAD > build/artifacts/source/$(app_name).tar.gz

clean:
	rm -rf build
