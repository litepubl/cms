<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
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
    }

    public function getBasename()
    {
        $theme = Theme::i();
        return 'widgetscache.' . $theme->name;
    }

    public function load()
    {
        if ($data = $this->getApp()->cache->get($this->getbasename())) {
            $this->data = $data;
            $this->afterload();
            return true;
        }

        return false;
    }

    public function savemodified()
    {
        if ($this->modified) {
            $this->modified = false;
            $this->getApp()->cache->set($this->getbasename() , $this->data);
        }
    }

    public function save()
    {
        if (!$this->modified) {
            $this->modified = true;
            $this->getApp()->onClose->on($this, 'saveModified');
        }
    }

    public function getContent($id, $sidebar, $onlybody = true)
    {
        if (isset($this->items[$id][$sidebar])) {
            return $this->items[$id][$sidebar];
        }

        return $this->setcontent($id, $sidebar, $onlybody);
    }

    public function setContent($id, $sidebar, $onlybody = true)
    {
        $widget = Widgets::i()->getwidget($id);

        if ($onlybody) {
            $result = $widget->getcontent($id, $sidebar);
        } else {
            $result = $widget->getwidget($id, $sidebar);
        }

        $this->items[$id][$sidebar] = $result;
        $this->save();
        return $result;
    }

    public function expired($id)
    {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();
        }
    }

    public function onclearcache()
    {
        $this->items = array();
        $this->modified = false;
    }

}

