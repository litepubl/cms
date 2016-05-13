rem set path=%path%;D:\OpenServer\modules\php\PHP-7-x64\
@cls
codecept.bat run acceptance  --steps --html --debug>bresult.txt
rem codecept.bat run acceptance 05AdminCept.php --steps --html --debug>bresult.txt
rem codecept.bat run acceptance 06EditorCept.php --steps --html --debug>bresult.txt
