SETLOCAL  
set home=d:\OpenServer\domains\cms
node D:\OpenServer\modules\node_modules\less\bin\lessc %home%\plugins\photoswipe\resource\default-skin\default-skin.inline.less %home%\plugins\photoswipe\resource\default-skin\default-skin.inline.css
for /f %%f in (jsplugins.txt) do call jsfile %home%\plugins\%%f
ENDLOCAL   