SETLOCAL  
set less=node D:\OpenServer\modules\node_modules\less\bin\lessc
set css=node D:\OpenServer\modules\node_modules\clean-css\bin\cleancss
rem set css=java -jar d:\OpenServer\domains\cms\build\compress-javascript\com.jar --type css
set home=d:\OpenServer\domains\cms
set lesspath=%home%\utils\less\
cd %home%\themes\default\css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootstrap\variables.less.original %lesspath%bootstrap\variables.less
%less% %lesspath%default.less default.css
%css% default.css -o default.min.css
rem pause 
rem exit
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\cerulean\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%cerulean.less cerulean.css
%css% cerulean.css -o cerulean.min.css
rem pause
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\cosmo\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%cosmo.less cosmo.css
%css% cosmo.css -o cosmo.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\cyborg\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%cyborg.less cyborg.css
%css% cyborg.css -o cyborg.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\darkly\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%darkly.less darkly.css
%css% darkly.css -o darkly.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\flatly\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%flatly.less flatly.css
%css% flatly.css -o flatly.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\journal\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%journal.less journal.css
%css% journal.css -o journal.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\lumen\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%lumen.less lumen.css
%css% lumen.css -o lumen.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\paper\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%paper.less paper.css
%css% paper.css -o paper.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\readable\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%readable.less readable.css
%css% readable.css -o readable.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\sandstone\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%sandstone.less sandstone.css
%css% sandstone.css -o sandstone.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\simplex\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%simplex.less simplex.css
%css% simplex.css -o simplex.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\slate\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%slate.less slate.css
%css% slate.css -o slate.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\spacelab\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%spacelab.less spacelab.css
%css% spacelab.css -o spacelab.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\superhero\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%superhero.less superhero.css
%css% superhero.css -o superhero.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\united\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%united.less united.css
%css% united.css -o united.min.css
del %lesspath%bootstrap\variables.less
copy %lesspath%bootswatch\yeti\variables.less %lesspath%bootstrap\variables.less
%less% %lesspath%yeti.less yeti.css
%css% yeti.css -o yeti.min.css
%less% ..\less\logo.less logo.css
%css% logo.css -o logo.min.css
ENDLOCAL   