<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class trssMultimedia extends tevents {
  public $domrss;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'rssmultimedia';
    $this->addevents('onroot', 'onitem');
    $this->data['feedburner'] = '';
  }
  
  public function fileschanged() {
    litepublisher::$urlmap->expiredclass(get_class($this));
  }
  
  public function request($arg) {
    $result = '';
    if (($arg == null) && ($this->feedburner  != '')) {
      $result .= "<?php
      if (!preg_match('/feedburner|feedvalidator/i', \$_SERVER['HTTP_USER_AGENT'])) {
        return litepublisher::\$urlmap->redir('$this->feedburner', 307);
      }
      ?>";
    }
    
    $result .= '<?php turlmap::sendxml(); ?>';
    
    $this->domrss = new tdomrss;
    $this->domrss->CreateRootMultimedia(litepublisher::$site->url. litepublisher::$urlmap->url, 'media');
    $this->onroot($this->domrss);
    
    $list = $this->getrecent($arg, litepublisher::$options->perpage);
    foreach ($list as $id) {
      $this->addfile($id);
    }
    
    $result .= $this->domrss->GetStripedXML();
    return $result;
  }
  
  private function getrecent($type, $count) {
    $files = tfiles::i();
    $sql = $type == '' ? '' : "media = '$type' and ";
    return $files->select($sql . 'parent = 0 and idperm = 0', " order by posted desc limit $count");
  }
  
  public function addfile($id) {
    $files = tfiles::i();
    $file = $files->getitem($id);
    $posts = $files->itemsposts->getposts($id);
    
    if (count($posts) == 0) {
      $postlink = litepublisher::$site->url . '/';
    } else {
      $post = tpost::i($posts[0]);
      $postlink = $post->link;
    }
    
    $item = $this->domrss->AddItem();
    tnode::addvalue($item, 'title', $file['title']);
    tnode::addvalue($item, 'link', $postlink);
    tnode::addvalue($item, 'pubDate', $file['posted']);
    
    $media = tnode::add($item, 'media:content');
    tnode::attr($media, 'url', $files->geturl($id));
    tnode::attr($media, 'fileSize', $file['size']);
    tnode::attr($media, 'type', $file['mime']);
    tnode::attr($media, 'medium', $file['media']);
    tnode::attr($media, 'expression', 'full');
    
    if ($file['width'] > 0 && $file['height'] > 0) {
      tnode::attr($media, 'height', $file['height']);
      tnode::attr($media, 'width', $file['width']);
    }
    
    /*
    if (!empty($file['bitrate'])) tnode::attr($media, 'bitrate', $file['bitrate']);
    if (!empty($file['framerate'])) tnode::attr($media, 'framerate', $file['framerate']);
    if (!empty($file['samplingrate'])) tnode::attr($media, 'samplingrate', $file['samplingrate']);
    if (!empty($file['channels'])) tnode::attr($media, 'channels', $file['channels']);
    if (!empty($file['duration'])) tnode::attr($media, 'duration', $file['duration']);
    */
    
    $hash = tnode::addvalue($item, 'media:hash', self::hashtomd5($file['hash']));
    tnode::attr($hash, 'algo', "md5");
    
    if (!empty($file['keywords'])) {
      tnode::addvalue($item, 'media:keywords', $file['keywords']);
    }
    
    if (!empty($file['description'])) {
      $description = tnode::addvalue($item, 'description', $file['description']);
      tnode::attr($description, 'type', 'html');
    }
    
    if ($file['preview'] > 0) {
      $idpreview = $file['preview'];
      $preview = $files->getitem($idpreview);
      $thumbnail  = tnode::add($item, 'media:thumbnail');
      tnode::attr($thumbnail, 'url', $files->geturl($idpreview));
      if ($preview['width'] > 0 && $preview['height'] > 0) {
        tnode::attr($thumbnail, 'height', $preview['height']);
        tnode::attr($thumbnail, 'width', $preview['width']);
      }
    }
    $this->onitem($item, $file);
  }
  
  public static function hashtomd5($hash) {
    $r ='';
    $a = base64_decode($hash);
    for($i=0; $i<16; $i++){
      $r .= dechex(ord($a[$i]));
    }
    return $r;
  }
  
  public function setfeedburner($url) {
    if (($this->feedburner != $url)) {
      $this->data['feedburner'] = $url;
      $this->save();
      litepublisher::$urlmap->clearcache();
    }
  }
  
}//class