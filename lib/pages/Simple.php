<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\pages;

use litepubl\core\Context;
use litepubl\core\Request;
use litepubl\core\Response;
use litepubl\view\MainView;

class Simple extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
    use \litepubl\view\EmptyViewTrait;

    public $text;
    public $html;

    protected function create()
    {
        parent::create();
        $this->basename = 'simplecontent';
    }

    public function request(Context $context)
    {
        $context->response->cache = false;
    }

    public function gettitle(): string
    {
        return '';
    }

    public function getCont(): string
    {
        $result = empty($this->text) ? $this->html : sprintf("<h2>%s</h2>\n", $this->text);
        return $this->getSchema()->theme->simple($result);
    }

    public static function html($content)
    {
        $self = static ::i();
        $self->html = $content;
        return $self->render();
    }

    public static function content($content)
    {
        $self = static ::i();
        $self->text = $content;
        return $self->render();
    }

    public function render()
    {
        $context = new Context(new Request('', ''), new Response());

        $context->model = $this;
        $context->view = $this;
        MainView::i()->render($context);
        return $context->response->body;
    }
}
