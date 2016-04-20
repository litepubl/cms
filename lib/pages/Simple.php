<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

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
        return \litepubl\core\Router::htmlheader(false);
    }

public function gettitle() {
}

    public function getCont() {
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