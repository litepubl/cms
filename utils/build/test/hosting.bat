set cur=%~dp0
cd %~dp0../../..
@rem copy current cms release
git archive --output=temp.zip --prefix=release.cms/ head
cd ..
rmdir /S /Q release.cms
unzip cms.cms/temp.zip
del cms.cms\temp.zip
copy cms.cms\index.debug.php release.cms\
@rem copy shop scripts
set dom=%cd%
cd ..\unfuddle\shop
git archive --output=temp.zip --prefix=release.cms/ head
cd %dom%
unzip ../unfuddle/shop/temp.zip
del ..\unfuddle\shop\temp.zip

cd %cur%
pause
exit
php tests\updateftp\install.php
del tests\_output\*.* /f /q
vendor\bin\codecept.bat run updateftp --steps --html --debug>bresult.txt
