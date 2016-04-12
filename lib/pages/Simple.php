<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\pages;
use litepubl\view\MainView;

class Simple extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
use \litepubl\view\EmptyViewTrait;

    public $text;
    public $html;

    protected function create() {
        parent::create();
        $this->basename = 'simplecontent';
    }

    public function httpheader() {
        return turlmap::htmlheader(false);
    }

    public function getcont() {
        $result = empty($this->text) ? $this->html : sprintf("<h2>%s</h2>\n", $this->text);
        return $this->getSchema()->theme->simple($result);
    }

    public static function html($content) {
        $self = static ::i();
        $self->html = $content;
return MainControler::i()->request($self);
    }

    public static function content($content) {
        $self = static ::i();
        $self->text = $content;
return MainControler::i()->request($self);
    }

}