SETLOCAL  
set home=d:\OpenServer\domains\cms
rem for /r %home%\js\litepubl /d %%d in (*) do call jsdir %%d
for /d %%d in (%home%\js\litepubl\*) do call jsdir %%d
call jsdir %home%\js\fonts\css
for /d %%d in (%home%\lib\languages\*) do call jsdir %%d
cd ..\..\..\themes\default\
%less% less\logo.less css\logo.css
%css% logo.css -o logo.min.css

ENDLOCAL   