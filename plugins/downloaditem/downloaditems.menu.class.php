<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdownloaditemsmenu extends tmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  protected function create() {
    parent::create();
    $this->data['type'] = '';
  }
  
  public function getcont() {
    $result = '';
    $theme = ttheme::i();
    if ((litepublisher::$urlmap->page == 1) && ($this->content != '')) {
      $result .= $theme->simple($theme->parse($this->rawcontent));
    }
    
    $perpage = litepublisher::$options->perpage;
    $downloaditems = tdownloaditems::i();
    $d = litepublisher::$db->prefix . $downloaditems->childtable;
    $p = litepublisher::$db->posts;
    $where = $this->type == '' ? '' : " and $d.type = '$this->type'";
    $count = $downloaditems->getchildscount($where);
    $from = (litepublisher::$urlmap->page - 1) * $perpage;
    if ($from <= $count)  {
      $items = $downloaditems->select("$p.status = 'published' $where", " order by $p.posted desc limit $from, $perpage");
      ttheme::$vars['lang'] = tlocal::i('downloaditem');
      $tml = $theme->templates['custom']['downloadexcerpt'];
      if (count($items) > 0) {
        $result .= $theme->templates['custom']['siteform'];
        foreach($items as $id) {
          ttheme::$vars['post'] = tdownloaditem::i($id);
          $result .= $theme->parse($tml);
        }
      }
    }
    $result .=$theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count / $perpage));
    return $result;
  }
  
}//class