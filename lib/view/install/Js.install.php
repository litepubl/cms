<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\view;

use litepubl\core\Arr;

function JsInstall($self)
{
    $dir = $self->getApp()->paths->files . 'js';
    @mkdir($dir, 0777);
    @chmod($dir, 0777);
    $file = $dir . DIRECTORY_SEPARATOR . 'index.htm';
    file_put_contents($file, ' ');
    @chmod($file, 0666);

    $language = $self->getApp()->options->language;
    $self->lock();
    $self->items = array();
    $section = 'default';

    $defaults = array(
        '/js/jquery/jquery-$site.jquery_version.min.js',
        '/js/plugins/class-extend.min.js',
        '/js/plugins/jquery.cookie.min.js',
        '/js/plugins/tojson.min.js',
        '/js/plugins/fontfaceobserver.js',

        // litepubl/system
        '/js/litepubl/system/css-loader.min.js',
        '/js/litepubl/system/escape.min.js',
        '/js/litepubl/system/get_get.min.js',
        '/js/litepubl/system/html-comments.min.js',
        '/js/litepubl/system/json-rpc.min.js',
        '/js/litepubl/system/load-font.min.js',
        '/js/litepubl/system/load-script.min.js',
        '/js/litepubl/system/parsetml.min.js',
        '/js/litepubl/system/ready2.min.js',
        //'/js/litepubl/system/storage.min.js',
        //litepubl/common
        '/js/litepubl/common/litepubl.namespace.min.js',
        '/js/litepubl/common/litepubl.init.min.js',
        '/js/litepubl/common/dialog.min.js',
        '/js/litepubl/common/players.min.js',
        '/js/litepubl/common/templates.min.js',

        //effects
        '/js/litepubl/effects/homeimage.min.js',
    );

    foreach ($defaults as $filename) {
        $self->add($section, $filename);
    }

    //Js_switch($self, Js_pretty_files(), array());
    Js_switch($self, Js_bootstrap_files(), array());

    $self->add($section, "/lib/languages/$language/default.min.js");

    $section = 'comments';
    $self->add($section, '/js/litepubl/comments/comments.template.min.js');
    $self->add($section, '/js/litepubl/comments/comments.quote.min.js');
    $self->add($section, '/js/litepubl/comments/confirmcomment.min.js');
    $self->add($section, '/js/litepubl/comments/moderate.min.js');
    $self->add($section, "/lib/languages/$language/comments.min.js");

    $section = 'media';
    $self->add($section, '/js/mediaelement/mediaelement-and-player.min.js');
    if ($language != 'en') {
        $self->add($section, "/lib/languages/$language/mediaplayer.min.js");
    }

    $section = 'admin';
    Js_bootstrap_admin($self, true);
    $self->add($section, '/js/litepubl/admin/admin.min.js');
    $self->add($section, 'js/litepubl/ui/datepicker.adapter.min.js');
    $self->add($section, '/js/litepubl/admin/calendar.min.js');
    $self->add($section, "/lib/languages/$language/admin.min.js");

    $section = 'posteditor';
    $self->add($section, '/js/swfupload/swfupload.min.js');
    $self->add($section, '/js/plugins/filereader.min.js');
    $self->add($section, '/js/litepubl/admin/uploader.min.js');
    $self->add($section, '/js/litepubl/admin/uploader.html.min.js');
    $self->add($section, '/js/litepubl/admin/uploader.flash.min.js');
    $self->add($section, '/js/litepubl/admin/posteditor.min.js');
    $self->add($section, '/js/litepubl/admin/fileman.min.js');
    $self->add($section, '/js/litepubl/admin/fileman.browser.min.js');
    $self->add($section, '/js/litepubl/admin/fileman.propedit.min.js');
    $self->add($section, '/js/litepubl/admin/fileman.templates.min.js');
    $self->add($section, "/lib/languages/$language/posteditor.min.js");

    $self->unlock();
}

function JsUninstall($self)
{
}

