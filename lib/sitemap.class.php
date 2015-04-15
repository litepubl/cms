<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsitemap extends titems_itemplate implements itemplate {
  public $classes;
  private $lastmod;
  private $count;
  private $fd;
  private $prio;
  
  public static function i() {
    return Getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'sitemap';
    $this->addevents('onindex');
    $this->data['date'] = time();
    $this->data['countfiles'] = 1;
    $this->addmap('classes', array('tmenus', 'tposts', 'tcategories', 'ttags', 'tarchives' ));
  }
  
  public function add($url, $prio) {
    $this->items[$url] = (int) $prio;
    $this->save();
  }
  
  public function cron() {
    $this->createfiles();
  }
  
  //itemplate
  public function gettitle() {
    return     tlocal::get('default', 'sitemap');
  }
  
  public function getcont() {
    $result = '<h4>' . tlocal::get('default', 'sitemap') . '</h4><ul>';
    $theme = $this->view->theme;
    $perpage = 1000;
    $count = 0;
    $from = (litepublisher::$urlmap->page - 1) * $perpage;
    $siteurl = litepublisher::$site->url;
    $classes = litepublisher::$urlmap->page == 1 ? $this->classes : 'tposts';
    foreach ($classes as $class) {
      $instance = getinstance($class);
      $links = $instance->getsitemap($from, $perpage - $count);
      $count += count($links);
      foreach ($links as $item) {
        $pages = '';
        if ($item['pages'] > 1) {
          $url = rtrim($item['url'], '/');
          for ($i = 2; $i < $link['pages']; $i++) {
            $pages = "<a href=\"$siteurl$url/page/$i/\">$i</a>,";
          }
        }
        
  $result .= "<li><a href=\"$siteurl{$item['url']}\" title=\"{$item['title']}\">{$item['title']}</a>$pages</li>";
      }
      
      if ($count > $perpage) break;
    }
    $result .= '</ul>';
    //    $result .=$theme->getpages('/sitemap.htm', litepublisher::$urlmap->page, ceil($posts->archivescount / $perpage));
    return $result;
  }
  
  public function request($arg) {
    if ($arg == 'xml') {
      return '<?php turlmap::sendxml(); ?>' .
      $this->GetIndex();
    }
  }
  
  public function getIndex() {
    $lastmod = date('Y-m-d', $this->date);
    $result = '<sitemapindex xmlns="http://www.google.com/schemas/sitemap/0.84">';
    $url = litepublisher::$site->files . '/files/' . litepublisher::$domain;
    $exists = true;
    for ($i =1; $i <= $this->countfiles; $i++) {
      $result .= "<sitemap><loc>$url.$i.xml.gz</loc>      <lastmod>$lastmod</lastmod></sitemap>";
      if ($exists) $exists = file_exists(litepublisher::$paths->files . "$i.xml.gz");
    }
    $this->callevent('onindex', array(&$result));
    $result .= '</sitemapindex>';
    if (!$exists)     $this->createfiles();
    return $result;
  }
  
  public function createfiles() {
    $this->countfiles = 0;
    $this->count = 0;
    $this->date = time();
    $this->lastmod = date('Y-m-d', $this->date);
    $this->openfile();
    
    $home = thomepage::i();
    $this->prio = 9;
    $this->write('/', $home->showposts && $home->showpagenator ? ceil($home->archcount / litepublisher::$options->perpage) : 1);
    
    $perpage = 1000;
    foreach ($this->classes as $prio => $class) {
      $this->prio = max(9 - $prio, 1);
      $instance = getinstance($class);
      $from = 0;
      do {
        $links = $instance->getsitemap($from, $perpage );
        $from += count($links);
        foreach ($links as $item) {
          $this->write($item['url'], $item['pages']);
        }
      } while (count($links) == $perpage);
    }
    
    //url's from items prop
    foreach ($this->items as $url => $prio) {
      $this->writeitem($url, $prio);
    }
    
    $this->closefile();
    $this->Save();
  }
  
  private function write($url, $pages) {
    $this->writeitem($url, $this->prio);
    $url = rtrim($url, '/');
    for ($i = 2; $i < $pages; $i++) {
      $this->writeitem("$url/page/$i/", $this->prio);
    }
  }
  
  private function writeitem($url, $prio) {
    $url = litepublisher::$site->url . $url;
    gzwrite($this->fd, "<url><loc>$url</loc><lastmod>$this->lastmod</lastmod>".
    "<changefreq>daily</changefreq><priority>0.$prio</priority></url>");
    
    if (++$this->count  >= 30000) {
      $this->closefile();
      $this->openfile();
    }
  }
  
  private function openfile() {
    $this->count = 0;
    $this->countfiles++;
    if ($this->fd = gzopen(litepublisher::$paths->files . litepublisher::$domain . ".$this->countfiles.xml.gz", 'w')) {
      $this->WriteHeader();
    } else {
      tfiler::log("error write file to folder " . litepublisher::$paths->files);
      exit();
    }
  }
  
  private function closefile() {
    $this->WriteFooter();
    gzclose($this->fd);
    @chmod(litepublisher::$paths->files . litepublisher::$domain . ".$this->countfiles.xml.gz", 0666);
    $this->fd = false;
  }
  
  private function WriteHeader() {
    gzwrite($this->fd, '<?xml version="1.0" encoding="UTF-8"?>' .
    '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84"'.
    ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' .
    ' xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">');
  }
  
  private function WriteFooter() {
    gzwrite($this->fd, '</urlset>');
  }
  
}//class