php %~dp0codesniffer/vendor/squizlabs/php_codesniffer/scripts/phpcbf %~dp0../../plugins --ignore=*/plugins/markdown/Markdown.php,*/plugins/sape/sape.php,*/plugins/sourcefiles/geshi* --report-file=report.txt --no-patch=true --default_standard=PSR2 --encoding=utf-8 --extensions=php 
pause