function Js_switch($self, $add, $delete)
{
    $self->lock();

    foreach ($delete as $filename) {
        $self->deletefile('default', $filename);
    }

    foreach ($add as $filename) {
        $self->add('default', $filename);
    }

    $self->unlock();
}

function Js_pretty_files()
{
    return array(
        '/js/prettyphoto/js/jquery.prettyPhoto.js',
        '/js/litepubl/pretty/dialog.pretty.min.js',
        '/js/litepubl/pretty/pretty.init.min.js',
        '/js/litepubl/pretty/youtubefix.min.js',
        '/js/litepubl/pretty/player.pretty.min.js',
        '/js/litepubl/common/widgets.min.js',
    );
}

function Js_bootstrap_files()
{
    return array(
        // fix
        '/js/fix/ie10.min.js',
        '/js/fix/android-select.min.js',

        //effects
        '/js/litepubl/effects/editor-height.min.js',
        '/js/litepubl/effects/scrollto.min.js',

        // bootstrap
        '/js/bootstrap/transition.min.js',
        '/js/bootstrap/collapse.min.js',
        '/js/bootstrap/dropdown.min.js',
        '/js/bootstrap/modal.min.js',
        '/js/bootstrap/tooltip.min.js',
        '/js/bootstrap/popover.min.js',

        // litepubl/bootstrap
        '/js/litepubl/bootstrap/tooltip.init.min.js',
        '/js/litepubl/bootstrap/anpost.ellipsis.min.js',
        '/js/litepubl/bootstrap/dialog.bootstrap.min.js',
        '/js/litepubl/bootstrap/dialog.simpler.min.js',
        '/js/litepubl/bootstrap/player.bootstrap.min.js',
        '/js/litepubl/bootstrap/popover.single.min.js',
        '/js/litepubl/bootstrap/widgets.bootstrap.min.js',
        '/js/litepubl/bootstrap/youtube.bootstrap.min.js',
        '/js/litepubl/bootstrap/theme.init.min.js',
        '/js/litepubl/bootstrap/theme.fonts.min.js',

        // fonts
        '/js/fonts/css/lobster.min.js',
        '/js/fonts/css/font-awesome.min.js',
    );
}

function Js_bootstrap_admin($js, $add = true)
{
    $items = array(
        'admin' => array(
            '/js/bootstrap/tab.min.js',
            '/js/litepubl/bootstrap/tabs.keys.min.js',
            '/js/litepubl/bootstrap/tabs.tml.min.js',
            '/js/litepubl/bootstrap/tabs.adapter.min.js',
        ) ,

        'posteditor' => array(
            '/js/litepubl/bootstrap/progressbar.adapter.min.js',
        ) ,
    );

    foreach ($items as $section => $filenames) {
        foreach ($filenames as $filename) {
            if ($add) {
                $js->add($section, $filename);
            } else {
                $js->deletefile($section, $filename);
            }
        }
    }

}

function Js_ui_admin($js, $add = true)
{
    $js->getApp()->getLogger()->debug(var_export($add, true));
    $js->getApp()->getLogManager()->trace();
    $items = array(
        'admin' => array(
            '/js/jquery/ui/core.min.js',
            '/js/jquery/ui/widget.min.js',
            '/js/jquery/ui/mouse.min.js',
            '/js/jquery/ui/position.min.js',
            '/js/jquery/ui/effect.min.js',
            '/js/jquery/ui/tabs.min.js',

            '/js/litepubl/ui/tabs.tml.min.js',
            '/js/litepubl/ui/tabs.adapter.min.js',
        ) ,

        'posteditor' => array(
            '/js/jquery/ui/progressbar.min.js',
            '/js/litepubl/ui/progressbar.adapter.min.js',
        ) ,
    );

    foreach ($items as $section => $filenames) {
        foreach ($filenames as $filename) {
            if ($add) {
                $js->add($section, $filename);
            } else {
                $js->deletefile($section, $filename);
            }
        }
    }

    if ($add) {
        Arr::move($js->items['posteditor']['files'], array_search($items['posteditor'][0], $js->items['posteditor']['files']), 0);
    }
}