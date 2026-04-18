#!/bin/bash

set -e

git config --global --add safe.directory $PWD

composer require guzzlehttp/guzzle
composer require opensearch-project/opensearch-php
composer require phpoffice/phpspreadsheet
composer require aws/aws-sdk-php
composer require firebase/php-jwt
composer require casbin/casbin

npm install --save \
    bootstrap bulma @tabler/core @material/web \
    @fortawesome/fontawesome-free dayjs \
    marked dompurify jsdom
exit 0
