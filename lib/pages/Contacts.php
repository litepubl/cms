<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\pages;
use litepubl\utils\Mailer;
use litepubl\view\Filter;

class Contacts extends SingleMenu
{

    protected function create() {
        parent::create();
        $this->cache = false;
        $this->data['extra'] = array();
        $this->data['subject'] = '';
        $this->data['errmesg'] = '';
        $this->data['success'] = '';
    }

    public function processform() {
        if (!isset($_POST['contactvalue'])) return '';
        $time = substr($_POST['contactvalue'], strlen('_contactform'));
        if (time() > $time) return $this->errmesg;
        $email = trim($_POST['email']);

        if (!Filter::ValidateEmail($email)) {
return sprintf('<p><strong>%s</strong></p>', tlocal::get('comment', 'invalidemail'));
}

        $content = trim($_POST['content']);
        if (strlen($content) <= 10) {
return sprintf('<p><strong>%s</strong></p>', tlocal::get('comment', 'emptycontent'));
}

        if (false !== strpos($content, '<a href')) {
return $this->errmesg;
}

        foreach ($this->data['extra'] as $name => $title) {
            if (isset($_POST[$name])) {
                $content.= sprintf("\n\n%s:\n%s", $title, trim($_POST[$name]));
            }
        }

        Mailer::sendmail('', $email, '', litepubl::$options->email, $this->subject, $content);
        return $this->success;
    }

}