del tests\_output\*.* /f /q
del tests\_output\debug\*.* /f /q
@rem vendor\bin\codecept.bat run acceptance  --steps --html --debug>bresult.txt
@rem vendor\bin\codecept.bat run acceptance  09homeCept.php --steps --html --debug>bresult.txt
@rem vendor\bin\codecept.bat run acceptance 05AdminCept.php --steps --html --debug>bresult.txt
@rem codecept.bat run acceptance 06EditorCept.php --steps --html --debug>bresult.txt
vendor\bin\codecept.bat run acceptance A07AddCategoryCest.php --steps --html --debug>bresult.txt
