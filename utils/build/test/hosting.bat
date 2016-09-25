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
echo shop= "../shop">release.cms\plugins\shop-plugins.ini
cd %cur%
curl -o tests/_data/admin.json  --connect-timeout  300  "http://release.cms/?mode=auto&name=Release&email=j@jj.jj&description=shoper&dbname=jusoft_test&dblogin=jusoft_test&dbpassword=test&dbversion=1&dbprefix=shop_&lang=ru&mode=remote&resulttype=json"
del tests\_output\*.* /f /q
call vendor\bin\codecept.bat run shop/70hostingCept.php --steps --html --debug>bresult.txt
echo ok finished