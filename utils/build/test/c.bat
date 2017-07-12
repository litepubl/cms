del tests\_output\*.* /f /q
del tests\_output\debug\*.* /f /q
vendor\bin\codecept run acceptance  A20UloginCest.php --steps --html --debug>bresult.txt
@rem vendor\bin\codecept.bat run acceptance A20TicketsCest.php --steps --html --debug>bresult.txt
@rem vendor\bin\codecept.bat run acceptance  A11CommentCest.php --steps --html --debug>bresult.txt
