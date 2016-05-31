<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\singletagwidget;

use litepubl\tag\Cats;
use litepubl\widget\Sidebars;
use litepubl\widget\View;
use litepubl\widget\Widgets;

class Widget extends \litepubl\widget\Widget
{
    public $items;
    public $tags;

    protected function create()
    {
        parent::create();
        $this->adminclass = __NAMESPACE__ . '\Admin';
        $this->basename = 'widget.singletag';
        $this->addmap('items', array());
        $this->tags = Cats::i();
    }

    public function getIdWidget(int $idtag): int
    {
        foreach ($this->items as $id => $item) {
            if ($idtag == $item['idtag']) {
                return $id;
            }
        }

        return 0;
    }

    public function add(int $idtag): int
    {
        $tag = $this->tags->getItem($idtag);
        $widgets = Widgets::i();
        $id = $widgets->addExt($this, $tag['title'], 'widget');
        $this->items[$id] = array(
            'idtag' => $idtag,
            'maxcount' => 10,
            'invertorder' => false
        );

        $sidebars = Sidebars::i();
        $sidebars->add($id);
        $this->save();
        return $id;
    }

    public function delete(int $id)
    {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();

            $widgets = Widgets::i();
            $widgets->delete($id);
        }
    }

    public function widgetDeleted(int $id)
    {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();
        }
    }

    public function tagDeleted(int $idtag)
    {
        if ($idwidget = $this->getidwidget($idtag)) {
            return $this->delete($idwidget);
        }
    }

    public function getTitle(int $id): string
    {
        if (isset($this->items[$id])) {
            if ($tag = $this->tags->getItem($this->items[$id]['idtag'])) {
                return $tag['title'];
            }
        }

        return '';
    }

    public function getContent(int $id, int $sidebar): string
    {
        if (!isset($this->items[$id])) {
            return '';
        }

        $item = $this->items[$id];
        $items = $this->tags->getSortedPosts($item['idtag'], $item['maxcount'], $item['invertorder']);
        if (!count($items)) {
            return '';
        }

        $view = new View();
        return $view->getPosts($items, $sidebar, '');
    }

}

