SETLOCAL  
set less=node d:\less.js\bin\lessc
set css=java -jar f:\web7\home\dest\www\build\compress-javascript\com.jar --type css
set lesspath=f:\web7\home\dest\unfuddle\shop\upload\less\less\
set themepath=f:\web7\home\dest\www\themes\mocart\css\
cd %themepath%
%less% %lesspath%glicons.less %themepath%glyphicons.css
%css% glyphicons.css -o glyphicons.min.css
%less% %lesspath%mocart.less %themepath%temp.css
copy before.css + temp.css style.css /b
%css% style.css -o style.min.css
del %themepath%temp.css
cd %lesspath%
ENDLOCAL   