set curdir=%cd%
cd %1
del *.min.js
for %%f in (*.js) do node C:\OpenServer\domains\builder.cms\node_modules\js-beautify\js\bin\js-beautify.js --type js -s 2 %%~nxf -o %%~nxf
for %%f in (*.js) do java -jar c:\OpenServer\domains\cms.cms\utils\build\closure\compiler.jar --js=%%~nxf --js_output_file=%%~nf.min%%~xf
IF NOT EXIST css  GOTO final
cd css
del *.min.css
for %%f in (*.less) do node C:\OpenServer\domains\builder.cms\node_modules\less\bin\lessc %%~nxf %%~nf.css
for %%f in (*.css) do node C:\OpenServer\domains\builder.cms\node_modules\js-beautify\js\bin\js-beautify.js --type css %%~nxf -o %%~nxf
for %%f in (*.css) do node C:\OpenServer\domains\builder.cms\node_modules\clean-css-cli\bin\cleancss %%~nxf -o %%~nf.min%%~xf
:final
cd %curdir%