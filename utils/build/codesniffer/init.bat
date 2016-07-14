@set PHP_DIR=D:\OpenServer\modules\php\PHP-7-x64\
@set require=%PHP_DIR%php.exe -d output_buffering=0 %PHP_DIR%composer.phar require %* 
rem %require% "squizlabs/php_codesniffer=*"
%PHP_DIR%php.exe -d output_buffering=0 %PHP_DIR%composer.phar update