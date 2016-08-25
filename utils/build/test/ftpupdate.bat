set cur=%~dp0
cd %~dp0../../..
git archive --output=temp.zip --prefix=shop.cms/ head
cd ..
rmdir /S /Q shop.cms
unzip cms.cms/temp.zip
del cms.cms\temp.zip
copy cms.cms\index.debug.php shop.cms\
cd %cur%
php tests\updateftp\install.php
