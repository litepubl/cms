set less=node d:\less\node_modules\less\bin\lessc
set css=java -jar d:\OpenServer\domains\cms\build\compress-javascript\com.jar
set lesspath=d:\unfuddle\shop\themes\less\
set themepath=d:\unfuddle\shop\themes\shop\css\
cd %themepath%
del %lesspath%bootstrap\variables.less
cls
copy %lesspath%bootswatch\paper\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%paper.less %themepath%paper.css
%css% paper.css -o paper.min.css
cd %lesspath%