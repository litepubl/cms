SETLOCAL  
set home=c:\OpenServer\domains\cms.cms
for /f %%f in (jsless.txt) do node C:\OpenServer\domains\builder.cms\node_modules\less\bin\lessc %home%\%%f.less %home%\%%f.css
for /f %%f in (jsplugins.txt) do call jsfile %home%\plugins\%%f
ENDLOCAL   