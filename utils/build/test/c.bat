rem set path=%path%;D:\OpenServer\modules\php\PHP-7-x64\
@cls
codecept.bat run acceptance 03PasswordCept.php --steps --html --debug>bresult.txt
