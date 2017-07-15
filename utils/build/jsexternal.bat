SETLOCAL  
set home=c:\OpenServer\domains\cms.cms
for /f %%f in (jsexternal.txt) do java -jar closure\compiler.jar --js=%home%\js\%%f.js --js_output_file=%home%\js\%%f.min.js
call jsfile %home%\js\fix\android-select
call jsfile %home%\js\fix\ie10
cd %home%\js\fix
copy html5shiv.js + respond.src.js ie9.js /b
copy html5shiv.min.js + respond.min.js ie9.min.js /b
ENDLOCAL   