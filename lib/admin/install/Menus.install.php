<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin;
use litepubl\view\Lang;

function MenusInstall($self) {
    if (__NAMESPACE__ . '\Menus' != get_class($self)) {
 return;
}



     $self->getApp()->classes->onrename = $self->classRenamed;

    $self->lock();
    $self->heads = '<link type="text/css" href="$site.files$template.cssmerger_admin" rel="stylesheet" />
  <script type="text/javascript" src="$site.files$template.jsmerger_admin"></script>';

    //posts
    $posts = $self->createitem(0, 'posts', 'author', 'litepubl\post\Posts');
 {
        $id = $self->createitem($posts, 'editor', 'author', 'tposteditor');
        $self->items[$id]['title'] = Lang::i()->newpost;
        $self->createitem($posts, 'addcat', 'editor', 'tadmintags');
        $self->createitem($posts, 'categories', 'editor', 'tadmintags');
        $self->createitem($posts, 'addtag', 'editor', 'tadmintags');
        $self->createitem($posts, 'tags', 'editor', 'tadmintags');
        $self->createitem($posts, 'staticpages', 'editor', 'tadminstaticpages');
    }

    $moder = $self->createitem(0, 'comments', 'commentator', '\litepubl\admin\Moderator');
 {
        $self->createitem($moder, 'hold', 'commentator', 'tadminmoderator');
        $self->createitem($moder, 'authors', 'moderator', 'Commentators');
        $self->createitem($moder, 'pingback', 'moderator', 'tadminpingbacks');
    }

    $plugins = $self->createitem(0, 'plugins', 'admin', '\litepubl\admin\Plugins');
    $files = $self->createitem(0, 'files', 'author', '\litepubl\admin\Files');
 {
        $self->createitem($files, 'thumbnail', 'editor', 'tadminfilethumbnails');
        $self->createitem($files, 'image', 'editor', 'tadminfiles');
        $self->createitem($files, 'video', 'editor', 'tadminfiles');
        $self->createitem($files, 'audio', 'editor', 'tadminfiles');
        $self->createitem($files, 'bin', 'editor', 'tadminfiles');
    }

    $schemes = $self->createitem(0, 'views', 'admin', 'tadminviews'); {
        $self->createitem($schemes, 'addview', 'admin', 'tadminviews');
        //$self->createitem($schemes, 'themes', 'admin', 'tadminthemes');
        $self->createitem($schemes, 'widgets', 'admin', 'tadminwidgets');
        $self->createitem($schemes, 'addcustom', 'admin', 'addcustomwidget');
        $self->createitem($schemes, 'group', 'admin', 'tadminviewsgroup');
        $self->createitem($schemes, 'defaults', 'admin', 'tadminviews');
        $self->createitem($schemes, 'spec', 'admin', 'tadminviewsspec');
        $self->createitem($schemes, 'headers', 'admin', 'tadminheaders');
        $self->createitem($schemes, 'jsmerger', 'admin', 'tadminjsmerger');
        $self->createitem($schemes, 'cssmerger', 'admin', '\litepubl\admin\Css');
    }

    $menu = $self->createitem(0, 'menu', 'editor', 'tadminmenumanager');
 {
        $id = $self->createitem($menu, 'edit', 'editor', 'tadminmenumanager');
        $self->items[$id]['title'] = Lang::get('menu', 'addmenu');
        $id = $self->createitem($menu, 'editfake', 'editor', 'tadminmenumanager');
        $self->items[$id]['title'] = Lang::get('menu', 'addfake');
    }

    $opt = $self->createitem(0, 'options', 'admin', 'tadminoptions'); {
        $self->createitem($opt, 'home', 'admin', 'adminhomeoptions');
        $self->createitem($opt, 'mail', 'admin', 'tadminoptions');
        $self->createitem($opt, 'rss', 'admin', 'tadminoptions');
        $self->createitem($opt, 'view', 'admin', 'tadminoptions');
        $self->createitem($opt, 'files', 'admin', 'tadminoptions');
        $self->createitem($opt, 'comments', 'admin', 'tadmincommentmanager');
        $self->createitem($opt, 'ping', 'admin', 'tadminoptions');
        $self->createitem($opt, 'links', 'admin', 'tadminoptions');
        $self->createitem($opt, 'cache', 'admin', 'tadminoptions');
        $self->createitem($opt, 'catstags', 'admin', 'tadminoptions');
        $self->createitem($opt, 'secure', 'admin', 'adminsecure');
        $self->createitem($opt, 'robots', 'admin', 'tadminoptions');
        $self->createitem($opt, 'local', 'admin', 'tadminlocalmerger');
        $self->createitem($opt, 'parser', 'admin', 'adminthemeparser');
        $self->createitem($opt, 'notfound404', 'admin', 'tadminoptions');
        $self->createitem($opt, 'redir', 'admin', 'tadminredirector');
    }

    $service = $self->createitem(0, 'service', 'admin', 'tadminservice'); {
        $self->createitem($service, 'backup', 'admin', 'tadminservice');
        $self->createitem($service, 'upload', 'admin', 'tadminservice');
        $self->createitem($service, 'run', 'admin', 'tadminservice');
    }

    $id = $self->addfake('/admin/logout/', Lang::i()->logout);
    $self->items[$id]['order'] = 9999999;
    /*
    $board = $self->additem(array(
    'parent' => 0,
    'url' => '/admin/',
    'title' => Lang::get('adminmenus', 'board'),
    'name' => 'board',
    'class' => 'tadminboard',
    'group' => 'author'
    ));
    */
    $self->unlock();

    $redir = tredirector::i();
    $redir->add('/admin/', '/admin/posts/editor/');
}

function MenusUninstall($self) {
    //rmdir(. 'menus');
    
}