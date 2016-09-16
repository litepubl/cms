<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\widget;

use litepubl\view\Args;
use litepubl\view\Lang;

class Meta extends Widget
{
    public $items;

    protected function create()
    {
        parent::create();
        $this->basename = 'widget.meta';
        $this->template = 'meta';
        $this->adminclass = '\litepubl\admin\widget\Meta';
        $this->addmap('items', []);
    }

    public function getDeftitle(): string
    {
        return Lang::get('default', 'subscribe');
    }

    public function add(string $name, string $url, string $title)
    {
        $this->items[$name] = [
            'enabled' => true,
            'url' => $url,
            'title' => $title
        ];
        $this->save();
    }

    public function delete(string $name)
    {
        if (isset($this->items[$name])) {
            unset($this->items[$name]);
            $this->save();
        }
    }

    public function getContent(int $id, int $sidebar): string
    {
        $result = '';
        $view = $this->getView();
        $tml = $view->getItem('meta', $sidebar);
        $metaclasses = $view->getTml($sidebar, 'meta', 'classes');
        $args = new Args();

        foreach ($this->items as $name => $item) {
            if (!$item['enabled']) {
                continue;
            }

            $args->add($item);

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
