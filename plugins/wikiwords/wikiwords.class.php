<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class twikiwords extends titems {
  public $itemsposts;
  private $fix;
  private $words;
  private $links;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->fix = array();
    $this->addevents('edited');
    $this->table = 'wikiwords';
    if (!$this->dbversion)  $this->data['itemsposts'] = array();
    $this->itemsposts = new titemspostsowner ($this);
    $this->words = array();
    $this->links = array();
  }
  
  public function __get($name) {
    if (strbegin($name, 'word_')) {
      $id = (int) substr($name, strlen('word_'));
      if (($id > 0) && $this->itemexists($id)) {
        return $this->getlink($id);
      }
      return '';
    }
    
    return parent::__get($name);
  }
  
  public function getpost($word) {
    if ($id =$this->add($word, 0)) {
      $items = $this->itemsposts->getposts($id);
      if (count($items) > 0) return $items[0];
    }
    return false;
  }
  
  public function getlink($id) {
    $item = $this->getitem($id);
    $word = $item['word'];
    if (isset($this->links[$word])) return $this->links[$word];
    $items = $this->itemsposts->getposts($id);
    $c = count($items);
    if ($c == 0) {
      $result = sprintf('<span class="wikiword">%s</span>', $word);
    } elseif ($c == 1) {
      $post = tpost::i($items[0]);
      $result = sprintf('<a href="%1$s#wikiword-%3$d" title="%2$s">%2$s</a>', $post->link, $word, $id);
    } else {
      $links = array();
      $posts = tposts::i();
      $posts->loaditems($items);
      foreach ($items as $idpost) {
        $post = tpost::i($idpost);
        $links[] = sprintf('<a href="%1$s#wikiword-%3$d" title="%2$s">%2$s</a>', $post->link, $post->title, $id);
      }
      $result = sprintf('<span class="wikiword">%s</span> (%s)', $word, implode(', ', $links));
    }
    
    $this->links[$word] = $result;
    return $result;
  }
  
  public function add($word, $idpost) {
    $word = trim(strip_tags($word));
    if ($word == '') return false;
    if (isset($this->words[$word])) {
      $id = $this->words[$word];
    } else {
      $id = $this->indexof('word', $word);
      if (!$id) $id = $this->additem(array('word' => $word));
      $this->words[$word] = $id;
    }
    
    if (($idpost > 0) && !$this->itemsposts->exists($idpost, $id)) {
      $this->itemsposts->add($idpost, $id);
      if (isset($this->links[$word])) unset($this->links[$word]);
      tposts::i()->addrevision();
    }
    
    return $id;
  }
  
  public function edit($id, $word) {
    return $this->setvalue($id, 'word', $word);
  }
  
  public function delete($id) {
    if (!$this->itemexists($id)) return false;
    $this->itemsposts->deleteitem($id);
    return parent::delete($id);
  }
  
  public function deleteword($word) {
    if ($id = $this->indexof('word', $word)) return $this->delete($id);
  }
  
  public function getword($word) {
    if ($id =$this->add($word, 0)) {
      return '$wikiwords.word_' . $id;
    }
    return '';
  }
  
  public function getwordlink($word) {
    $word = trim($word);
    if (isset($this->links[$word])) return $this->links[$word];
    if ($id =$this->add($word, 0)) {
      return $this->getlink($id);
    }
    return $word;
  }
  
  public function fixpost(tpost $post) {
    if (count($this->fix) == 0) return;
    foreach ($this->fix as $id => $wikipost) {
      if ($post == $wikipost) {
        $this->itemsposts->add($post->id, $id);
        unset($this->fix[$id]);
      }
    }
    
    tposts::i()->addrevision();
  }
  
  public function postdeleted($idpost) {
    if (count($this->itemsposts->deletepost($idpost)) > 0) {
      tposts::i()->addrevision();
    }
  }
  
  public function beforefilter($post, &$content, &$cancel) {
    $this->createwords($post, $content);
    $this->replacewords($content);
  }
  
  public function createwords($post, &$content) {
    $result = array();
    if (preg_match_all('/\[wiki\:(.*?)\]/im', $content, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
        $word = $item[1];
        if ($id = $this->add($word, $post->id)) {
          $result[] = $id;
          if ($post->id == 0) {
            $this->fix[$id] = $post;
            $post->onid = array($this, 'fixpost');
          }
          $content = str_replace($item[0], "<span class=\"wiki\" id=\"wikiword-$id\">$word</span>", $content);
        }
      }
    }
    return $result;
  }
  
  public function replacewords(&$content) {
    $result = array();
    if (preg_match_all('/\[\[(.*?)\]\]/i', $content, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
        $word = $item[1];
        if ($id =$this->add($word, 0)) {
          $result[] = $id;
          //$content = str_replace($item[0], "\$wikiwords.word_$id", $content);
          $content = str_replace($item[0], $this->getlink($id), $content);
        }
      }
    }
    return $result;
  }
  
}//class