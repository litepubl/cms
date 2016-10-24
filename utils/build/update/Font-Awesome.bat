cd ../../..
set home=%cd%
cd ../../gitrepos/components/Font-Awesome
git pull
cd font
for %%f in (%home%\js\mediaelement\*.*) do if exist %%~nxf copy /Y %%~nxf %home%\js\mediaelement\%%~nxf
for %%f in (%home%\js\mediaelement\css\*.*) do if exist %%~nxf copy /Y %%~nxf %home%\js\mediaelement\css\%%~nxf
for %%f in (%home%\js\mediaelement\lang\*.*) do if exist lang\%%~nxf copy /Y lang\%%~nxf %home%\js\mediaelement\lang\%%~nxf
cd %home%\js\mediaelement\lang
del *.min.js
for %%f in (*.js) do java -jar D:\OpenServer\domains\cms.cms\utils\build\closure\compiler.jar --js=%%~nxf --js_output_file=%%~nf.min%%~xf
pause