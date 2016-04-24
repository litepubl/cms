<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\pages;
use litepubl\core\Context;
use litepubl\view\Lang;

class Forbidden extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
use \litepubl\view\EmptyViewTrait;

    protected function create() {
        parent::create();
        $this->basename = 'forbidden';
        $this->data['text'] = '';
    }

    public function request(Context $context)
{
        $context->response->status = 403;
    }

public function gettitle() {
return Lang::i()->forbidden;
}

    public function getCont() {
        $this->cache = false;
        $schema = $this->getSchema();
        $theme = $schema->theme;
        if ($this->text) {
return $theme->simple($this->text);
}

        $lang = Lang::i('default');
            return $theme->simple($theme->h($lang->forbidden));
    }

}