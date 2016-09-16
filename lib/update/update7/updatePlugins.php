<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\update;

use litepubl\core\DBManager;
use litepubl\core\Plugins;
use litepubl\core\litepubl;
use litepubl\pages\FakeMenu;
use litepubl\pages\Menus;
use litepubl\tag\Tags;
use litepubl\view\AutoVars;
use litepubl\view\Js;
use litepubl\view\Parser;

function updatePlugins()
{
    $plugins = Plugins::i();    
    if (isset($plugins->items['wiki'])) {
        $vars = AutoVars::i();
        $vars->items['wiki'] = 'litepubl\plugins\wikiwords\Wiki';
        $vars->save();
    }
    
    if (isset($plugins->items['ulogin'])) {
        Parser::i()->addTags('plugins/ulogin/resource/theme.txt', false);
        $man = DBManager::i();
        $man->addEnum('ulogin', 'service', 'uid');
        $man->addEnum('ulogin', 'service', 'instagram');
        $man->addEnum('ulogin', 'service', 'wargaming');
        
        $ulogin = \litepubl\plugins\ulogin\Ulogin::i();
        unset($ulogin->data['panel']);
        $ulogin->save();
    }
    
    if (isset($plugins->items['downloatitem'])) {
        $js = Js::i();
        $js->lock();
        $js->deleteFile('default', '/plugins/downloaditem/downloaditem.min.js');
        $js->unlock();
        
        $parser = Parser::i();
        $parser->unbind('tdownloaditems');
        $parser->addTags('plugins/downloaditem/resource/theme.txt', 'plugins/downloaditem/resource/theme.ini');
        
        $man = DBManager::i();
        if ($man->columnExists('downloaditems', 'votes')) {
            $man->deleteColumn('downloaditems', 'votes');
        }
        
        if ($man->columnExists('downloaditems', 'poll')) {
            $man->deleteColumn('downloaditems', 'poll');
        }
        
        $tags = Tags::i();
        $tags->loadAll();
        $idplugin = litepubl::$app->options->downloaditem_plugintag;
        $item = $tags->getItem($idplugin);
        if ((int) $item['parent'] == 0) {
            $menus = Menus::i();
            $menus->lock();
            $id = $menus->url2id('/downloads.htm');
            $title = $menus->getValue($id, 'title');
            $menus->deleteTree($id);
            
            $idparent = $tags->add(0, $title);
            $tags->edit($idparent, $title, '/downloads.htm');
            $tags->setValue($idparent, 'includechilds', '1');
            
            $tags->setvalue($idplugin, 'parent', $idparent);
            $tags->setValue($idplugin, 'includechilds', '1');
            $idtheme = litepubl::$app->options->downloaditem_themetag;
            $tags->setvalue($idtheme, 'parent', $idparent);
            $tags->setValue($idtheme, 'includechilds', '1');
            $item = $tags->getItem($idparent);
            $menu = new FakeMenu();
            $menu->url = $item['url'];
            $menu->title = $item['title'];
            $id = $menus->addFakeMenu($menu);
            
            $item = $tags->getItem($idplugin);
            $menu = new FakeMenu();
            $menu->parent = $id;
            $menu->url = $item['url'];
            $menu->title = $item['title'];
            $menus->addFakeMenu($menu);
            
            $item = $tags->getItem($idtheme);
            $menu = new FakeMenu();
            $menu->parent = $id;
            $menu->url = $item['url'];
            $menu->title = $item['title'];
            $menus->addFakeMenu($menu);
            $menus->unlock();
            
            $redir = Redirector::i();
            $redir->lock();
            $redir->add('/downloads/plugins.htm', $tags->getValue($idplugin, 'url'));
            $redir->add('/downloads/themes.htm', $tags->getValue($idtheme, 'url'));
            $redir->unlock();
        }
    }
}
