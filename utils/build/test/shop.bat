del tests\_output\*.* /f /q
del tests\_output\debug\*.* /f /q
vendor\bin\codecept.bat run shop --steps --html --debug>bresult.txt
@rem vendor\bin\codecept.bat run shop S40BuyCest.php --steps --html --debug>bresult.txt