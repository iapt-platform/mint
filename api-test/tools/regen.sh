#!/usr/bin/env bash
#
# 一键重新生成测试客户端：
#   全量 spec bundle → 切出 /v3 + 打补丁 → openapi-generator 生成 PHP(Guzzle) 客户端 → composer install
#
# 依赖：
#   - Java 17+（openapi-generator 是 jar）
#   - npx（openapi/ 里已装 @redocly/cli）
#   - php / composer
#
# openapi-generator-cli.jar 通过环境变量 OPENAPI_GENERATOR_JAR 指定，
# 默认 /tmp/og/openapi-generator-cli.jar。下载方式：
#   curl -L https://repo1.maven.org/maven2/org/openapitools/openapi-generator-cli/7.25.0/openapi-generator-cli-7.25.0.jar \
#     -o /tmp/og/openapi-generator-cli.jar
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
OG_JAR="${OPENAPI_GENERATOR_JAR:-/tmp/og/openapi-generator-cli.jar}"
COMPOSER_CACHE="${COMPOSER_CACHE_DIR:-/tmp/composer-cache}"

if [ ! -f "$OG_JAR" ]; then
  echo "找不到 openapi-generator-cli.jar：$OG_JAR" >&2
  echo "下载：curl -L https://repo1.maven.org/maven2/org/openapitools/openapi-generator-cli/7.25.0/openapi-generator-cli-7.25.0.jar -o /tmp/og/openapi-generator-cli.jar" >&2
  exit 1
fi

echo "1/4  bundle 全量 spec → spec/openapi.json"
(cd "$ROOT/openapi" && npx redocly bundle public/assets/protocol/main.yaml -o "$ROOT/api-test/spec/openapi.json" --ext json)

echo "2/4  切出 /v3 + 打补丁 → spec/openapi-v3.json"
php "$ROOT/api-test/tools/filter-v3.php" "$ROOT/api-test/spec/openapi.json" "$ROOT/api-test/spec/openapi-v3.json"

echo "3/4  openapi-generator 生成 PHP 客户端 → client/"
rm -rf "$ROOT/api-test/client"
java -jar "$OG_JAR" generate -g php \
  -i "$ROOT/api-test/spec/openapi-v3.json" \
  -o "$ROOT/api-test/client" \
  --additional-properties=invokerPackage=WikipaliApi,packageName=wikipali/api-client,artifactVersion=1.0.0

# 清掉 openapi-generator 的样板文件（.openapi-generator-ignore 只能防止覆盖已编辑文件，
# 不能阻止生成，所以这里生成后直接删，只留 lib/ + composer.json）
rm -rf \
  "$ROOT/api-test/client/docs" \
  "$ROOT/api-test/client/test" \
  "$ROOT/api-test/client/README.md" \
  "$ROOT/api-test/client/.travis.yml" \
  "$ROOT/api-test/client/phpunit.xml.dist" \
  "$ROOT/api-test/client/.php-cs-fixer.dist.php" \
  "$ROOT/api-test/client/git_push.sh"

echo "4/4  composer install（Guzzle）"
(cd "$ROOT/api-test/client" && COMPOSER_CACHE_DIR="$COMPOSER_CACHE" composer install --no-dev --no-interaction --no-progress)

echo "完成。运行：php api-test/run.php --server=local"
