set cur=%~dp0
cd %~dp0../../..
git archive --output=temp.zip --prefix=shop.cms/ head
cd ..
@rem del *.* /f /q /s
rmdir /S /Q shop.cms
unzip cms.cms/temp.zip
del cms.cms\temp.zip
copy cms.cms\index.debug.php shop.cms\
cd %cur%
pause