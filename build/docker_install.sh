#!/bin/sh

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && [[ ! -e /.dockerinit ]] && exit 0

# Where am I?
a="/$0"; a=${a%/*}; a=${a:-.}; a=${a#/}/; DIR=$(cd $a; pwd)
#DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

set -xe

# Install git (the php image doesn't have it) which is required by composer
echo -e 'http://dl-cdn.alpinelinux.org/alpine/edge/main\nhttp://dl-cdn.alpinelinux.org/alpine/edge/community\nhttp://dl-cdn.alpinelinux.org/alpine/edge/testing' > /etc/apk/repositories
apk add --no-cache \
	curl \
	git \
	postgresql-dev

# Install phpunit, the tool that we will use for testing
curl -Lo /usr/local/bin/phpunit https://phar.phpunit.de/phpunit.phar
chmod +x /usr/local/bin/phpunit

# Set up build config
rm "$DIR/../tests/settings.json.dist"
mv "$DIR/../tests/settings-ci.json" "$DIR/../tests/settings.json"

# Install mysql driver
# Here you can install any other extension that you need
docker-php-ext-install pdo pdo_mysql pdo_pgsql
#docker-php-ext-install pdo_pgsql
#docker-php-ext-install pdo_oci
#docker-php-ext-install interbase
#docker-php-ext-install pdo_firebird
