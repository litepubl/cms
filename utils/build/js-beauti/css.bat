SETLOCAL
set parser=node D:\OpenServer\modules\node_modules\js-beautify\js\bin\js-beautify.js --type css
set tml=D:\OpenServer\domains\cms\temp\js-beauti\
for %%f in (%tml%*.css) do %parser% %%f -o %%f.min.css
ENDLOCAL