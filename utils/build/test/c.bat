del tests\_output\*.* /fq
@rem codecept.bat run acceptance  --steps --html --debug>bresult.txt
@rem codecept.bat run acceptance  03PasswordCept.php --steps --html --debug>bresult.txt
@rem codecept.bat run acceptance  07AddCategoryCept.php --steps --html --debug>bresult.txt
@rem codecept.bat run acceptance 05AdminCept.php --steps --html --debug>bresult.txt
@rem codecept.bat run acceptance 06EditorCept.php --steps --html --debug>bresult.txt
@rem codecept.bat run api 01rssCept.php --steps --html --debug>bresult.txt
codecept.bat run acceptance 20wikiwordsCept.php --steps --html --debug>bresult.txt