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
use litepubl\pages\Redirector;

function MenusInstall($self) {
    if (__NAMESPACE__ . '\Menus' != get_class($self)) {
 return;
}

     $self->getApp()->classes->onrename = $self->classRenamed;

    $self->lock();
    $self->heads = '<link type="text/css" href="$site.files$template.cssmerger_admin" rel="stylesheet" />
  <script type="text/javascript" src="$site.files$template.jsmerger_admin"></script>';

    //posts
    $posts = $self->createitem(0, 'posts', 'author', 'litepubl\admin\posts\Posts');
 {
        $id = $self->createitem($posts, 'editor', 'author', 'litepubl\admin\posts\Editor');
        $self->items[$id]['title'] = Lang::i()->newpost;
        $self->createitem($posts, 'addcat', 'editor', 'litepubl\admin\posts\Tags');
        $self->createitem($posts, 'categories', 'editor', 'litepubl\admin\posts\Tags');
        $self->createitem($posts, 'addtag', 'editor', 'litepubl\admin\posts\Tags');
        $self->createitem($posts, 'tags', 'editor', 'litepubl\admin\posts\Tags');
        $self->createitem($posts, 'staticpages', 'editor', 'litepubl\admin\posts\StaticPages');
    }

    $moder = $self->createitem(0, 'comments', 'commentator', 'litepubl\admin\comments\Moderator');
 {
        $self->createitem($moder, 'hold', 'commentator', 'litepubl\admin\comments\Moderator');
        $self->createitem($moder, 'authors', 'moderator', 'litepubl\admin\comments\Authors');
        $self->createitem($moder, 'pingback', 'moderator', 'litepubl\admin\comments\Pingbacks');
    }

    $plugins = $self->createitem(0, 'plugins', 'admin', 'litepubl\admin\Plugins');
    $files = $self->createitem(0, 'files', 'author', 'litepubl\admin\files\Files');
 {
        $self->createitem($files, 'thumbnail', 'editor', 'litepubl\admin\files\Thumbnails');
        $self->createitem($files, 'image', 'editor', 'litepubl\admin\files\Files');
        $self->createitem($files, 'video', 'editor', 'litepubl\admin\files\Files');
        $self->createitem($files, 'audio', 'editor', 'litepubl\admin\files\Files');
        $self->createitem($files, 'bin', 'editor', 'litepubl\admin\files\Files');
    }

    $views = $self->createitem(0, 'views', 'admin', 'litepubl\admin\views\Schemes');
 {
        $self->createitem($views, 'addschema', 'admin', 'litepubl\admin\views\Schemes');
        //$self->createitem($schemes, 'themes', 'admin', 'tadminthemes');
        $self->createitem($views, 'widgets', 'admin', 'litepubl\admin\widget\Widgets');
        $self->createitem($views, 'addcustom', 'admin', 'litepubl\admin\widget\AddCustom');
        $self->createitem($views, 'group', 'admin', 'litepubl\admin\views\Group');
        $self->createitem($views, 'defaults', 'admin', 'litepubl\admin\views\Schemes');
        $self->createitem($views, 'spec', 'admin', 'litepubl\admin\views\Spec');
        $self->createitem($views, 'headers', 'admin', 'litepubl\admin\views\Head');
        $self->createitem($views, 'jsmerger', 'admin', 'litepubl\admin\views\Js');
        $self->createitem($views, 'cssmerger', 'admin', '\litepubl\admin\Css');
    }

    $menu = $self->createitem(0, 'menu', 'editor', 'litepubl\admin\menu\Manager');
 {
        $id = $self->createitem($menu, 'edit', 'editor', 'litepubl\admin\menu\Editor');
        $self->items[$id]['title'] = Lang::get('menu', 'addmenu');
        $id = $self->createitem($menu, 'editfake', 'editor', 'litepubl\admin\menu\Editor');
        $self->items[$id]['title'] = Lang::get('menu', 'addfake');
    }

    $opt = $self->createitem(0, 'options', 'admin', 'litepubl\admin\options\Options'); {
        $self->createitem($opt, 'home', 'admin', 'litepubl\admin\options\Home');
        $self->createitem($opt, 'mail', 'admin', 'litepubl\admin\options\Mail');
        $self->createitem($opt, 'rss', 'admin', 'litepubl\admin\options\Rss');
        $self->createitem($opt, 'view', 'admin', 'litepubl\admin\options\View');
        $self->createitem($opt, 'files', 'admin', 'litepubl\admin\options\Options');
        $self->createitem($opt, 'comments', 'admin', 'litepubl\admin\comments\Options');
        $self->createitem($opt, 'ping', 'admin', 'litepubl\admin\options\Pinger');
        $self->createitem($opt, 'links', 'admin', 'litepubl\admin\options\Options');
        $self->createitem($opt, 'cache', 'admin', 'litepubl\admin\options\Options');
        $self->createitem($opt, 'catstags', 'admin', 'litepubl\admin\options\Options');
        $self->createitem($opt, 'secure', 'admin', 'litepubl\admin\options\Secury');
        $self->createitem($opt, 'robots', 'admin', 'litepubl\admin\options\Options');
        $self->createitem($opt, 'local', 'admin', 'litepubl\admin\options\LangMerger');
        $self->createitem($opt, 'parser', 'admin', 'litepubl\admin\options\Theme');
        $self->createitem($opt, 'notfound404', 'admin', 'litepubl\admin\options\Notfound404');
        $self->createitem($opt, 'redir', 'admin', 'litepubl\admin\options\Redirect');
    }

    $service = $self->createitem(0, 'service', 'admin', 'litepubl\admin\service\Service'); {
        $self->createitem($service, 'backup', 'admin', 'litepubl\admin\service\Backup');
        $self->createitem($service, 'upload', 'admin', 'litepubl\admin\service\Upload');
        $self->createitem($service, 'run', 'admin', 'litepubl\admin\service\Run');
    }

    $id = $self->addfake('/admin/logout/', Lang::i()->logout);
    $self->items[$id]['order'] = 9999999;
    $self->unlock();

    $redir = Redirector::i();
    $redir->add('/admin/', '/admin/posts/editor/');
}

function MenusUninstall($self) {
    //rmdir(. 'menus');
    
}