<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

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
        if ((litepubl::$urlmap->page == 1) && ($this->content != '')) {
            $result.= $theme->simple($theme->parse($this->rawcontent));
        }

        $perpage = litepubl::$options->perpage;
        $downloaditems = tdownloaditems::i();
        $d = litepubl::$db->prefix . $downloaditems->childtable;
        $p = litepubl::$db->posts;
        $where = $this->type == '' ? '' : " and $d.type = '$this->type'";
        $count = $downloaditems->getchildscount($where);
        $from = (litepubl::$urlmap->page - 1) * $perpage;
        if ($from <= $count) {
            $items = $downloaditems->select("$p.status = 'published' $where", " order by $p.posted desc limit $from, $perpage");
            ttheme::$vars['lang'] = tlocal::i('downloaditem');
            $tml = $theme->templates['custom']['downloadexcerpt'];
            if (count($items) > 0) {
                $result.= $theme->templates['custom']['siteform'];
                foreach ($items as $id) {
                    ttheme::$vars['post'] = tdownloaditem::i($id);
                    $result.= $theme->parse($tml);
                }
            }
        }
        $result.= $theme->getpages($this->url, litepubl::$urlmap->page, ceil($count / $perpage));
        return $result;
    }

} //class