php %~dp0codesniffer/vendor/squizlabs/php_codesniffer/scripts/phpcbf %~dp0test/tests/acceptance %~dp0test/tests/api %~dp0test/tests/_support/page %~dp0test/tests/_support/test --report-file=report.txt --no-patch=true --default_standard=PSR2 --encoding=utf-8 --extensions=php 
pause