SETLOCAL  
set home=d:\OpenServer\domains\cms
for /f %%f in (jsexternal.txt) do java -jar closure\compiler.jar --js=%home%\js\%%f.js --js_output_file=%home%\js\%%f.min.js

ENDLOCAL   