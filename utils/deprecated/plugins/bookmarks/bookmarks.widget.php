<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class tbookmarkswidget extends tlinkswidget {

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->basename = 'widget.bookmarks';
        $this->cache = 'nocache';
        $this->data['redir'] = false;
        $this->redirlink = '/addtobookmarks.htm';
    }

    public function getDeftitle() {
        $about = tplugins::getabout(tplugins::getname(__file__));
        return $about['name'];
    }

    public function getWidget($id, $sidebar) {
        $widgets = twidgets::i();
        return $widgets->getinline($id, $sidebar);
    }

    public function getContent($id, $sidebar) {
        if ( $this->getApp()->router->is404) {
 return '';
}


        $result = '';
        $a = array(
            '$url' => urlencode( $this->getApp()->site->url .  $this->getApp()->router->url) ,
            '$title' => urlencode(ttemplate::i()->title)
        );
        $redirlink =  $this->getApp()->site->url . $this->redirlink .  $this->getApp()->site->q . strtr('url=$url&title=$title&id=', $a);
        $iconurl =  $this->getApp()->site->files . '/plugins/bookmarks/icons/';
        $theme = ttheme::i();
        $tml = $theme->getwidgetitem('links', $sidebar);
        $args = new Args();
        $args->subcount = '';
        $args->subitems = '';
        $args->rel = 'link bookmark';
        foreach ($this->items as $id => $item) {
            $args->id = $id;
            $args->title = $item['title'];
            $args->text = $item['title'];
            if ($this->redir) {
                $args->link = $redirlink . $id;
            } else {
                $args->link = strtr($item['url'], $a);
            }

            $args->icon = $item['text'] == '' ? '' : sprintf('<img src="%s%s" alt="%s" />', $iconurl, $item['text'], $item['title']);
            $result.= $theme->parsearg($tml, $args);
        }

        return $theme->getwidgetcontent($result, 'links', $sidebar);
    }

    public function request($arg) {
        $this->cache = false;
        $id = empty($_GET['id']) ? 1 : (int)$_GET['id'];
        if (!isset($this->items[$id])) {
 return 404;
}


        $url = $this->items[$id]['url'];
        $a = array(
            '$url' => urlencode($_GET['url']) ,
            '$title' => urlencode($_GET['title'])
        );
        $url = strtr($url, $a);
        return  $this->getApp()->router->redir($url);
    }

} //class