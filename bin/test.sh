if [ ! -d "toml-test" ]; then
  echo "Installing test suite..."
  git clone https://github.com/BurntSushi/toml-test.git
  cd toml-test || exit
  go build ./cmd/toml-test
  cd ..
  echo "Test suite installed."
fi

echo "Running test suite..."
./toml-test/toml-test -- /usr/bin/php tests/toml.php