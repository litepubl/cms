SETLOCAL  
set less=node D:\OpenServer\modules\node_modules\less\bin\lessc
set css=java -jar d:\OpenServer\domains\cms\build\compress-javascript\com.jar --type css
set lesspath=d:\OpenServer\domains\cms\utils\less\
set themepath=d:\OpenServer\domains\cms\themes\default\css\
cd %themepath%
del %lesspath%bootstrap\variables.less
copy %lesspath%bootstrap\variables.less.original %lesspath%bootstrap\variables.less
%less% %lesspath%default.less %themepath%default.css
%css% default.css -o default.min.css
pause
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\cerulean\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%cerulean.less %themepath%cerulean.css
%css% cerulean.css -o cerulean.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\cosmo\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%cosmo.less %themepath%cosmo.css
%css% cosmo.css -o cosmo.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\cyborg\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%cyborg.less %themepath%cyborg.css
%css% cyborg.css -o cyborg.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\darkly\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%darkly.less %themepath%darkly.css
%css% darkly.css -o darkly.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\flatly\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%flatly.less %themepath%flatly.css
%css% flatly.css -o flatly.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\journal\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%journal.less %themepath%journal.css
%css% journal.css -o journal.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\lumen\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%lumen.less %themepath%lumen.css
%css% lumen.css -o lumen.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\paper\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%paper.less %themepath%paper.css
%css% paper.css -o paper.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\readable\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%readable.less %themepath%readable.css
%css% readable.css -o readable.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\sandstone\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%sandstone.less %themepath%sandstone.css
%css% sandstone.css -o sandstone.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\simplex\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%simplex.less %themepath%simplex.css
%css% simplex.css -o simplex.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\slate\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%slate.less %themepath%slate.css
%css% slate.css -o slate.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\spacelab\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%spacelab.less %themepath%spacelab.css
%css% spacelab.css -o spacelab.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\superhero\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%superhero.less %themepath%superhero.css
%css% superhero.css -o superhero.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\united\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%united.less %themepath%united.css
%css% united.css -o united.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\yeti\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%yeti.less %themepath%yeti.css
%css% yeti.css -o yeti.min.css
cd %lesspath%
ENDLOCAL   