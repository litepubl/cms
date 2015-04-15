<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tnode {
  public static function attr($node, $name, $value) {
    $attr = $node->ownerDocument->createAttribute($name);
    $attr->value = $value;
    $node->appendChild($attr);
    return $attr;
  }
  
  public static function add($node, $name) {
    $result = $node->ownerDocument->createElement($name);
    $node->appendChild($result);
    return $result;
  }
  
  public static function addvalue($node, $name, $value) {
    $result = $node->ownerDocument->createElement($name);
    $textnode = $node->ownerDocument->createTextNode($value);
    $result->appendChild($textnode);
    $node->appendChild($result);
    Return $result;
  }
  
  public static function addcdata($node, $name, $value) {
    $result = $node->ownerDocument->createElement($name);
    $textnode = $node->ownerDocument->createCDATASection($value);
    $result->appendChild($textnode);
    $node->appendChild($result);
    Return $result;
  }
  
  public static function copy($node){
    $result = $node->ownerDocument->createElement($node->nodeName);
    foreach($node->attributes as $value) $result->setAttribute($value->nodeName,$value->value);
    if(!$node->childNodes) return $result;
    
    foreach($node->childNodes as $child) {
      if($child->nodeName=="#text") {
        $result->appendChild($node->ownerDocument->createTextNode($child->nodeValue));
      } else {
        $result->appendChild(self::copy($child));
      }
    }
    
    return $result;
  }
  
}//class
function _struct_to_array(&$values, &$i)  {
  $result = array();
  if (isset($values[$i]['value'])) array_push($result, $values[$i]['value']);
  
  while (++$i < count($values)) {
    switch ($values[$i]['type']) {
      case 'cdata':
      array_push($result, $values[$i]['value']);
      break;
      
      case 'complete':
      $name = $values[$i]['tag'];
      if(!empty($name)){
        if (isset($values[$i]['value'])) {
          if (isset($values[$i]['attributes'])) {
            $val = array(
            0 => $values[$i]['value'],
            'attributes' => $values[$i]['attributes']
            );
          } else {
            $val = $values[$i]['value'];
          }
        } elseif (isset($values[$i]['attributes'])) {
          $val = $values[$i]['attributes'];
        } else {
          $val = '';
        }
        if (!isset($result[$name])) {
          $result[$name]= $val;
        } elseif(is_array($result[$name])) {
          $result[$name][] = $val;
        } else {
          $result[$name] = array($result[$name], $val);
        }
      }
      break;
      
      case 'open':
      $name = $values[$i]['tag'];
      $size = isset($result[$name]) ? sizeof($result[$name]) : 0;
      $result[$name][$size] = _struct_to_array($values, $i);
      break;
      
      case 'close':
      return $result;
      break;
    }
  }
  return $result;
}//_struct_to_array

function xml2array($xml)  {
  $values = array();
  $index  = array();
  $result  = array();
  $parser = xml_parser_create();
  xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
  xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
  xml_parse_into_struct($parser, $xml, $values, $index);
  xml_parser_free($parser);
  
  $i = 0;
  $name = $values[$i]['tag'];
  $result[$name] = isset($values[$i]['attributes']) ? $values[$i]['attributes'] : '';
  $result[$name] = _struct_to_array($values, $i);
  return $result;
}

class tdomrss extends domDocument {
  public $items;
  public $rss;
  public $channel;
  
  public function __construct() {
    parent::__construct();
    $this->items = array();
  }
  
  public function CreateRoot($url, $title) {
    $this->encoding = 'utf-8';
    $this->appendChild($this->createComment('generator="Lite Publisher/' . litepublisher::$options->version . ' version"'));
    $this->rss = $this->createElement('rss');
    $this->appendChild($this->rss);
    
    tnode::attr($this->rss, 'version', '2.0');
    tnode::attr($this->rss, 'xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
    tnode::attr($this->rss, 'xmlns:wfw', 'http://wellformedweb.org/CommentAPI/');
    tnode::attr($this->rss, 'xmlns:dc', 'http://purl.org/dc/elements/1.1/');
    tnode::attr($this->rss, 'xmlns:atom', 'http://www.w3.org/2005/Atom');
    
    $this->channel = tnode::add($this->rss, 'channel');
    
    $link = tnode::add($this->channel, 'atom:link');
    tnode::attr($link, 'href', $url);
    tnode::attr($link, 'rel', 'self');
    tnode::attr($link,'type', 'application/rss+xml');
    
    tnode::addvalue($this->channel , 'title', $title);
    tnode::addvalue($this->channel , 'link', $url);
    tnode::addvalue($this->channel , 'description', litepublisher::$site->description);
    tnode::addvalue($this->channel , 'pubDate', date('r'));
    tnode::addvalue($this->channel , 'generator', 'http://litepublisher.com/generator.htm?version=' . litepublisher::$options->version);
    tnode::addvalue($this->channel , 'language', 'en');
  }
  
  public function CreateRootMultimedia($url, $title) {
    $this->encoding = 'utf-8';
    $this->appendChild($this->createComment('generator="Lite Publisher/' . litepublisher::$options->version . ' version"'));
    $this->rss = $this->createElement('rss');
    $this->appendChild($this->rss);
    
    tnode::attr($this->rss, 'version', '2.0');
    tnode::attr($this->rss, 'xmlns:media', 'http://video.search.yahoo.com/mrss');
    tnode::attr($this->rss, 'xmlns:atom', 'http://www.w3.org/2005/Atom');
    
    $this->channel = tnode::add($this->rss, 'channel');
    
    $link = tnode::add($this->channel, 'atom:link');
    tnode::attr($link, 'href', $url);
    tnode::attr($link, 'rel', 'self');
    tnode::attr($link,'type', 'application/rss+xml');
    
    tnode::addvalue($this->channel , 'title', $title);
    tnode::addvalue($this->channel , 'link', $url);
    tnode::addvalue($this->channel , 'description', litepublisher::$site->description);
    tnode::addvalue($this->channel , 'pubDate', date('r'));
    tnode::addvalue($this->channel , 'generator', 'http://litepublisher.com/generator.htm?version=' . litepublisher::$options->version);
    tnode::addvalue($this->channel , 'language', 'en');
  }
  
  public function AddItem() {
    $result = tnode::add($this->channel, 'item');
    $this->items[] = $result;
    return $result;
  }
  
  public function GetStripedXML() {
    $s = $this->saveXML();
    return substr($s, strpos($s, '?>') + 2);
  }
  
}//class