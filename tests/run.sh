#!/usr/bin/env bash

set -e

if ! command -v toml-test &> /dev/null; then
    echo "toml-test is not installed."
    echo "See https://github.com/toml-lang/toml-test for installation instructions."
    exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "Running decoder tests..."
toml-test test -decoder "php ${SCRIPT_DIR}/toml-decoder.php"

echo ""
echo "Running encoder tests..."
toml-test test -decoder "php ${SCRIPT_DIR}/toml-decoder.php" -encoder "php ${SCRIPT_DIR}/toml-encoder.php"
