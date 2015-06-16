SETLOCAL  
set less=node d:\less.js\bin\lessc
set css=java -jar f:\web7\home\dest\www\build\compress-javascript\com.jar
set lesspath=f:\web7\home\dest\unfuddle\shop\upload\less\less\
set themepath=f:\web7\home\dest\unfuddle\shop\themes\shop\css\
timeout 1
%less% %lesspath%glicons.less %themepath%glyphicons.css
timeout 1
%css% %themepath%glyphicons.css -o %themepath%glyphicons.min.css
