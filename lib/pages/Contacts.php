<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\pages;

use litepubl\core\Context;
use litepubl\utils\Mailer;
use litepubl\view\Filter;
use litepubl\view\Lang;

class Contacts extends SingleMenu
{

    protected function create()
    {
        parent::create();
        $this->data['extra'] = [];
        $this->data['subject'] = '';
        $this->data['errmesg'] = '';
        $this->data['success'] = '';
    }

    public function request(Context $context)
    {
        $context->response->cache = false;

        parent::request($context);
    }
    public function processForm()
    {
        if (!isset($_POST['contactvalue'])) {
            return '';
        }

        $time = substr($_POST['contactvalue'], strlen('_contactform'));
        if (time() > $time) {
            return $this->errmesg;
        }

        $email = trim($_POST['email']);

        if (!Filter::ValidateEmail($email)) {
            return sprintf('<p><strong>%s</strong></p>', Lang::get('comment', 'invalidemail'));
        }

        $content = trim($_POST['content']);
        if (strlen($content) <= 10) {
            return sprintf('<p><strong>%s</strong></p>', Lang::get('comment', 'emptycontent'));
        }

        if (false !== strpos($content, '<a href')) {
            return $this->errmesg;
        }

        foreach ($this->data['extra'] as $name => $title) {
            if (isset($_POST[$name])) {
                $content.= sprintf("\n\n%s:\n%s", $title, trim($_POST[$name]));
            }
        }

        Mailer::sendmail('', $email, '', $this->getApp()->options->email, $this->subject, $content);
        return $this->success;
    }

    public function update()
    {
        $this->externalFunc(get_class($self), 'Update', null);
    }
}
