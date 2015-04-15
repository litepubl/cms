<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcodedocmenu extends tmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    $result = parent::gethead();
    $result .=   '<style type="text/css">
  .doc_classes p { display:none;}
    </style>
    <script type="text/javascript">
    $(document).ready(function() {
      $("a[href=\'#\']", ".doc_classes").click(function() {
        $(this).parent().children("p").slideToggle();
        return false;
      });
    });
    </script>';
    return $result;
  }
  
  public function getcontent() {
    $result = parent::getcontent();
    $db = litepublisher::$db;
  $items = $db->res2items($db->query("select id, class from {$db->prefix}codedoc order by class"));
    if (count($items) == 0) return $result;
    tposts::i()->loaditems(array_keys($items));
    $theme = tview::getview($this)->theme;
    $args = new targs();
    $result .= '<ul class="doc_classes">';
    $tml = '<li id="doc-class-$id"><a href="#">$class</a> $post.excerptcontent</li>';
    foreach ($items as $id => $item) {
      $args->add($item);
      ttheme::$vars['post'] = tpost::i($id);
      $result .= $theme->parsearg($tml, $args);
    }
    
    $result .= '</ul>';
    return $result;
  }
  
}//class