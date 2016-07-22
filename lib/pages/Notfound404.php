<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.01
  */

namespace litepubl\pages;

use litepubl\core\Context;
use litepubl\utils\Mailer;
use litepubl\view\Args;
use litepubl\view\Lang;

class Notfound404 extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
    use \litepubl\view\EmptyViewTrait;

    protected function create()
    {
        parent::create();
        $this->basename = 'notfound';
        $this->data['text'] = '';
        $this->data['notify'] = false;
    }

    public function request(Context $context)
    {
        $context->response->status = 404;
    }

    public function getTitle(): string
    {
        return Lang::i()->notfound;
    }

    public function getCont(): string
    {
        if ($this->notify) {
            $this->sendmail();
        }

        $schema = $this->getSchema();
        $theme = $schema->theme;
        if ($this->text) {
            return $theme->simple($this->text);
        }

        return $theme->notfound;
    }

    private function sendMail()
    {
        $args = new Args();
        $args->url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $args->ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        Lang::usefile('mail');
        $lang = Lang::i('notfound');
        $theme = $this->getSchema()->theme;

        $subject = $theme->parseArg($lang->subject, $args);
        $body = $theme->parseArg($lang->body, $args);

        Mailer::sendtoadmin($subject, $body, true);
    }
}
