<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmailer {
  private static $hold;
  
  protected static function  send($from, $to, $subj, $body) {
    $options =     litepublisher::$options;
    $subj = $subj == '' ? '' : '=?utf-8?B?'.@base64_encode($subj). '?=';
    $date = date('r');
    if (litepublisher::$debug) {
      $dir = litepublisher::$paths->data . 'logs' . DIRECTORY_SEPARATOR ;
      if (!is_dir($dir)) {
        mkdir($dir, 0777);
        @chmod($dir, 0777);
      }
      $eml = "To: $to\nSubject: $subj\nFrom: $from\nReply-To: $from\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit\nDate: $date\nX-Priority: 3\nX-Mailer: LitePublisher mailer\n\n$body";
      return file_put_contents($dir . date('H-i-s.d.m.Y.') . microtime(true) . '.eml', $eml);
    }
    
    return mail($to, $subj, $body,
    "To: $to\nFrom: $from\nReply-To: $from\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit\nDate: $date\nX-Priority: 3\nX-Mailer: Lite Publisher ver " . litepublisher::$options->version);
  }
  
  public static function  sendmail($fromname, $fromemail, $toname, $toemail, $subj, $body) {
    if (litepublisher::$options->mailer == 'smtp') {
      $mailer = TSMTPMailer ::i();
      return $mailer->mail($fromname, $fromemail, $toname, $toemail, $subj, $body);
    }
    
    return self::send(self::CreateEmail($fromname, $fromemail), self::CreateEmail($toname, $toemail), $subj, $body);
  }
  
  public static function CreateEmail($name, $email) {
    if (empty($name)) return $email;
    return   '=?utf-8?B?'.@base64_encode($name). '?=' . " <$email>";
  }
  
  public static function sendtoadmin($subject, $body, $onshutdown = false) {
    if ($onshutdown) {
      if (!isset(self::$hold)) {
        self::$hold = array();
        register_shutdown_function(array(__class__, 'onshutdown'));
      }
      self::$hold[] = array('subject' => $subject, 'body' => $body);
      return;
    }
    
    return self::sendmail(litepublisher::$site->name, litepublisher::$options->fromemail,
    'admin', litepublisher::$options->email, $subject, $body);
  }
  
  public static function onshutdown() {
    if (litepublisher::$options->mailer == 'smtp') {
      $mailer = TSMTPMailer ::i();
      if ($mailer->auth()) {
        $fromname= litepublisher::$site->name;
        $fromemail = litepublisher::$options->fromemail;
        $toemail = litepublisher::$options->email;
        
        foreach (self::$hold as $i => $item) {
          $mailer->send($fromname, $fromemail, 'admin', $toemail, $item['subject'], $item['body'], false);
          unset(self::$hold[$i]);
        }
        $mailer->close();
      }
    } else {
      foreach (self::$hold as $i => $item) {
        self::sendtoadmin($item['subject'], $item['body'], false);
        unset(self::$hold[$i]);
      }
    }
  }
  
  public static function sendlist(array $list) {
    if (!count($list)) return;
    if (litepublisher::$options->mailer == 'smtp') {
      $mailer = TSMTPMailer ::i();
      if ($mailer->auth()) {
        foreach ($list as $item) {
          $mailer->send($item['fromname'], $item['fromemail'], $item['toname'], $item['toemail'], $item['subject'], $item['body']);
        }
        $mailer->close();
        return true;
      }
      return false;
    } else {
      foreach ($list as $item) {
        self::sendmail($item['fromname'], $item['fromemail'], $item['toname'], $item['toemail'], $item['subject'], $item['body']);
      }
    }
  }
  
  public static function  SendAttachmentToAdmin($subj, $body, $filename, $attachment) {
    return self::sendattachment(litepublisher::$site->name, litepublisher::$options->fromemail,
    'admin', litepublisher::$options->email,
    $subj, $body, $filename, $attachment);
  }
  
  public static function  sendattachment($fromname, $fromemail, $toname, $toemail, $subj, $body, $filename, $attachment) {
    $subj = $subj == '' ? '' : '=?utf-8?B?'.@base64_encode($subj). '?=';
    $date = date('r');
    $from = self::CreateEmail($fromname, $fromemail);
    $to =  self::CreateEmail($toname, $toemail);
    
    $boundary = md5(microtime());
    $textpart = "--$boundary\nContent-Type: text/plain; charset=\"UTF-8\"\nContent-Transfer-Encoding: base64\n\n";
    $textpart .= base64_encode($body);
    
    $attachpart = "--$boundary\nContent-Type: application/octet-stream; name=\"$filename\"\nContent-Disposition: attachment; filename=\"$filename\"\nContent-Transfer-Encoding: base64\n\n";
    $attachpart .= base64_encode($attachment);
    
    $body = $textpart . "\n\n". $attachpart . "\n\n";
    $options = litepublisher::$options;
    if (litepublisher::$debug)
    return file_put_contents(litepublisher::$paths->data . 'logs' . DIRECTORY_SEPARATOR  . date('H-i-s.d.m.Y.\e\m\l'),
    "To: $to\nSubject: $subj\nFrom: $from\nReply-To: $from\nMIME-Version: 1.0\nContent-Type: multipart/mixed; boundary=\"$boundary\"\nDate: $date\nX-Priority: 3\nX-Mailer: Lite Publisher ver $options->version\n\n". $body);
    
    return mail($to, $subj, $body,
    "From: $from\nReply-To: $from\nMIME-Version: 1.0\nContent-Type: multipart/mixed; boundary=\"$boundary\"\nDate: $date\nX-Priority: 3\nX-Mailer: Lite Publisher ver " . litepublisher::$options->version);
  }
  
} //class

class TSMTPMailer extends tevents {
  private $smtp;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'smtpmailer';
    $this->data = $this->data +  array(
    'host' => '',
    'login' => '',
    'password' => '',
    'port' => 25
    );
  }
  
  public function Mail($fromname,  $fromemail, $toname, $toemail, $subj, $body) {
    if ($this->auth()) {
      $this->send($fromname,  $fromemail, $toname, $toemail, $subj, $body);
      $this->close();
      return true;
    }
    return false;
  }
  
  public function auth() {
    litepublisher::$classes->include_file(litepublisher::$paths->libinclude . 'class-smtp.php');
    $this->smtp = new SMTP();
    if($this->smtp->Connect($this->host, $this->port, 10)) {
      $this->smtp->Hello($_SERVER['SERVER_NAME']);
      if ($this->smtp->Authenticate($this->login, $this->password)) {
        return true;
      }
    }
    return false;
  }
  
  public function send($fromname,  $fromemail, $toname, $toemail, $subj, $body) {
    if ($this->smtp->Mail($this->login) && $this->smtp->Recipient($toemail)) {
      $options =     litepublisher::$options;
      $subj = $subj == '' ? '' : '=?utf-8?B?'.@base64_encode($subj). '?=';
      $date = date('r');
      $from = tmailer::CreateEmail($fromname, $fromemail);
      $to = tmailer::CreateEmail($toname, $toemail);
      
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
  
}//class