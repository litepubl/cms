<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsinglecat extends  tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['invertorder'] = false;
    $this->data['maxcount'] = 5;
    $this->data['tml'] = '<li><a href="$site.url$url" title="$title">$title</a></li>';
    $this->data['tmlitems'] = '<ul>$items</ul>';
  }
  
  public function themeparsed(ttheme $theme) {
    $tag = '$singlecat.content';
    if (!strpos($theme->templates['content.post'], $tag)) {
      $theme->templates['content.post'] = str_replace('$post.content', '$post.content ' . $tag, $theme->templates['content.post']);
    }
  }
  
  public function getcontent() {
    $post = litepublisher::$urlmap->context;
    if (!($post instanceof tpost)) return '';
    if (count($post->categories) == 0) return '';
    $idcat = $post->categories[0];
    if ($idcat == 0) return '';
    $table = litepublisher::$db->prefix . 'categoriesitems';
    $order = $this->invertorder ? 'asc' : 'desc';
    $posts = tposts::i();
    $result = $posts->getlinks("$posts->thistable.id in
    (select  $table.post from $table where $table.item = $idcat)
    and $posts->thistable.id != $post->id
    order by $posts->thistable.posted  $order limit $this->maxcount",
    $this->tml);
    
    return str_replace('$items', $result, $this->tmlitems);
  }
  
}//class