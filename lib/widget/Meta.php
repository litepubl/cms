<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\widget;
use litepubl\view\Lang;
use litepubl\view\Args;

class Meta extends Widget
 {
    public $items;

    protected function create() {
        parent::create();
        $this->basename = 'widget.meta';
        $this->template = 'meta';
        $this->adminclass = '\litepubl\admin\widget\Meta';
        $this->addmap('items', array());
    }

    public function getDeftitle() {
        return Lang::get('default', 'subscribe');
    }

    public function add($name, $url, $title) {
        $this->items[$name] = array(
            'enabled' => true,
            'url' => $url,
            'title' => $title
        );
        $this->save();
    }

    public function delete($name) {
        if (isset($this->items[$name])) {
            unset($this->items[$name]);
            $this->save();
        }
    }

    public function getContent($id, $sidebar) {
        $result = '';
$view = new View();
        $tml = $view->getItem('meta', $sidebar);
        $metaclasses = $view->getTml($sidebar, 'meta', 'classes');
        $args = new Args();
        foreach ($this->items as $name => $item) {
            if (!$item['enabled']) {
 continue;
}


            $args->add($item);
            $args->icon = '';
            $args->subcount = '';
            $args->subitems = '';
            $args->rel = $name;
            if ($name == 'profile') {
                $args->rel = 'author profile';
            }
            $args->class = isset($metaclasses[$name]) ? $metaclasses[$name] : '';
            $result.= $view->theme->parseArg($tml, $args);
        }

        if ($result == '') {
 return '';
}


        return $view->getContent($result, 'meta', $sidebar);
    }

}