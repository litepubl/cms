<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\pages;
use litepubl\view\Lang;

class Forbidden extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
use \litepubl\view\ViewTrait;

    protected function create() {
        parent::create();
        $this->basename = 'forbidden';
        $this->data['text'] = '';
    }

    public function httpheader() {
        return '<?php Header(\'HTTP/1.0 403 Forbidden\', true, 403); ?>' . turlmap::htmlheader(false);
    }

    public function getcont() {
        $this->cache = false;
        $schema = $this->getSchema();
        $theme = $schema->theme;
        if ($this->text) {
return $theme->simple($this->text);
}

        $lang = Lang::i('default');
        if ($this->basename == 'forbidden') {
            return $theme->simple(sprintf('<h1>%s</h1>', $lang->forbidden));
        } else {
            return $theme->parse($theme->templates['content.notfound']);
        }
    }

}
