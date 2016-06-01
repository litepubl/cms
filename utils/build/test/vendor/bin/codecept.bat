@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/codeception/codeception/codecept
php "%BIN_TARGET%" %*
