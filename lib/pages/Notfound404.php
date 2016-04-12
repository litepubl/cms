<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\pages;
use litepubl\view\Lang;

class Notfound404 extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
use \litepubl\view\ViewTrait;

    protected function create() {
        parent::create();
        $this->basename = 'notfound';
        $this->data['text'] = '';
        $this->data['notify'] = false;
    }

    public function httpheader() {
        return "<?php Header( 'HTTP/1.0 404 Not Found'); ?>" . turlmap::htmlheader(false);
    }

    public function getcont() {
        if ($this->notify) {
$this->sendmail();
}
        return parent::getcont();
    }

    private function sendmail() {
        $args = new targs();
        $args->url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $args->ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        tlocal::usefile('mail');
        $lang = tlocal::i('notfound');
        $theme = ttheme::i();

        $subject = $theme->parsearg($lang->subject, $args);
        $body = $theme->parsearg($lang->body, $args);

        tmailer::sendtoadmin($subject, $body, true);
    }

} //class