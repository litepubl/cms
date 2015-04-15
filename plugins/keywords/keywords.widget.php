<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tkeywordswidget extends twidget {
  public $links;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function create() {
    parent::create();
    $this->basename = 'keywords' . DIRECTORY_SEPARATOR   . 'index';
    $this->cache = 'nocache';
    $this->adminclass = 'tadminkeywords';
    $this->data['count'] = 6;
    $this->data['notify'] = true;
    $this->data['trace'] = true;
    $this->addmap('links', array());
  }
  
  public function getdeftitle() {
    $about = tplugins::getabout(tplugins::getname(__file__));
    return $about['deftitle'];
  }
  
  public function getwidget($id, $sidebar) {
    $content = $this->getcontent($id, $sidebar);
    if ($content == '') return '';
    $title = $this->gettitle($id);
    $theme = ttheme::i();
    return $theme->getwidget($title, $content, $this->template, $sidebar);
  }
  
  public function getcontent($id, $sidebar) {
    if (litepublisher::$urlmap->is404 || litepublisher::$urlmap->adminpanel ||
    strbegin(litepublisher::$urlmap->url, '/croncron.php') || strend(litepublisher::$urlmap->url, '.xml'))  return '';
    
    $id = litepublisher::$urlmap->itemrequested['id'];
    $filename = litepublisher::$paths->data . 'keywords' . DIRECTORY_SEPARATOR.$id . '.' . litepublisher::$urlmap->page . '.php';
    if (@file_exists($filename)) {
      $links = file_get_contents($filename);
    } else {
      if (count($this->links) < $this->count) return '';
      $arlinks = array_splice($this->links, 0, $this->count);
      $this->save();
      
      //$links = "\n<li>" . implode("</li>\n<li>", $arlinks)  . "</li>";
      $links = '';
      $text = '';
      foreach ($arlinks as $link) {
        $links .= sprintf('<li><a href="%s">%s</a></li>', $link['url'], $link['text']);
        $text .= $link['text'] . "\n";
      }
      file_put_contents($filename, $links);
      if ($this->notify) {
        $plugin = tkeywordsplugin::i();
        $plugin->added($filename, $text);
      }
    }
    
    $theme = ttheme::i();
    return $theme->getwidgetcontent($links, $this->template, $sidebar);
  }
  
}//class