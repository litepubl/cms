del tests\_output\*.* /f /q
del tests\_output\debug\*.* /f /q
@rem vendor\bin\codecept.bat run shop --steps --html --debug>bresult.txt
vendor\bin\codecept.bat run shop S21EditorCest.php --steps --html --debug>bresult.txt