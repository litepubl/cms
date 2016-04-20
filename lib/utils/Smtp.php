<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\utils;

class Smtp extends \litepubl\core\Events
{
    private $smtp;

    protected function create() {
        parent::create();
        $this->basename = 'smtpmailer';
        $this->data = $this->data + array(
            'host' => '',
            'login' => '',
            'password' => '',
            'port' => 25
        );
    }

    public function Mail($fromname, $fromemail, $toname, $toemail, $subj, $body) {
        if ($this->auth()) {
            $this->send($fromname, $fromemail, $toname, $toemail, $subj, $body);
            $this->close();
            return true;
        }
        return false;
    }

    public function auth() {
         $this->getApp()->classes->includeFile( $this->getApp()->paths->lib . 'include/class-smtp.php');
        $this->smtp = new \SMTP();
        if ($this->smtp->Connect($this->host, $this->port, 10)) {
            $this->smtp->Hello($_SERVER['SERVER_NAME']);
            if ($this->smtp->Authenticate($this->login, $this->password)) {
                return true;
            }
        }
        return false;
    }

    public function send($fromname, $fromemail, $toname, $toemail, $subj, $body) {
        if ($this->smtp->Mail($this->login) && $this->smtp->Recipient($toemail)) {
            $options =  $this->getApp()->options;
            $subj = $subj == '' ? '' : '=?utf-8?B?' . @base64_encode($subj) . '?=';
            $date = date('r');
            $from = Mailer::CreateEmail($fromname, $fromemail);
            $to = Mailer::CreateEmail($toname, $toemail);

            $this->smtp->data("To: $to\nFrom: $from\nReply-To: $from\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit\nDate: $date\nSubject: $subj\nX-Priority: 3\nX-Mailer: Lite Publisher ver $options->version\n\n$body");
            return true;
        }
        return false;
    }

    public function close() {
        if ($this->smtp) {
            $this->smtp->Quit();
            $this->smtp->Close();
            $this->smtp = false;
        }
    }

} //class