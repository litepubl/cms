<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

namespace litepubl\widget;

class Custom extends Widget
{
    public $items;

    protected function create()
    {
        parent::create();
        $this->basename = 'widgets.custom';
        $this->adminclass = '\litepubl\admin\widget\Custom';
        $this->addmap('items', array());
        $this->addevents('added', 'deleted');
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
        $this->items[$id] = array(
            'title' => $title,
            'content' => $content,
            'template' => $template
        );

        $sidebars = Sidebars::i($idschema);
        $sidebars->add($id);
        $widgets->unlock();
        $this->save();
        $this->added($id);
        return $id;
    }

    public function edit(int $id, string $title, string $content, string $template)
    {
        $this->items[$id] = array(
            'title' => $title,
            'content' => $content,
            'template' => $template
        );
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
            $this->deleted($id);
        }
    }

    public function widgetDeleted(int $id)
    {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();
        }
    }
}
