@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
@set PHP_DIR=D:\OpenServer\modules\php\PHP-7-x64\
SET BIN_TARGET=%~dp0/vendor/codeception/codeception/codecept
%php_dir%php.exe "%BIN_TARGET%" %*