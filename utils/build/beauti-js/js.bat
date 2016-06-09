SETLOCAL
set parser=node D:\OpenServer\modules\node_modules\js-beautify\js\bin\js-beautify.js --type js
for %%f in (*.js) do %parser% %%f -o %%f
ENDLOCAL