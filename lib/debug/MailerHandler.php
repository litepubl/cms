<?php

namespace litepubl\debug;

use Monolog\Logger;
use Monolog\Handler\MailHandler;
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
            Mailer::sentToAdmin($this->subject, $content);
    }
}