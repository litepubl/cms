SETLOCAL
set parser=node D:\OpenServer\modules\node_modules\js-beautify\js\bin\js-beautify.js --type html
rem set tml=D:\OpenServer\domains\cms\temp\js-beauti\
for %%f in (*.txt) do %parser% %%f -o %%f
ENDLOCAL