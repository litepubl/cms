<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\tag;
use litepubl\core\Items;
use litepubl\core\TemplateInterface;

class Common extends Items implements TemplateInterface {
    public $factory;
    public $contents;
    public $itemsposts;
    public $PermalinkIndex;
    public $postpropname;
    public $id;
    private $newtitle;
    private $all_loaded;
    private $_idposts;

    protected function create() {
        $this->dbversion = dbversion;
        parent::create();
        $this->addevents('changed', 'onbeforecontent', 'oncontent');
        $this->data['includechilds'] = false;
        $this->data['includeparents'] = false;
        $this->PermalinkIndex = 'category';
        $this->postpropname = 'categories';
        $this->all_loaded = false;
        $this->_idposts = array();
        $this->createfactory();
    }

    protected function createfactory() {
        $this->factory = litepubl::$classes->getfactory($this);
        $this->contents = new ttagcontent($this);
        if (!$this->dbversion) $this->data['itemsposts'] = array();
        $this->itemsposts = new titemspostsowner($this);
    }

    public function loadall() {
        //prevent double request
        if ($this->all_loaded) return;
        $this->all_loaded = true;
        return parent::loadall();
    }

    public function select($where, $limit) {
        if ($where != '') $where.= ' and ';
        $db = litepubl::$db;
        $t = $this->thistable;
        $u = $db->urlmap;
        $res = $db->query("select $t.*, $u.url from $t, $u
    where $where $u.id = $t.idurl $limit");
        return $this->res2items($res);
    }

    public function load() {
        if (parent::load() && !$this->dbversion) {
            $this->itemsposts->items = & $this->data['itemsposts'];
        }
    }

    public function getsortedcontent(array $tml, $parent, $sortname, $count, $showcount) {
        $sorted = $this->getsorted($parent, $sortname, $count);
        if (count($sorted) == 0) return '';
        $result = '';
        $iconenabled = !litepubl::$options->icondisabled;
        $theme = ttheme::i();
        $args = new targs();
        $args->rel = $this->PermalinkIndex;
        $args->parent = $parent;
        foreach ($sorted as $id) {
            $item = $this->getitem($id);
            $args->add($item);
            $args->icon = $iconenabled ? $this->geticonlink($id) : '';
            $args->subcount = $showcount ? $theme->parsearg($tml['subcount'], $args) : '';
            $args->subitems = $tml['subitems'] ? $this->getsortedcontent($tml, $id, $sortname, $count, $showcount) : '';
            $result.= $theme->parsearg($tml['item'], $args);
        }
        if ($parent == 0) return $result;
        $args->parent = $parent;
        $args->item = $result;
        return $theme->parsearg($tml['subitems'], $args);
    }

    public function geticonlink($id) {
        $item = $this->getitem($id);
        if ($item['icon'] == 0) return '';
        $files = tfiles::i();
        if ($files->itemexists($item['icon'])) return $files->geticon($item['icon'], $item['title']);
        $this->setvalue($id, 'icon', 0);
        if (!$this->dbversion) $this->save();
        return '';
    }

    public function geticon() {
        $item = $this->getitem($this->id);
        return $item['icon'];
    }

    public function geturl($id) {
        $item = $this->getitem($id);
        return $item['url'];
    }

    public function postedited($idpost) {
        $post = $this->factory->getpost((int)$idpost);
        $items = $post->{$this->postpropname};
        array_clean($items);
        if (count($items)) $items = $this->db->idselect(sprintf('id in (%s)', implode(',', $items)));
        $changed = $this->itemsposts->setitems($idpost, $items);
        $this->updatecount($changed);
    }

    public function postdeleted($idpost) {
        $changed = $this->itemsposts->deletepost($idpost);
        $this->updatecount($changed);
    }

    protected function updatecount(array $items) {
        if (count($items) == 0) return;
        $db = litepubl::$db;
        //next queries update values
        $items = implode(',', $items);
        $thistable = $this->thistable;
        $itemstable = $this->itemsposts->thistable;
        $itemprop = $this->itemsposts->itemprop;
        $postprop = $this->itemsposts->postprop;
        $poststable = $db->posts;
        $list = $db->res2assoc($db->query("select $itemstable.$itemprop as id, count($itemstable.$itemprop)as itemscount from $itemstable, $poststable
    where $itemstable.$itemprop in ($items)  and $itemstable.$postprop = $poststable.id and $poststable.status = 'published'
    group by $itemstable.$itemprop"));

        $db->table = $this->table;
        foreach ($list as $item) {
            $db->setvalue($item['id'], 'itemscount', $item['itemscount']);
        }
    }

    public function geturltype() {
        return 'normal';
    }

    public function add($parent, $title) {
        $title = trim($title);
        if (empty($title)) return false;
        if ($id = $this->indexof('title', $title)) return $id;
        $parent = (int)$parent;
        if (($parent != 0) && !$this->itemexists($parent)) $parent = 0;

        $url = tlinkgenerator::i()->createurl($title, $this->PermalinkIndex, true);
        $views = tviews::i();
        $idview = isset($views->defaults[$this->PermalinkIndex]) ? $views->defaults[$this->PermalinkIndex] : 1;

        $item = array(
            'idurl' => 0,
            'customorder' => 0,
            'parent' => $parent,
            'title' => $title,
            'idview' => $idview,
            'idperm' => 0,
            'icon' => 0,
            'itemscount' => 0,
            'includechilds' => $this->includechilds,
            'includeparents' => $this->includeparents,
        );

        $id = $this->db->add($item);
        $this->items[$id] = $item;
        $idurl = litepubl::$urlmap->add($url, get_class($this) , $id, $this->urltype);
        $this->setvalue($id, 'idurl', $idurl);
        $this->items[$id]['url'] = $url;
        $this->added($id);
        $this->changed();
        litepubl::$urlmap->clearcache();
        return $id;
    }

    public function edit($id, $title, $url) {
        $item = $this->getitem($id);
        if (($item['title'] == $title) && ($item['url'] == $url)) return;
        $item['title'] = $title;
        if ($this->dbversion) {
            $this->db->updateassoc(array(
                'id' => $id,
                'title' => $title
            ));
        }

        $linkgen = tlinkgenerator::i();
        $url = trim($url);
        // try rebuild url
        if ($url == '') {
            $url = $linkgen->createurl($title, $this->PermalinkIndex, false);
        }

        if ($item['url'] != $url) {
            if (($urlitem = litepubl::$urlmap->find_item($url)) && ($urlitem['id'] != $item['idurl'])) {
                $url = $linkgen->MakeUnique($url);
            }
            litepubl::$urlmap->setidurl($item['idurl'], $url);
            litepubl::$urlmap->addredir($item['url'], $url);
            $item['url'] = $url;
        }

        $this->items[$id] = $item;
        $this->save();
        $this->changed();
        litepubl::$urlmap->clearcache();
    }

    public function delete($id) {
        $item = $this->getitem($id);
        litepubl::$urlmap->deleteitem($item['idurl']);
        $this->contents->delete($id);
        $list = $this->itemsposts->getposts($id);
        $this->itemsposts->deleteitem($id);
        parent::delete($id);
        if ($this->postpropname) $this->itemsposts->updateposts($list, $this->postpropname);
        $this->changed();
        litepubl::$urlmap->clearcache();
    }

    public function createnames($list) {
        if (is_string($list)) $list = explode(',', trim($list));
        $result = array();
        $this->lock();
        foreach ($list as $title) {
            $title = tcontentfilter::escape($title);
            if ($title == '') continue;
            $result[] = $this->add(0, $title);
        }
        $this->unlock();
        return $result;
    }

    public function getnames(array $list) {
        $this->loaditems($list);
        $result = array();
        foreach ($list as $id) {
            if (!isset($this->items[$id])) continue;
            $result[] = $this->items[$id]['title'];
        }
        return $result;
    }

    public function getlinks(array $list) {
        if (count($list) == 0) return array();
        $this->loaditems($list);
        $result = array();
        foreach ($list as $id) {
            if (!isset($this->items[$id])) continue;
            $item = $this->items[$id];
            $result[] = sprintf('<a href="%1$s" title="%2$s">%2$s</a>', litepubl::$site->url . $item['url'], $item['title']);
        }
        return $result;
    }

    public function getsorted($parent, $sortname, $count) {
        $count = (int)$count;
        if ($sortname == 'count') $sortname = 'itemscount';
        if (!in_array($sortname, array(
            'title',
            'itemscount',
            'customorder',
            'id'
        ))) $sortname = 'title';

        if ($this->dbversion) {
            $limit = $sortname == 'itemscount' ? "order by $this->thistable.$sortname desc" : "order by $this->thistable.$sortname asc";
            if ($count > 0) $limit.= " limit $count";
            return $this->select($parent == - 1 ? '' : "$this->thistable.parent = $parent", $limit);
        }

        $list = array();
        foreach ($this->items as $id => $item) {
            if (($parent != - 1) & ($parent != $item['parent'])) continue;
            $list[$id] = $item[$sortname];
        }
        if (($sortname == 'itemscount')) {
            arsort($list);
        } else {
            asort($list);
        }

        if (($count > 0) && ($count < count($list))) {
            $list = array_slice($list, 0, $count, true);
        }

        return array_keys($list);
    }

    //Itemplate
    public function request($id) {
        if ($this->id = (int)$id) {
            try {
                $item = $this->getitem((int)$id);
            }
            catch(Exception $e) {
                return 404;
            }

            $view = tview::getview($this);
            $perpage = $view->perpage ? $view->perpage : litepubl::$options->perpage;
            $pages = (int)ceil($item['itemscount'] / $perpage);
            if ((litepubl::$urlmap->page > 1) && (litepubl::$urlmap->page > $pages)) {
                return sprintf('<?php litepubl::$urlmap->redir(\'%s\'); ?>', $item['url']);
            }
        }
    }

    public function getname($id) {
        $item = $this->getitem($id);
        return $item['title'];
    }

    public function gettitle() {
        if ($this->id) {
            return $this->getvalue($this->id, 'title');
        }

        return tlocal::i()->categories;
    }

    public function gethead() {
        if ($this->id) {
            $result = $this->contents->getvalue($this->id, 'head');
            $theme = tview::getview($this)->theme;
            $result.= $theme->templates['head.tags'];

            $list = $this->getidposts($this->id);
            $result.= $this->factory->posts->getanhead($list);

            return $theme->parse($result);
        }
    }

    public function getkeywords() {
        if ($this->id) {
            $result = $this->contents->getvalue($this->id, 'keywords');
            if ($result == '') $result = $this->title;
            return $result;
        }
    }

    public function getdescription() {
        if ($this->id) {
            $result = $this->contents->getvalue($this->id, 'description');
            if ($result == '') $result = $this->title;
            return $result;
        }
    }

    public function getidview() {
        if ($this->id) {
            return $this->getvalue($this->id, 'idview');
        }

        return 1;
    }

    public function setidview($id) {
        if ($id != $this->idview) {
            $this->setvalue($this->id, 'idview', $id);
        }
    }

    public function getidperm() {
        if ($this->id) {
            $item = $this->getitem($this->id);
            return isset($item['idperm']) ? (int)$item['idperm'] : 0;
        }

        return 0;
    }

    public function getindex_tml() {
        $theme = ttheme::i();
        if (!empty($theme->templates['index.tag'])) return $theme->templates['index.tag'];
        return false;
    }

    public function getcontent() {
        if ($s = $this->contents->getcontent($this->id)) {
            $pages = explode('<!--nextpage-->', $s);
            $page = litepubl::$urlmap->page - 1;
            if (isset($pages[$page])) return $pages[$page];
        }

        return '';
    }

    public function getcont() {
        $result = '';
        $this->callevent('onbeforecontent', array(&$result
        ));

        if (!$this->id) {
            $result.= $this->getcont_all();
        } else {
            $view = tview::getview($this);

            if ($this->getcontent()) {
                ttheme::$vars['menu'] = $this;
                $result.= $view->theme->parse($view->theme->templates['content.menu']);
            }

            $list = $this->getidposts($this->id);
            $item = $this->getitem($this->id);
            $result.= $view->theme->getpostsnavi($list, $item['url'], $item['itemscount'], $view->postanounce, $view->perpage);
        }

        $this->callevent('oncontent', array(&$result
        ));
        return $result;
    }

    public function getcont_all() {
        return sprintf('<ul>%s</ul>', $this->getsortedcontent(array(
            'item' => '<li><a href="$link" title="$title">$icon$title</a>$subcount</li>',
            'subcount' => '<strong>($itemscount)</strong>',
            'subitems' => '<ul>$item</ul>'
        ) , 0, 'count', 0, 0, false));
    }

    public function get_sorted_posts($id, $count, $invert) {
        $ti = $this->itemsposts->thistable;
        $posts = $this->factory->posts;
        $p = $posts->thistable;
        $order = $invert ? 'asc' : 'desc';
        $result = $this->db->res2id($this->db->query("select $p.id as id, $ti.post as post from $p, $ti
    where    $ti.item = $id and $p.id = $ti.post and $p.status = 'published'
    order by $p.posted $order limit 0, $count"));

        $posts->loaditems($result);
        return $result;
    }

    public function getidposts($id) {
        if (isset($this->_idposts[$id])) {
            return $this->_idposts[$id];
        }

        $item = $this->getitem($id);
        $includeparents = (int)$item['includeparents'];
        $includechilds = (int)$item['includechilds'];

        $view = tview::i($item['idview']);
        $perpage = $view->perpage ? $view->perpage : litepubl::$options->perpage;
        $order = $view->invertorder ? 'asc' : 'desc';
        $from = (litepubl::$urlmap->page - 1) * $perpage;

        $posts = $this->factory->posts;
        $p = $posts->thistable;
        $t = $this->thistable;
        $ti = $this->itemsposts->thistable;
        $postprop = $this->itemsposts->postprop;
        $itemprop = $this->itemsposts->itemprop;

        if ($includeparents || $includechilds) {
            $this->loadall();
            $all = array(
                $id
            );

            if ($includeparents) {
                $all = array_merge($all, $this->getparents($id));
            }

            if ($includechilds) {
                $all = array_merge($all, $this->getchilds($id));
            }

            $tags = sprintf('in (%s)', implode(',', $all));
        } else {
            $tags = " = $id";
        }

        $result = $this->db->res2id($this->db->query("select $ti.$postprop as $postprop, $p.id as id from $ti, $p
    where    $ti.$itemprop $tags and $p.id = $ti.$postprop and $p.status = 'published'
    order by $p.posted $order limit $from, $perpage"));

        $result = array_unique($result);
        $posts->loaditems($result);
        $this->_idposts[$id] = $result;
        return $result;
    }

    public function getparents($id) {
        $result = array();
        while ($id = (int)$this->items[$id]['parent']) {
            //if (!isset($this->items[$id])) $this->error(sprintf('Parent category %d not exists', $id);
            $result[] = $id;
        }

        return $result;
    }

    public function getchilds($parent) {
        $result = array();
        foreach ($this->items as $id => $item) {
            if ($parent == $item['parent']) {
                $result[] = $id;
                $result = array_merge($result, $this->getchilds($id));
            }
        }
        return $result;
    }

    public function getsitemap($from, $count) {
        return $this->externalfunc(__class__, 'Getsitemap', array(
            $from,
            $count
        ));
    }

} //class