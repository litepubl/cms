<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\widget;
    use litepubl\core\Context;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\core\Str;

class Links extends Widget implements \litepubl\core\ResponsiveInterface
 {
    public $items;
    public $autoid;
    public $redirlink;

    protected function create() {
        parent::create();
        $this->addevents('added', 'deleted');
        $this->basename = 'widgets.links';
        $this->template = 'links';
        $this->adminclass = '\litepubl\admin\widget\Links';
        $this->addmap('items', array());
        $this->addmap('autoid', 0);
        $this->redirlink = '/linkswidget/';
        $this->data['redir'] = false;
    }

    public function getDeftitle() {
        return Lang::get('default', 'links');
    }

    public function getContent($id, $sidebar) {
        if (count($this->items) == 0) {
 return '';
}


        $result = '';
$view = new View();
        $tml = $view->getItem('links', $sidebar);
        $redirlink =  $this->getApp()->site->url . $this->redirlink .  $this->getApp()->site->q . 'id=';
        $url =  $this->getApp()->site->url;
        $args = new Args();
        $args->subcount = '';
        $args->subitems = '';
        $args->icon = '';
        $args->rel = 'link';
        foreach ($this->items as $id => $item) {
            $args->add($item);
            $args->id = $id;
            if ($this->redir && !Str::begin($item['url'], $url)) {
                $args->link = $redirlink . $id;
            } else {
                $args->link = $item['url'];
            }
            $result.= $view->theme->parseArg($tml, $args);
        }

        return $view->getContent($result, 'links', $sidebar);
    }

    public function add($url, $title, $text) {
        $this->items[++$this->autoid] = array(
            'url' => $url,
            'title' => $title,
            'text' => $text
        );

        $this->save();
        $this->added($this->autoid);
        return $this->autoid;
    }

    public function edit($id, $url, $title, $text) {
        $id = (int)$id;
        if (!isset($this->items[$id])) {
 return false;
}


        $this->items[$id] = array(
            'url' => $url,
            'title' => $title,
            'text' => $text
        );
        $this->save();
    }

    public function delete($id) {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();
             $this->getApp()->cache->clear();
        }
    }

    public function request(Context $context)
    {
    $response = $context->response;
        $response->cache = false;
        $id = empty($_GET['id']) ? 1 : (int)$_GET['id'];
        if (!isset($this->items[$id])) {
$response->status = 404;
 return;
}

$response->redir($this->items[$id]['url']);
    }

}