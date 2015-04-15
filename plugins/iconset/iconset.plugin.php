<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ticonsetplugin extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getdir() {
    return dirname(__file__) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
  }
  
  public function add($name, $filename) {
    $icons = ticons::i();
    $parser = tmediaparser::i();
    $icons->items[$name] = $parser->uploadicon($filename, file_get_contents($this->dir . $filename), true);
    $icons->save();
  }
  
  public function delete($name, $filename) {
    $files = tfiles::i();
    $icons = ticons::i();
    $id = $icons->items[$name];
    if ($files->itemexists($id)) {
      $item = $files->getitem($id);
      if (strend($item['filename'], $filename)) {
        $icons->items[$name] = 0;
        $files->delete($id);
      }
    }
  }
  
  public function install() {
    $files = tfiles::i();
    $files->lock();
    $icons = ticons::i();
    $icons->lock();
    $this->add('post', 'document-list.png');
    $this->add('categories', 'asterisk.png');
    $this->add('tags', 'tag-label.png');
    $this->add('archives', 'book.png');
    
    $this->add('audio', 'document-music.png');
    $this->add('video', 'film.png');
    $this->add('bin', 'document-binary.png');
    $this->add('document', 'document-text.png');
    $this->add('news', 'blog-blue.png');
    
    $icons->unlock();
    $files->unlock();
    
    /*
    $this->add('update', 'arrow-circle-double.png');
    $this->add('develop', 'user-black.png');
    $this->add('idea', 'light-bulb.png');
    $this->add('sql', 'database-network.png');
    $this->add('multiadmin', 'user-silhouette.png');
    */
  }
  
  public function uninstall() {
    $files = tfiles::i();
    $files->lock();
    $icons = ticons::i();
    $icons->lock();
    $this->delete('post', 'document-list.png');
    $this->delete('categories', 'asterisk.png');
    $this->delete('tags', 'tag-label.png');
    $this->delete('archives', 'book.png');
    
    $this->delete('audio', 'document-music.png');
    $this->delete('video', 'film.png');
    $this->delete('bin', 'document-binary.png');
    $this->delete('document', 'document-text.png');
    $this->delete('news', 'blog-blue.png');
    
    $icons->unlock();
    $files->unlock();
    litepublisher::$urlmap->clearcache();
  }
  
}//class