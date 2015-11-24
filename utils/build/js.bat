SETLOCAL  
set mini=java -jar d:\OpenServer\domains\cms\build\closure\compiler.jar --js=
set css=java -jar d:\OpenServer\domains\cms\build\compress-javascript\com.jar
set less=node D:\OpenServer\modules\node_modules\less\bin\lessc
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
%mini%comments.template.js --js_output_file=comments.template.min.js
%mini%comments.quote.js --js_output_file=comments.quote.min.js
%mini%confirmcomment.js --js_output_file=confirmcomment.min.js
%mini%moderate.js --js_output_file=moderate.min.js
cd ..\bootstrap
%mini%anpost.ellipsis.js --js_output_file=anpost.ellipsis.min.js
%mini%dialog.bootstrap.js --js_output_file=dialog.bootstrap.min.js
%mini%dialog.simpler.js --js_output_file=dialog.simpler.min.js
%mini%player.bootstrap.js --js_output_file=player.bootstrap.min.js
%mini%popover.image.js --js_output_file=popover.image.min.js
%mini%popover.image.init.js --js_output_file=popover.image.init.min.js
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
%mini%homeuploader.js --js_output_file=homeuploader.min.js
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
%less% fileman.less fileman.css
%css% fileman.css -o fileman.min.css
%css% table.css -o table.min.css
cd ..\..\..\..\lib\languages\en
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
cd ..\imagepolls
%mini%imagepolls.client.js --js_output_file=imagepolls.client.min.js
%css% topimages.css -o topimages.min.css
cd ..\slides
%mini%slides.plugin.js --js_output_file=slides.plugin.min.js
cd ..\slideshow
%mini%slideshow.js --js_output_file=slideshow.min.js
cd ..\regservices
%css% regservices.css -o regservices.min.css
cd ..\ulogin\resource
%mini%authdialog.js --js_output_file=authdialog.min.js
%mini%email.auth.js --js_output_file=email.auth.min.js
%mini%ru.authdialog.js --js_output_file=ru.authdialog.min.js
%mini%en.authdialog.js --js_output_file=en.authdialog.min.js
%mini%ulogin.popup.js --js_output_file=ulogin.popup.min.js
cd ..\..\rss-chrome\resource
%mini%rss-chrome.js --js_output_file=rss-chrome.min.js
%mini%ru.rss-chrome.js --js_output_file=ru.rss-chrome.min.js
cd ..\..\bootstrap-theme\resource
%mini%header.js --js_output_file=header.min.js
cd ..\..\likebuttons\resource
%mini%likebuttons.js --js_output_file=likebuttons.min.js
cd ..\..\photoswipe\resource
%css% photoswipe.css -o photoswipe.min.css
%mini%photoswipe.plugin.js --js_output_file=photoswipe.plugin.min.js
%mini%photoswipe.plugin.tml.js --js_output_file=photoswipe.plugin.tml.min.js
%mini%en.photoswipe.plugin.js --js_output_file=en.photoswipe.plugin.min.js
%mini%ru.photoswipe.plugin.js --js_output_file=ru.photoswipe.plugin.min.js
cd default-skin
%css% default-skin.css -o default-skin.min.css
%less% default-skin.inline.less default-skin.inline.css
%css% default-skin.inline.css -o default-skin.inline.min.css
cd ..\..\..\photoswipe-thumbnail\resource
%mini%thumbnails.js --js_output_file=thumbnails.min.js
%css% thumbnails.css -o thumbnails.min.css
cd ..\..\..\themes\default\
%less% less\logo.less css\logo.css
%css% logo.css -o logo.min.css
cd ..\..\..\build
ENDLOCAL   