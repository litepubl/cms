<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl;

use litepubl\core\Plugins;
use litepubl\core\Str;
use litepubl\view\Args;
use litepubl\widget\View;

class tfriendswidget extends twidget
{

    protected function create()
    {
        parent::create();
        $this->basename = 'widget.friends';
        $this->template = 'friends';
        $this->adminclass = 'tadminfriendswidget';
        $this->data['maxcount'] = 0;
        $this->data['redir'] = true;
        $this->data['redirlink'] = '/foaflink.htm';
    }

    public function getDeftitle(): string
    {
        $about = Plugins::getabout(Plugins::getname(__file__));
        return $about['name'];
    }

    public function getContent(int $id, int $sidebar): string
    {
        $foaf = tfoaf::i();
        $items = $foaf->getapproved($this->maxcount);
        if (count($items) == 0) {
            return '';
        }

        $result = '';
        $url = $this->getApp()->site->url;
        $redirlink = $this->getApp()->site->url . $this->redirlink . $this->getApp()->site->q . 'id=';
        $view = new View();
        $tml = $view->getItem('friends', $sidebar);
        $args = new Args();
        $args->subcount = '';
        $args->subitems = '';
        $args->$icon = '';
        $args->rel = 'friend';
        foreach ($items as $id) {
            $item = $foaf->getitem($id);
            $args->add($item);
            $args->anchor = $item['title'];
            if ($this->redir && !Str::begin($item['url'], $url)) {
                $args->url = $redirlink . $id;
            }
            $result.= $view->theme->parseArg($tml, $args);
        }

        return $view->getContent($result, 'friends', $sidebar);
    }

    public function request($arg)
    {
        $id = empty($_GET['id']) ? 1 : (int)$_GET['id'];
        $foaf = tfoaf::i();
        if (!$foaf->itemExists($id)) {
            return 404;
        }

        $item = $foaf->getitem($id);
        $this->cache = false;
        return sprintf('<?php  $this->getApp()->router->redir(\'%s\'); ?>', $item['url']);
    }

}

