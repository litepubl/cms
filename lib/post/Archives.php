<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\post;

class Archives extends \litepubl\core\Items implements \litepubl\theme\ControlerInterface
{
use \litepubl\theme\ControlerTrait;

    public $date;
    private $_idposts;


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

        return $this->schema->theme->parse($result);
    }

    public function postschanged() {
        $posts = Posts::i();
        $this->lock();
        $this->items = array();
        //sort archive by months
        $linkgen = tlinkgenerator::i();
        $db = $this->db;
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
        $result.= Posts::i()->getanhead($this->getidposts());
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

}