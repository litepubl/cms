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

class Comment extends Base
{
    public $comment = '#comment';
    public $email = 'input[name=email]';
    public $submit = '#submit-button';
    public $postlink= '.post-bookmark';

    public function send(string $comment)
    {
        $i = $this->tester;
        //$i->fillField($this->email, $email);
        $i->fillField($this->comment, $comment);
        $this->screenshot('send');
        $i->click($this->submit);
        $i->checkError();
    }
}
