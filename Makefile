default: ci

ci: ci-phpcs ci-phpstan ci-phpunit ci-check-coverage

ci-phpcs:
	composer exec phpcs

ci-phpstan:
	composer exec phpstan analyse

ci-phpunit:
	XDEBUG_MODE=coverage composer exec phpunit

ci-check-coverage:
	composer exec coverage-check clover.xml 100

