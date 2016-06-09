SETLOCAL  
set mini=java -jar d:\OpenServer\domains\cms.cms\build\closure\compiler.jar --js=
set css=java -jar d:\OpenServer\domains\cms.cms\build\compress-javascript\com.jar
cd d:\OpenServer\domains\cms.cms\js\jquery\ui
%mini%accordion.js --js_output_file=accordion.min.js
%mini%autocomplete.js --js_output_file=autocomplete.min.js
%mini%button.js --js_output_file=button.min.js
%mini%core.js --js_output_file=core.min.js
%mini%datepicker.js --js_output_file=datepicker.min.js
%mini%dialog.js --js_output_file=dialog.min.js
%mini%draggable.js --js_output_file=draggable.min.js
%mini%droppable.js --js_output_file=droppable.min.js
%mini%effect-blind.js --js_output_file=effect-blind.min.js
%mini%effect-bounce.js --js_output_file=effect-bounce.min.js
%mini%effect-clip.js --js_output_file=effect-clip.min.js
%mini%effect-drop.js --js_output_file=effect-drop.min.js
%mini%effect-explode.js --js_output_file=effect-explode.min.js
%mini%effect-fade.js --js_output_file=effect-fade.min.js
%mini%effect-fold.js --js_output_file=effect-fold.min.js
%mini%effect-highlight.js --js_output_file=effect-highlight.min.js
%mini%effect-puff.js --js_output_file=effect-puff.min.js
%mini%effect-pulsate.js --js_output_file=effect-pulsate.min.js
%mini%effect-scale.js --js_output_file=effect-scale.min.js
%mini%effect-shake.js --js_output_file=effect-shake.min.js
%mini%effect-size.js --js_output_file=effect-size.min.js
%mini%effect-slide.js --js_output_file=effect-slide.min.js
%mini%effect-transfer.js --js_output_file=effect-transfer.min.js
%mini%effect.js --js_output_file=effect.min.js
%mini%menu.js --js_output_file=menu.min.js
%mini%mouse.js --js_output_file=mouse.min.js
%mini%position.js --js_output_file=position.min.js
%mini%progressbar.js --js_output_file=progressbar.min.js
%mini%resizable.js --js_output_file=resizable.min.js
%mini%selectable.js --js_output_file=selectable.min.js
%mini%selectmenu.js --js_output_file=selectmenu.min.js
%mini%slider.js --js_output_file=slider.min.js
%mini%sortable.js --js_output_file=sortable.min.js
%mini%spinner.js --js_output_file=spinner.min.js
%mini%tabs.js --js_output_file=tabs.min.js
%mini%tooltip.js --js_output_file=tooltip.min.js
%mini%widget.js --js_output_file=widget.min.js
%mini%i18n\datepicker-ru.js --js_output_file=datepicker-ru.min.js
rem timeout 2
cd ..\..\..\build
ENDLOCAL   