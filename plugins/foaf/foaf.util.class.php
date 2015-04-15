<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tfoafutil  extends tevents {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getfoafdom(&$foafurl) {
    $s = http::get($foafurl);
    if (!$s) return false;
    if (!$this->isfoaf($s)) {
      $foafurl = $this->discoverfoafurl($s);
      if (!$foafurl) return false;
      $s = http::get($foafurl);
      if (!$s) return false;
      if (!$this->isfoaf($s)) return false;
    }
    
    
    $dom = new domDocument;
    $dom->loadXML($s);
    return $dom;
  }
  
  public function getinfo($url) {
    $dom = $this->getfoafdom($url);
    if (!$dom) return false;
    $person = $dom->getElementsByTagName('RDF')->item(0)->getElementsByTagName('Person')->item(0);
    $result = array(
    'nick' => $person->getElementsByTagName('nick')->item(0)->nodeValue,
    'url' => $person->getElementsByTagName('weblog')->item(0)->attributes->getNamedItem('resource')->nodeValue,
    'foafurl' => $url
    );
    return $result;
  }
  
  private function isfoaf(&$s) {
    return strpos($s, '<rdf:RDF') > 0;
  }
  
  private function discoverfoafurl(&$s) {
    $tag = '<link rel="meta" type="application/rdf+xml" title="FOAF" href="';
    if ($i = strpos($s, $tag)) {
      $i = $i + strlen($tag);
      $i2 = strpos($s, '"', $i);
      return substr($s, $i, $i2 - $i);
    } else {
      $tag = str_replace('"', "'", $tag);
      if ($i = strpos($s, $tag)) {
        $i = $i + strlen($tag);
        $i2 = strpos($s, "'", $i);
        return substr($s, $i, $i2 - $i);
      }
    }
    return false;
  }
  
  public function checkfriend($foafurl) {
    $dom = $this->getfoafdom($foafurl);
    if (!$dom) return false;
    
    $knows = $dom->getElementsByTagName('knows');
    foreach ($knows  as $node) {
      $blog = $node->getElementsByTagName('Person')->item(0)->getElementsByTagName('weblog')->item(0)->attributes->getNamedItem('resource')->nodeValue;
      $seealso = $node->getElementsByTagName('Person')->item(0)->getElementsByTagName('seeAlso')->item(0)->attributes->getNamedItem('resource')->nodeValue;
      if (($blog == litepublisher::$site->url . '/') && ($seealso == litepublisher::$site->url . '/foaf.xml')) {
        return true;
      }
    }
    return false;
  }
  
  public function check() {
    $result = '';
    $lang = tlocal::i('foaf');
    $foaf = tfoaf::i();
    $items = $foaf->getapproved(0);
    foreach ($items as $id) {
      $item = $foaf->getitem($item);
      if (!$this->checkfriend($item['foafurl'])) {
        $result.= sprintf($lang->mailerror, $item['nick'], $item['blog'], $item['url']);
        $foaf->lock();
        $foaf->setvalue($id, 'errors', ++$item['errors']);
        if ($item['errors'] > 3) {
          $foaf->setstatus($id, 'error');
          $result.= sprintf($lang->manyerrors, $item['errors']);
        }
        $foaf->unlock();
      }
    }
    
    if($result != '') {
      $result = $lang->founderrors . $result;
      $result = str_replace('\n', "\n", $result);
      $args = targs::i();
      $args->errors = $result;
      
      tlocal::usefile('mail');
      $lang = tlocal::i('mailfoaf');
      $theme = ttheme::i();
      
      $subject = $theme->parsearg($lang->errorsubj, $args);
      $body = $theme->parsearg($lang->errorbody, $args);
      
      tmailer::sendtoadmin($subject, $body);
    }
  }
  
}//class