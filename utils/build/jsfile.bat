IF NOT EXIST %1.js GOTO css
node c:\OpenServer\modules\node_modules\js-beautify\js\bin\js-beautify.js --type js -s 2 %1.js -o %1.js
java -jar c:\OpenServer\domains\cms.cms\utils\build\closure\compiler.jar --js=%1.js --js_output_file=%1.min.js
:css
IF NOT EXIST %1.css GOTO final
node c:\OpenServer\modules\node_modules\js-beautify\js\bin\js-beautify.js --type css %1.css -o %1.css
node c:\OpenServer\modules\node_modules\clean-css\bin\cleancss %1.css -o %1.min.css
:final