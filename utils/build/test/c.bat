del tests\_output\*.* /f /q
del tests\_output\debug\*.* /f /q
vendor\bin\codecept.bat run acceptance  --steps --html --debug>bresult.txt
@rem vendor\bin\codecept.bat run acceptance  09homeCept.php --steps --html --debug>bresult.txt
@rem vendor\bin\codecept.bat run acceptance 05AdminCept.php --steps --html --debug>bresult.txt
@rem codecept.bat run acceptance 06EditorCept.php --steps --html --debug>bresult.txt
@rem codecept.bat run api 01rssCept.php --steps --html --debug>bresult.txt
@ren vendor\bin\codecept.bat run acceptance 20uloginCept.php --steps --html --debug>bresult.txt
