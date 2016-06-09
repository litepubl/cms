SETLOCAL
set parser=node D:\OpenServer\modules\node_modules\uglify-js\bin\uglifyjs
cls
for %%f in (*.js) do %parser% %%f -c -o %%~nf.min%%~xf