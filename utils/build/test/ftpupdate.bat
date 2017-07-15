set cur=%~dp0
cd %~dp0../../..
git archive --output=temp.zip --prefix=shop.cms/ head
cd ..
rmdir /S /Q shop.cms
unzip cms.cms/temp.zip
del cms.cms\temp.zip
copy cms.cms\index.debug.php shop.cms\
cd %cur%
curl -o tests/_data/admin.json  --connect-timeout  300  "http://shop.cms/?mode=auto&name=Release&email=j@jj.jj&description=shoper&dbname=jusoft_test&dblogin=jusoft_test&dbpassword=test&dbversion=1&dbprefix=shop_&lang=ru&mode=remote&resulttype=json"
del tests\_output\*.* /f /q
vendor\bin\codecept.bat run updateftp --steps --html --debug>bresult.txt
