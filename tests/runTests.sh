#!/bin/sh

set -e

echo 'Running unit tests.'
./bin/phpunit --configuration phpunit.xml --verbose --coverage-clover build/logs/clover.xml

echo ''
echo ''
echo ''
echo 'Testing for Coding Styling Compliance.'
echo 'All code should follow PSR standards.'
./bin/php-cs-fixer fix -vv --dry-run