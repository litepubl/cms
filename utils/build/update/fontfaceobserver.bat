cd ../../..
set home=%cd%
cd ../../gitrepos/components/fontfaceobserver
git pull
copy /Y fontfaceobserver.js %home%\js\plugins\
