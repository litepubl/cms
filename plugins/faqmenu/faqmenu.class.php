<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\core\Str;

class tfaqmenu extends tmenu {

    public static function i($id = 0) {
        return static ::iteminstance(__class__, $id);
    }

    public function getHead() {
        $result = parent::gethead();
        $result.= '<style type="text/css">
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

    public function setContent($s) {
        $this->rawcontent = $s;
        $this->data['content'] = $this->convert($s);
    }

    public function convert($content) {
        $result = '';
        $content = str_replace(array(
            "\r\n",
            "\r"
        ) , "\n", trim($content));
        $lines = explode("\n", $content);
        $q = array();
        $a = array();
        $filter = tcontentfilter::i();
        foreach ($lines as $s) {
            $s = trim($s);
            if (Str::begin($s, 'q:') || Str::begin($s, 'Q:')) {
                $q[] = trim(substr($s, 2));
            } elseif (Str::begin($s, 'a:') || Str::begin($s, 'A:')) {
                $a[] = trim(substr($s, 2));
            } elseif ($s != '') {
                $result.= $this->createlist($q, $a);
                $q = array();
                $a = array();
                $result.= $filter->simplefilter($s);
            }
        }

        $result.= $this->createlist($q, $a);
        return $result;
    }

    private function createlist(array $questions, array $answers) {
        if (count($questions) == 0) {
 return '';
}


        $result = '';
        foreach ($questions as $i => $q) {
            $result.= sprintf('<li><a href="#" rel="faqitem">%s</a><p>%s</p></li>', $q, $answers[$i]);
        }
        return sprintf('<ul class="faqlist">%s</ul>', $result);
    }

}