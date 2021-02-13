default: ci

ci: ci-phpcs ci-phpstan ci-phpunit ci-check-coverage

ci-phpcs:
	vendor/bin/phpcs

ci-phpstan:
	vendor/bin/phpstan analyse

ci-phpunit:
	XDEBUG_MODE=coverage vendor/bin/phpunit

ci-check-coverage:
	vendor/bin/coverage-check clover.xml 100

