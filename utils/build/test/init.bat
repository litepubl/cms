@cls
@set PHP_DIR=D:\OpenServer\modules\php\PHP-7-x64\
@set require=%PHP_DIR%php.exe -d output_buffering=0 %PHP_DIR%composer.phar require %* 
@set remov=D:\OpenServer\modules\php\PHP-5.6\php.exe D:\OpenServer\modules\php\PHP-5.6\composer.phar remove
rem %require% codeception/codeception
%PHP_DIR%php.exe -d output_buffering=0 %PHP_DIR%composer.phar update
pause