<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace Page;

class Contacts extends Base
{
    public $email = 'input[name=email]';
    public $message = '#editor-content';
    public $submit = '#submitbutton-update';

    public function sendForm($email, $message)
    {
        $i = $this->tester;
        $i->fillField($this->email, $email);
        $i->fillField($this->message, $message);
        $i->click($this->submit);
        $i->checkError();
        return $this;
    }
}
