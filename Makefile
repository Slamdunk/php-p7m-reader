PHP_BIN=php8.1
COMPOSER_BIN=$(shell command -v composer)

all: csfix static-analysis test
	@echo "Done."

vendor: composer.json
	${PHP_BIN} ${COMPOSER_BIN} update
	touch vendor

.PHONY: csfix
csfix: vendor
	${PHP_BIN} vendor/bin/php-cs-fixer fix --verbose

.PHONY: static-analysis
static-analysis: vendor
	${PHP_BIN} vendor/bin/phpstan analyse

.PHONY: test
test: vendor
	${PHP_BIN} -d zend.assertions=1 vendor/bin/phpunit
