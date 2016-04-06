<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tarchives extends titems_itemplate implements itemplate {
    public $date;
    private $_idposts;

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->basename = 'archives';
        $this->table = 'posts';
    }

    public function getheadlinks() {
        $result = '';
        foreach ($this->items as $date => $item) {
            $result.= "<link rel=\"archives\" title=\"{$item['title']}\" href=\"litepubl::$site->url{$item['url']}\" />\n";
        }
        return ttheme::i()->parse($result);
    }

    public function postschanged() {
        $posts = tposts::i();
        $this->lock();
        $this->items = array();
        //sort archive by months
        $linkgen = tlinkgenerator::i();
        $db = litepubl::$db;
        $res = $db->query("SELECT YEAR(posted) AS 'year', MONTH(posted) AS 'month', count(id) as 'count' FROM  $db->posts
    where status = 'published' GROUP BY YEAR(posted), MONTH(posted) ORDER BY posted DESC ");

        while ($r = $db->fetchassoc($res)) {
            $this->date = mktime(0, 0, 0, $r['month'], 1, $r['year']);
            $this->items[$this->date] = array(
                'idurl' => 0,
                'url' => $linkgen->Createlink($this, 'archive', false) ,
                'title' => tlocal::date($this->date, 'F Y') ,
                'year' => $r['year'],
                'month' => $r['month'],
                'count' => $r['count']
            );
        }

        $this->CreatePageLinks();
        $this->unlock();
    }

    public function CreatePageLinks() {
        $this->lock();
        //Compare links
        $old = litepubl::$urlmap->GetClassUrls(get_class($this));
        foreach ($this->items as $date => $item) {
            $j = array_search($item['url'], $old);
            if (is_int($j)) {
                array_splice($old, $j, 1);
            } else {
                $this->items[$date]['idurl'] = litepubl::$urlmap->Add($item['url'], get_class($this) , $date);
            }
        }
        foreach ($old as $url) {
            litepubl::$urlmap->delete($url);
        }

        $this->unlock();
    }

    //ITemplate
    public function request($date) {
        $date = (int)$date;
        if (!isset($this->items[$date])) return 404;

        $this->date = $date;
        $item = $this->items[$date];

        $view = tview::getview($this);
        $perpage = $view->perpage ? $view->perpage : litepubl::$options->perpage;
        $pages = (int)ceil($item['count'] / $perpage);
        if ((litepubl::$urlmap->page > 1) && (litepubl::$urlmap->page > $pages)) {
            return "<?php litepubl::\$urlmap->redir('{$item['url']}'); ?>";
        }
    }

    public function gethead() {
        $result = parent::gethead();
        $result.= tposts::i()->getanhead($this->getidposts());
        return $result;
    }

    public function gettitle() {
        return $this->items[$this->date]['title'];
    }

    public function getcont() {
        $items = $this->getidposts();
        if (count($items) == 0) return '';

        $view = tview::getview($this);
        $perpage = $view->perpage ? $view->perpage : litepubl::$options->perpage;
        $list = array_slice($items, (litepubl::$urlmap->page - 1) * $perpage, $perpage);
        $result = $view->theme->getposts($list, $view->postanounce);
        $result.= $view->theme->getpages($this->items[$this->date]['url'], litepubl::$urlmap->page, ceil(count($items) / $perpage));
        return $result;
    }

    public function getidposts() {
        if (isset($this->_idposts)) return $this->_idposts;
        $item = $this->items[$this->date];
        $order = tview::getview($this)->invertorder ? 'asc' : 'desc';
        return $this->_idposts = $this->getdb('posts')->idselect("status = 'published' and
year(posted) = '{$item['year']}' and month(posted) = '{$item['month']}'
    ORDER BY posted $order");
    }

    public function getsitemap($from, $count) {
        return $this->externalfunc(__class__, 'Getsitemap', array(
            $from,
            $count
        ));
    }

} //class
class tarchiveswidget extends twidget {

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->basename = 'widget.archives';
        $this->template = 'archives';
        $this->adminclass = 'tadminshowcount';
        $this->data['showcount'] = false;
    }

    public function getdeftitle() {
        return tlocal::get('default', 'archives');
    }

    protected function setshowcount($value) {
        if ($value != $this->showcount) {
            $this->data['showcount'] = $value;
            $this->Save();
        }
    }

    public function getcontent($id, $sidebar) {
        $arch = tarchives::i();
        if (count($arch->items) == 0) return '';
        $result = '';
        $theme = ttheme::i();
        $tml = $theme->getwidgetitem('archives', $sidebar);
        if ($this->showcount) $counttml = $theme->getwidgettml($sidebar, 'archives', 'subcount');
        $args = targs::i();
        $args->icon = '';
        $args->subcount = '';
        $args->subitems = '';
        $args->rel = 'archives';
        foreach ($arch->items as $date => $item) {
            $args->add($item);
            $args->text = $item['title'];
            if ($this->showcount) $args->subcount = str_replace($counttml, '$itemscount', $item['count']);
            $result.= $theme->parsearg($tml, $args);
        }

        return $theme->getwidgetcontent($result, 'archives', $sidebar);
    }

} //class