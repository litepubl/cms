SETLOCAL  
set home=d:\OpenServer\domains\cms.cms
rem for /r %home%\js\litepubl /d %%d in (*) do call jsdir %%d
for /d %%d in (%home%\js\litepubl\*) do call jsdir %%d
call jsdir %home%\js\fonts\css
for /d %%d in (%home%\lib\languages\*) do call jsdir %%d
ENDLOCAL   