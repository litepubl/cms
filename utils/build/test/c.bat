rem set path=%path%;D:\OpenServer\modules\php\PHP-7-x64\
@cls
rem codecept.bat run acceptance  --steps --html --debug>bresult.txt
codecept.bat run acceptance 05AdminCept.php --steps --html --debug>bresult.txt
