cd ../../..
set home=%cd%
cd ../../gitrepos/fontfaceobserver
git pull
copy /Y fontfaceobserver.js %home%\js\plugins\
