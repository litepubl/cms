<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

namespace litepubl\widget;

class Contextual extends Widget
{
    private $item;

    private function isValue(string $name): bool
    {
        return in_array(
            $name, [
            'ajax',
            'order',
            'sidebar'
            ]
        );
    }

    public function __get($name)
    {
        if ($this->isvalue($name)) {
            if (!$this->item) {
                $widgets = Widgets::i();
                $this->item = & $widgets->finditem($widgets->find($this));
            }
            return $this->item[$name];
        }
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if ($this->isvalue($name)) {
            if (!$this->item) {
                $widgets = Widgets::i();
                $this->item = & $widgets->finditem($widgets->find($this));
            }
            $this->item[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function save()
    {
        parent::save();
        Widgets::i()->save();
    }
}
