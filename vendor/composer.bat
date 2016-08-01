@cls
@set PHP_DIR=D:\OpenServer\modules\php\PHP-7-x64\
@set require=%PHP_DIR%php.exe -d output_buffering=0 %PHP_DIR%composer.phar require %* 
@set remov=%PHP_DIR%php.exe -d output_buffering=0 %PHP_DIR%composer.phar remove %* 
%PHP_DIR%php.exe -d output_buffering=0 %PHP_DIR%composer.phar update
pause
exit
rem %PHP_DIR%php.exe -d output_buffering=0 %PHP_DIR%composer.phar update
%require% "monolog/monolog"
%require% "psr/cache"
%require% "psr/http-message"
%require% "zendframework/zend-diactoros"
pause