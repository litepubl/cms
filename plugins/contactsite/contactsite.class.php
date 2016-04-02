<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tcontactsite extends tmenu {

  public static function i($id = 0) {
    return static::iteminstance(__class__, $id);
  }

  protected function create() {
    parent::create();
    $this->cache = false;
    $this->data['subject'] = '';
    $this->data['errmesg'] = '';
    $this->data['success'] = '';
  }

  public function processform() {
    if (!isset($_POST['contactvalue'])) return '';
    $time = substr($_POST['contactvalue'], strlen('_contactform'));
    if (time() > $time) return $this->errmesg;
    $email = trim($_POST['email']);

    if (!tcontentfilter::ValidateEmail($email)) return sprintf('<p><strong>%s</strong></p>', tlocal::get('comment', 'invalidemail'));
    $url = trim($_POST['site']);
    if (empty($url) || strbegin($url, litepubl::$site->url)) return $this->errmesg;
    if ($s = http::get($url)) {
      if (!strpos($s, '<meta name="generator" content="Lite Publisher')) return $this->errmesg;
    } else {
      return $this->errmesg;
    }

    $content = trim($_POST['content']);
    if (strlen($content) <= 15) return sprintf('<p><strong>%s</strong></p>', tlocal::get('comment', 'emptycontent'));
    $content = "$url\n" . $_POST['sitetitle'] . "\n\n" . $content;
    tmailer::sendmail('', $email, '', litepubl::$options->email, $this->subject, $content);
    return $this->success;
  }

} //class