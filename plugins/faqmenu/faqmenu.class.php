<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tfaqmenu extends tmenu {
  
  public static function i($id = 0) {
    return self::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    $result = parent::gethead();
    $result .=   '<style type="text/css">
  .faqlist  p { display:none;}
    </style>
    <script type="text/javascript">
    $(document).ready(function() {
      $("a[rel=\'faqitem\']").click(function() {
        $(this).parent().children("p").slideToggle();
        return false;
      });
    });
    </script>';
    return $result;
  }
  
  public function setcontent($s) {
    $this->rawcontent = $s;
    $this->data['content'] = $this->convert($s);
  }
  
  public function convert($content) {
    $result = '';
    $content = str_replace(array("\r\n", "\r"), "\n", trim($content));
    $lines = explode("\n", $content);
    $q = array();
    $a = array();
    $filter = tcontentfilter::i();
    foreach ($lines as $s) {
      $s = trim($s);
      if (strbegin($s, 'q:') || strbegin($s, 'Q:')) {
        $q[] = trim(substr($s, 2));
      } elseif (strbegin($s, 'a:') || strbegin($s, 'A:')) {
        $a[] = trim(substr($s, 2));
      } elseif($s != '') {
        $result .= $this->createlist($q, $a);
        $q = array();
        $a = array();
        $result .= $filter->simplefilter($s);
      }
    }
    
    $result .= $this->createlist($q, $a);
    return $result;
  }
  
  private function createlist(array $questions, array $answers) {
    if (count($questions) == 0) return '';
    $result = '';
    foreach ($questions as $i => $q) {
      $result .= sprintf('<li><a href="#" rel="faqitem">%s</a><p>%s</p></li>', $q, $answers[$i]);
    }
    return sprintf('<ul class="faqlist">%s</ul>', $result);
  }
  
  
}//class