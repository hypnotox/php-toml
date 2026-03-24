#!/usr/bin/env bash

if ! command -v toml-test &> /dev/null; then
    echo "toml-test is not installed."
    echo "See https://github.com/toml-lang/toml-test for installation instructions."
    exit 1
fi

echo "Running TOML test suite..."
toml-test test -decoder "php tests/toml.php"
