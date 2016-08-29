<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

namespace litepubl\debug;

use Monolog\Handler\MailHandler;
use Monolog\Logger;
use litepubl\utils\Mailer;

class MailerHandler extends MailHandler
{
    protected $subject;

    public function __construct(string $subject, $level = Logger::ERROR, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    protected function send($content, array $records)
    {
        $content = wordwrap($content, 70);
            Mailer::sendToAdmin($this->subject, $content);
    }
}
