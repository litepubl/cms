del tests\_output\*.* /f /q
del tests\_output\debug\*.* /f /q
@rem vendor\bin\codecept.bat run acceptance  --steps --html --debug>bresult.txt
vendor\bin\codecept.bat run acceptance A20TicketsCest.php --steps --html --debug>bresult.txt
@rem vendor\bin\codecept.bat run acceptance  A11CommentCest.php --steps --html --debug>bresult.txt
