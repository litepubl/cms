<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\pages;
use litepubl\view\Lang;

class Forbidden extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
use \litepubl\view\EmptyViewTrait;

    protected function create() {
        parent::create();
        $this->basename = 'forbidden';
        $this->data['text'] = '';
    }

    public function httpheader() {
        return '<?php Header(\'HTTP/1.0 403 Forbidden\', true, 403); ?>' . \litepubl\core\Router::htmlheader(false);
    }

    public function getCont() {
        $this->cache = false;
        $schema = $this->getSchema();
        $theme = $schema->theme;
        if ($this->text) {
return $theme->simple($this->text);
}

        $lang = Lang::i('default');
            return $theme->simple($theme->h($lang->forbidden)));
    }

}