#!/usr/bin/env bash
# Run from the Lab 7.2.0 root: bash tests/run-all.sh
set -euo pipefail
cd "$(dirname "$0")/.."
echo '== PHP syntax lint =='
while IFS= read -r -d '' script; do php -l "$script" >/dev/null; done < <(find . -name '*.php' -print0)
echo 'All PHP files passed syntax lint.'
echo '== Check PDO SQLite =='
php -r 'if (!in_array("sqlite", PDO::getAvailableDrivers(), true)) { fwrite(STDERR, "ERROR: pdo_sqlite missing; install/enable the PHP SQLite extension before testing.\n"); exit(2); }'
echo '== Lab 6 form validation regression =='
php tests/validation-tests.php
echo
echo '== Lab 6 request persistence and filtering regression =='
php tests/integration-tests.php
echo '== Lab 7 UM-01..UM-10 acceptance and security checks =='
php tests/user-management-tests.php
