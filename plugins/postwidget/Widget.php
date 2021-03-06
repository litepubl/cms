<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\postwidget;

use litepubl\core\Arr;
use litepubl\core\Event;
use litepubl\widget\Widgets;

class Widget extends \litepubl\widget\Contextual
{
    const POSTCLASS = 'litepubl\post\Post';
    public $items;

    protected function create()
    {
        parent::create();
        $this->cache = 'nocache';
        $this->adminclass = __NAMESPACE__ . '\Admin';
        $this->basename = 'widget.postcat';
        $this->addmap('items', []);
    }

    public function add(string $title, string $content, string $template, array $cats): int
    {
        $widgets = Widgets::i();
        $widgets->lock();
        $id = $widgets->addclass($this, static::POSTCLASS);
        $widgets->items[$id]['title'] = $title;
        $widgets->unlock();

        $this->items[$id] = [
            'title' => $title,
            'content' => $content,
            'template' => $template,
            'cats' => $cats
        ];

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

    public function widgetDeleted(Event $event)
    {
        if (isset($this->items[$event->id])) {
            unset($this->items[$event->id]);
            $this->save();
        }
    }

    public function tagDeleted(Event $event)
    {
        foreach ($this->items as & $item) {
            Arr::deleteValue($item['cats'], $event->id);
        }
        $this->save();
    }

    public function getWidget(int $id, int $sidebar): string
    {
        if (!isset($this->items[$id])) {
            return '';
        }

        $item = $this->items[$id];
        $post = $this->getContext(static::POSTCLASS);
        if (0 == count(array_intersect($item['cats'], $post->categories))) {
            return '';
        }

        if (!$item['template']) {
            return $item['content'];
        }

        return $this->getView()->getWidget($id, $sitebar, $item['title'], $item['content'], $item['template']);
    }

    public function getTitle(int $id): string
    {
        if (isset($this->items[$id])) {
            return $this->items[$id]['title'];
        }

        return '';
    }

    public function getContent(int $id, int $sidebar): string
    {
        if (isset($this->items[$id])) {
            return $this->items[$id]['content'];
        }

        return '';
    }
}
