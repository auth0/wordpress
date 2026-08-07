#!/bin/bash

# This script is used to build and package the plugin for distribution.
# It will clean up the environment, execute Composer, prefix dependencies, finalize the build, archive the build, and sign the archive.

function trim() {
    local str="$*"
    str="${str#"${str%%[![:space:]]*}"}"
    str="${str%"${str##*[![:space:]]}"}"
    echo "${str}"
}

function semver {
  local SEMVER_REGEX='^([0-9]+\.){2}(\*|[0-9]+)(-.*)?$'
  local version=$(trim $1)

  if [[ "$version" =~ $SEMVER_REGEX ]]; then
    if [ "$#" -eq 2 ]; then
      local major=${BASH_REMATCH[0]}
      local minor=${BASH_REMATCH[1]}
      local patch=${BASH_REMATCH[2]}
      local suffix=${BASH_REMATCH[3]}
      eval "$2=(\"$major\" \"$minor\" \"$patch\" \"$suffix\")"
    fi
  else
    echo "Error: version '$version' does not match the semver 'X.Y.Z' format."
    exit 1
  fi
}

printf 'What version are you building? '
read version
echo "Version: $version"
semver "$version" version
filename="Auth0_WordPress_${version}.zip"

PLUGIN_NAME="wp-auth0"

echo "# Cleaning up environment..."
rm -f build.zip
rm -f build.zip.sig
rm -rf build
rm -rf ${PLUGIN_NAME}
rm -rf vendor
rm -f composer.lock

echo "# Executing Composer..."
composer update --no-plugins

echo "# Prefixing Dependencies..."
php -d memory_limit=512M vendor/bin/php-scoper add-prefix --force

echo "# Copying plugin entry file..."
cp wpAuth0.php build/wpAuth0.php

echo "# Finalizing Build..."
cd build
composer update --no-dev --optimize-autoloader --no-plugins
rm composer.json
rm composer.lock
cd ..

echo "# Verifying scoped build (checking for incorrectly prefixed WordPress globals)..."
if grep -rn 'Auth0\\WordPress\\Vendor\\WP_' build/src/; then
    echo ""
    echo "ERROR: php-scoper has incorrectly prefixed WordPress global classes!"
    echo "The above files contain 'Auth0\\WordPress\\Vendor\\WP_*' references that will cause fatal errors."
    echo "Check scoper.inc.php expose-global-classes setting."
    exit 1
fi
echo "  No incorrectly prefixed WordPress globals found. Build OK."

echo "# Renaming build folder to ${PLUGIN_NAME}..."
mv build ${PLUGIN_NAME}

echo "# Archiving Build..."
zip -vr ${filename} ${PLUGIN_NAME}/ -x "*.DS_Store"

echo "# Signing Build..."
openssl dgst -sign private-signing-key.pem -sha256 -out ${filename}.sig -binary ${filename}
