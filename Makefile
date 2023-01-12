PHP_BIN=php8.2
COMPOSER_BIN=$(shell command -v composer)

all: csfix static-analysis test
	@echo "Done."

vendor: composer.json
	$(PHP_BIN) $(COMPOSER_BIN) update
	$(PHP_BIN) $(COMPOSER_BIN) bump
	touch vendor

.PHONY: csfix
csfix: vendor
	PHP_CS_FIXER_IGNORE_ENV=1 $(PHP_BIN) vendor/bin/php-cs-fixer fix --verbose

.PHONY: static-analysis
static-analysis: vendor
	$(PHP_BIN) vendor/bin/phpstan analyse

.PHONY: test
test: vendor
	$(PHP_BIN) -d zend.assertions=1 vendor/bin/phpunit
