<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl;

use litepubl\view\Args;
use litepubl\view\Theme;

class tcodedocmenu extends tmenu
{

    public static function i($id = 0)
    {
        return parent::iteminstance(__class__, $id);
    }

    public function getHead()
    {
        $result = parent::gethead();
        $result.= '<style type="text/css">
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

    public function getContent()
    {
        $result = parent::getcontent();
        $db = $this->getApp()->db;
        $items = $db->res2items($db->query("select id, class from {$db->prefix}codedoc order by class"));
        if (count($items) == 0) {
            return $result;
        }

        tposts::i()->loaditems(array_keys($items));
        $theme = Schema::getview($this)->theme;
        $args = new Args();
        $result.= '<ul class="doc_classes">';
        $tml = '<li id="doc-class-$id"><a href="#">$class</a> $post.excerptcontent</li>';
        foreach ($items as $id => $item) {
            $args->add($item);
            Theme::$vars['post'] = tpost::i($id);
            $result.= $theme->parseArg($tml, $args);
        }

        $result.= '</ul>';
        return $result;
    }
}