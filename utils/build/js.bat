SETLOCAL  
set mini=java -jar d:\OpenServer\domains\cms\build\closure\compiler.jar --js=
set css=java -jar d:\OpenServer\domains\cms\build\compress-javascript\com.jar
cd d:\OpenServer\domains\cms\js\litepubl\system
%mini%css-loader.js --js_output_file=css-loader.min.js
%mini%escape.js --js_output_file=escape.min.js
%mini%get_get.js --js_output_file=get_get.min.js
%mini%html-comments.js --js_output_file=html-comments.min.js
%mini%json-rpc.js --js_output_file=json-rpc.min.js
%mini%load-font.js --js_output_file=load-font.min.js
%mini%load-script.js --js_output_file=load-script.min.js
%mini%parsetml.js --js_output_file=parsetml.min.js
%mini%ready2.js --js_output_file=ready2.min.js
%mini%storage.js --js_output_file=storage.min.js
cd ..\common
%mini%litepubl.namespace.js --js_output_file=litepubl.namespace.min.js
%mini%litepubl.init.js --js_output_file=litepubl.init.min.js
%mini%widgets.js --js_output_file=widgets.min.js
%mini%players.js --js_output_file=players.min.js
%mini%dialog.js --js_output_file=dialog.min.js
%mini%templates.js --js_output_file=templates.min.js
cd css
%css% common.css -o common.min.css
%css% filelist.css -o filelist.min.css
%css% form.inline.css -o form.inline.min.css
cd ..\..\comments
%mini%comments.js --js_output_file=comments.min.js
%mini%confirmcomment.js --js_output_file=confirmcomment.min.js
%mini%moderate.js --js_output_file=moderate.min.js
cd ..\bootstrap
%mini%dialog.bootstrap.js --js_output_file=dialog.bootstrap.min.js
%mini%dialog.simpler.js --js_output_file=dialog.simpler.min.js
%mini%player.bootstrap.js --js_output_file=player.bootstrap.min.js
%mini%popover.image.js --js_output_file=popover.image.min.js
%mini%popover.post.js --js_output_file=popover.post.min.js
%mini%popover.single.js --js_output_file=popover.single.min.js
%mini%theme.fonts.js --js_output_file=theme.fonts.min.js
%mini%theme.init.js --js_output_file=theme.init.min.js
%mini%tooltip.init.js --js_output_file=tooltip.init.min.js
%mini%widgets.bootstrap.js --js_output_file=widgets.bootstrap.min.js
%mini%youtube.bootstrap.js --js_output_file=youtube.bootstrap.min.js
cd ..\effects
%mini%editor-height.js --js_output_file=editor-height.min.js
%mini%flytext.js --js_output_file=flytext.min.js
%mini%homeimage.js --js_output_file=homeimage.min.js
%mini%moveitem.js --js_output_file=moveitem.min.js
%mini%remove-selected.js --js_output_file=remove-selected.min.js
%mini%scrollto.js --js_output_file=scrollto.min.js
cd css
%css% homeimage.css -o homeimage.min.css
cd ..\..\pretty
%mini%dialog.pretty.js --js_output_file=dialog.pretty.min.js
%mini%player.pretty.js --js_output_file=player.pretty.min.js
%mini%pretty.init.js --js_output_file=pretty.init.min.js
%mini%youtubefix.js --js_output_file=youtubefix.min.js
%css% dialog.pretty.css -o dialog.pretty.min.css
cd ..\admin
%mini%admin.js --js_output_file=admin.min.js
%mini%admin.views.js --js_output_file=admin.views.min.js
%mini%calendar.js --js_output_file=calendar.min.js
%mini%fileman.js --js_output_file=fileman.min.js
%mini%fileman.browser.js --js_output_file=fileman.browser.min.js
%mini%fileman.propedit.js --js_output_file=fileman.propedit.min.js
%mini%fileman.templates.js --js_output_file=fileman.templates.min.js
%mini%posteditor.js --js_output_file=posteditor.min.js
%mini%swfuploader.js --js_output_file=swfuploader.min.js
%mini%tablecolumns.js --js_output_file=tablecolumns.min.js
%mini%uploader.js --js_output_file=uploader.min.js
%mini%uploader.flash.js --js_output_file=uploader.flash.min.js
%mini%uploader.html.js --js_output_file=uploader.html.min.js
cd css
%css% admin.views.css -o admin.views.min.css
%css% fileman.css -o fileman.min.css
cd ..\..\deprecated
%mini%fileman.templates.js --js_output_file=fileman.templates.min.js
cd css
%css% align.css -o align.min.css
%css% button.css -o button.min.css
node D:\OpenServer\modules\node_modules\less\bin\lessc fileman.less fileman.css
%css% fileman.css -o fileman.min.css

%css% table.css -o table.min.css
cd ..\..\..\plugins
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
cd ..\..\bootstrap
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
cd ..\..\lib\languages\en
%mini%admin.js --js_output_file=admin.min.js
%mini%comments.js --js_output_file=comments.min.js
%mini%default.js --js_output_file=default.min.js
%mini%posteditor.js --js_output_file=posteditor.min.js
cd ..\ru
%mini%admin.js --js_output_file=admin.min.js
%mini%comments.js --js_output_file=comments.min.js
%mini%default.js --js_output_file=default.min.js
%mini%mediaplayer.js --js_output_file=mediaplayer.min.js
%mini%posteditor.js --js_output_file=posteditor.min.js
cd ..\..\..\plugins
cd polls
%mini%polls.client.js --js_output_file=polls.client.min.js
rem timeout 1
cd ..\imagepolls
%mini%imagepolls.client.js --js_output_file=imagepolls.client.min.js
rem timeout 1
%css% topimages.css -o topimages.min.css
rem timeout 1
cd ..\slides
%mini%slides.plugin.js --js_output_file=slides.plugin.min.js
rem timeout 1
cd ..\slideshow
%mini%slideshow.js --js_output_file=slideshow.min.js
rem timeout 1
cd ..\regservices
%css% regservices.css -o regservices.min.css
rem timeout 1
cd ..\ulogin\resource
%mini%ulogin.popup.js --js_output_file=ulogin.popup.min.js
rem timeout 1
%mini%ru.ulogin.popup.js --js_output_file=ru.ulogin.popup.min.js
rem timeout 1
%mini%en.ulogin.popup.js --js_output_file=en.ulogin.popup.min.js
rem timeout 1
%mini%email.auth.js --js_output_file=email.auth.min.js
rem timeout 1
%mini%ru.email.auth.js --js_output_file=ru.email.auth.min.js
cd ..\..\rss-chrome\resource
%mini%rss-chrome.js --js_output_file=rss-chrome.min.js
%mini%ru.rss-chrome.js --js_output_file=ru.rss-chrome.min.js
cd ..\..\..\build
ENDLOCAL   