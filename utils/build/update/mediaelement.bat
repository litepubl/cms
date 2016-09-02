cd ../../..
set home=%cd%
cd ../../gitrepos/components/mediaelement
git pull
cd build
for %%f in (%home%\js\mediaelement\*.*) do if exist %%~nxf copy /Y %%~nxf %home%\js\mediaelement\%%~nxf
for %%f in (%home%\js\mediaelement\css\*.*) do if exist %%~nxf copy /Y %%~nxf %home%\js\mediaelement\css\%%~nxf
