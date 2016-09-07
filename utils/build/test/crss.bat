del tests\_output\*.* /f /q
del tests\_output\debug\*.* /f /q
vendor\bin\codecept.bat run api 01rssCept.php --steps --html --debug>bresult.txt