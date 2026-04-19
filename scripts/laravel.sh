#!/bin/bash

set -e

git config --global --add safe.directory "$(dirname -- "$(realpath -- "$PWD")")"

composer8.5 require --no-scripts \
    guzzlehttp/guzzle opensearch-project/opensearch-php phpoffice/phpspreadsheet aws/aws-sdk-php firebase/php-jwt casbin/casbin

npm install --save \
    bootstrap bulma @material/web \
    @tabler/core @tabler/icons @tabler/icons-webfont \
    @fortawesome/fontawesome-free dayjs \
    marked dompurify jsdom

echo 'done.'
exit 0
