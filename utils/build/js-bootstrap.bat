SETLOCAL  
set mini=java -jar d:\OpenServer\domains\cms\build\closure\compiler.jar --js=
set css=java -jar d:\OpenServer\domains\cms\build\compress-javascript\com.jar
set less=node D:\OpenServer\modules\node_modules\less\bin\lessc
cd d:\OpenServer\domains\cms\js\bootstrap
%mini%button.js --js_output_file=button.min.js
%mini%collapse.js --js_output_file=collapse.min.js
%mini%dropdown.js --js_output_file=dropdown.min.js
%mini%modal.js --js_output_file=modal.min.js
%mini%popover.js --js_output_file=popover.min.js
%mini%tooltip.js --js_output_file=tooltip.min.js
%mini%transition.js --js_output_file=transition.min.js
cd ..\fix
%mini%android-select.js --js_output_file=android-select.min.js
%mini%ie10.js --js_output_file=ie10.min.js
%mini%modernizr.transitions.js --js_output_file=modernizr.transitions.min.js
copy html5shiv.js + respond.src.js ie9.js /b
copy html5shiv.min.js + respond.min.js ie9.min.js /b
cd ..\plugins
%mini%class-extend.js --js_output_file=class-extend.min.js
%mini%jquery.cookie.js --js_output_file=jquery.cookie.min.js
%mini%jquery.mousewheel.js --js_output_file=jquery.mousewheel.min.js
%mini%tojson.js --js_output_file=tojson.min.js
%mini%filereader.js --js_output_file=filereader.min.js
cd ..\swfupload
%mini%swfupload.js --js_output_file=swfupload.min.js
cd ..\fonts/css
rem %css% font-awesome.css -o font-awesome.min.css
%mini%font-awesome.js --js_output_file=font-awesome.min.js
%mini%font-awesome.cdn.js --js_output_file=font-awesome.cdn.min.js
%mini%lobster.js --js_output_file=lobster.min.js
%mini%lobster.cdn.js --js_output_file=lobster.cdn.min.js
ENDLOCAL   
