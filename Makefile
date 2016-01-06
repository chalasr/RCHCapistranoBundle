cs:
	./vendor/fabpot/php-cs-fixer/php-cs-fixer fix --verbose --config-file=.php_cs

cs_dry_run:
	./vendor/fabpot/php-cs-fixer/php-cs-fixer fix --verbose --dry-run

test:
	phpunit
