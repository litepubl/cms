<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\widget;

use litepubl\view\Theme;

class Cache extends \litepubl\core\Items
{
    private $modified;

    protected function create()
    {
        $this->dbversion = false;
        parent::create();
        $this->modified = false;
        $this->getApp()->cache->onClear([$this, 'onClearCache']);
    }

    public function getBasename(): string
    {
        $theme = Theme::i();
        return 'widgetscache.' . $theme->name;
    }

    public function load(): bool
    {
        if ($data = $this->getApp()->cache->get($this->getBasename())) {
            $this->data = $data;
            $this->afterLoad();
            return true;
        }

        return false;
    }

    public function commit()
    {
        if ($this->modified) {
            $this->modified = false;
            $this->getApp()->cache->set($this->getbasename(), $this->data);
        }
    }

    public function save()
    {
        if (!$this->modified) {
            $this->modified = true;
            $this->getApp()->onClose([$this, 'commit']);
        }
    }

    public function onClearCache()
    {
        $this->items = [];
        $this->modified = false;
    }

    public function getContent(int $id, int $sidebar): string
    {
        if (isset($this->items[$id][$sidebar]['content'])) {
            return $this->items[$id][$sidebar]['content'];
        }

        return $this->setcontent($id, $sidebar);
    }

    public function setContent(int $id, int $sidebar): string
    {
        $widget = Widgets::i()->getwidget($id);
            $result = $widget->getcontent($id, $sidebar);
        $this->items[$id][$sidebar]['content'] = $result;
        $this->save();
        return $result;
    }

    public function getWidget(int $id, int $sidebar): string
    {
        if (isset($this->items[$id][$sidebar]['widget'])) {
            return $this->items[$id][$sidebar]['widget'];
        }

        return $this->setWidget($id, $sidebar);
    }

    public function setWidget(int $id, int $sidebar): string
    {
        $widget = Widgets::i()->getwidget($id);
            $result = $widget->getwidget($id, $sidebar);
        $this->items[$id][$sidebar]['widget'] = $result;
        $this->save();
        return $result;
    }

    public function remove(int $id, string $cacheType)
    {
        switch ($cacheType) {
        case 'cache':
            $this->delete($id);
            break;


        case 'include':
            $filename = $this->getIncludeFilename($id);
            $this->getApp()->cache->delete($filename);
            break;
        }
    }

    public function removeWidget(Widget $widget)
    {
        $widgets = Widgets::i();
        foreach ($widgets->items as $id => $item) {
            if ($widget instanceof $item['class']) {
                $this->remove($id, $widget->cache);
            }
        }
    }

    public function getIncludeFilename(int $id): string
    {
        $theme = Theme::context();
        return sprintf('widget.%s.%d.php', $theme->name, $id);
    }

    public function getInclude(int $id, int $sidebar): string
    {
        $filename = $this->getIncludeFilename($id);
        $appCache = $this->getApp()->cache;
        if ($result = $appCache->getString($filename)) {
            return $result;
        }

            $widget = Widgets::i()->getWidget($id);
            $result = $widget->getContent($id, $sidebar);

        $appCache->setString($filename, $result);
        return $result;
    }
}
