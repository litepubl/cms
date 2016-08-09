<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\widget;

use litepubl\core\Event;

/**
 * Widgets with editable content
 *
 * @property-write callable $added
 * @property-write callable $deleted
 * @method         array added(array $params)
 * @method         array deleted(array $params) triggered when item has been deleted
 */

class Custom extends Widget
{
    public $items;

    protected function create()
    {
        parent::create();
        $this->basename = 'widgets.custom';
        $this->adminclass = '\litepubl\admin\widget\Custom';
        $this->addMap('items', []);
        $this->addEvents('added', 'deleted');
    }

    public function getWidget(int $id, int $sidebar): string
    {
        if (!isset($this->items[$id])) {
            return '';
        }

        $item = $this->items[$id];
        if (!$item['template']) {
            return $item['content'];
        }

        return $this->getview()->getWidget($id, $sidebar, $item['title'], $item['content'], $item['template']);
    }

    public function getTitle(int $id): string
    {
        return $this->items[$id]['title'];
    }

    public function getContent(int $id, int $sidebar): string
    {
        return $this->items[$id]['content'];
    }

    public function add(int $idschema, string $title, string $content, string $template): int
    {
        $widgets = Widgets::i();
        $widgets->lock();
        $id = $widgets->addExt($this, $title, $template);
        $this->items[$id] = [
            'title' => $title,
            'content' => $content,
            'template' => $template
        ];

        $sidebars = Sidebars::i($idschema);
        $sidebars->add($id);
        $widgets->unlock();
        $this->save();
        $this->added(['id' => $id]);
        return $id;
    }

    public function edit(int $id, string $title, string $content, string $template)
    {
        $this->items[$id] = [
            'title' => $title,
            'content' => $content,
            'template' => $template
        ];
        $this->save();

        $widgets = Widgets::i();
        $widgets->items[$id]['title'] = $title;
        $widgets->save();

        $this->getApp()->cache->clear();
    }

    public function delete($id)
    {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();

            $widgets = Widgets::i();
            $widgets->delete($id);
            $this->deleted(['id' => $id]);
        }
    }

    public function widgetDeleted(Event $event)
    {
        $id = $event->id;
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();
        }
    }
}
