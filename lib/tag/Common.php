<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\tag;

use litepubl\core\Arr;
use litepubl\core\ItemsPosts;
use litepubl\utils\LinkGenerator;
use litepubl\view\Filter;
use litepubl\view\Schema;
use litepubl\view\Schemes;

class Common extends \litepubl\core\Items
{
    public $factory;
    public $contents;
    public $itemsposts;
    public $PermalinkIndex;
    public $postpropname;
    private $newtitle;
    private $all_loaded;

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->addevents('changed', 'onbeforecontent', 'oncontent');
        $this->data['includechilds'] = false;
        $this->data['includeparents'] = false;
        $this->PermalinkIndex = 'category';
        $this->postpropname = 'categories';
        $this->all_loaded = false;
        $this->createFactory();
    }

    protected function createFactory()
    {
        $this->factory = new Factory();
        $this->contents = new Content($this);
        $this->itemsposts = new ItemsPosts();
    }

    public function getView()
    {
        $view = View::i();
        $view->setTags($this);
        return $view;
    }

    public function loadAll()
    {
        //prevent double request
        if ($this->all_loaded) {
            return;
        }

        $this->all_loaded = true;
        return parent::loadAll();
    }

    public function select(string $where, string $limit): array
    {
        if ($where) {
            $where.= ' and ';
        }

        $db = $this->db;
        $t = $this->thistable;
        $u = $db->urlmap;
        $res = $db->query("select $t.*, $u.url from $t, $u
    where $where $u.id = $t.idurl $limit");

        return $this->res2items($res);
    }

    public function getUrl($id)
    {
        $item = $this->getItem($id);
        return $item['url'];
    }

    public function getName($id)
    {
        $item = $this->getItem($id);
        return $item['title'];
    }

    public function postEdited($idpost)
    {
        $post = $this->factory->getPost((int)$idpost);
        $items = $post->{$this->postpropname};
        Arr::clean($items);
        if (count($items)) {
            $items = $this->db->idSelect(sprintf('id in (%s)', implode(',', $items)));
        }

        $changed = $this->itemsposts->setItems($idpost, $items);
        $this->updateCount($changed);
    }

    public function postDeleted($idpost)
    {
        $changed = $this->itemsposts->deletePost($idpost);
        $this->updateCount($changed);
    }

    protected function updateCount(array $items)
    {
        if (!count($items)) {
            return;
        }

        $db = $this->db;
        //next queries update values
        $items = implode(',', $items);
        $thistable = $this->thistable;
        $itemstable = $this->itemsposts->thistable;
        $itemprop = $this->itemsposts->itemprop;
        $postprop = $this->itemsposts->postprop;
        $poststable = $db->posts;
        $list = $db->res2assoc($db->query("select $itemstable.$itemprop as id, count($itemstable.$itemprop)as itemscount from $itemstable, $poststable
    where $itemstable.$itemprop in ($items)  and $itemstable.$postprop = $poststable.id and $poststable.status = 'published' group by $itemstable.$itemprop"));

        $db->table = $this->table;
        foreach ($list as $item) {
            $db->setValue($item['id'], 'itemscount', $item['itemscount']);
        }
    }

    public function getUrlType()
    {
        return 'normal';
    }

    public function add($parent, $title)
    {
        $title = trim($title);
        if (empty($title)) {
            return false;
        }

        if ($id = $this->indexOf('title', $title)) {
            return $id;
        }

        $parent = (int)$parent;
        if ($parent && !$this->itemExists($parent)) {
            $parent = 0;
        }

        $url = LinkGenerator::i()->createurl($title, $this->PermalinkIndex, true);
        $schemes = Schemes::i();
        $idschema = isset($schemes->defaults[$this->PermalinkIndex]) ? $schemes->defaults[$this->PermalinkIndex] : 1;

        $item = array(
            'idurl' => 0,
            'customorder' => 0,
            'parent' => $parent,
            'title' => $title,
            'idschema' => $idschema,
            'idperm' => 0,
            'icon' => 0,
            'itemscount' => 0,
            'includechilds' => $this->includechilds,
            'includeparents' => $this->includeparents,
        );

        $id = $this->db->add($item);
        $this->items[$id] = $item;
        $idurl = $this->getApp()->router->add($url, get_class($this) , $id, $this->urltype);
        $this->setValue($id, 'idurl', $idurl);
        $this->items[$id]['url'] = $url;
        $this->added($id);
        $this->changed();
        $this->getApp()->cache->clear();
        return $id;
    }

    public function edit($id, $title, $url)
    {
        $item = $this->getItem($id);
        if (($item['title'] == $title) && ($item['url'] == $url)) {
            return;
        }

        $item['title'] = $title;
        $this->db->updateAssoc(array(
            'id' => $id,
            'title' => $title
        ));

        $app = $this->getApp();
        $linkgen = LinkGenerator::i();
        $url = trim($url);
        // try rebuild url
        if ($url == '') {
            $url = $linkgen->createurl($title, $this->PermalinkIndex, false);
        }

        if ($item['url'] != $url) {
            if (($urlitem = $app->router->queryItem($url)) && ($urlitem['id'] != $item['idurl'])) {
                $url = $linkgen->MakeUnique($url);
            }
            $app->router->setIdUrl($item['idurl'], $url);
            $app->router->addRedir($item['url'], $url);
            $item['url'] = $url;
        }

        $this->items[$id] = $item;
        $this->save();
        $this->changed();
        $app->cache->clear();
    }

    public function delete($id)
    {
        $item = $this->getitem($id);
        $this->getApp()->router->deleteitem($item['idurl']);
        $this->contents->delete($id);
        $list = $this->itemsposts->getPosts($id);
        $this->itemsposts->deleteItem($id);
        parent::delete($id);
        if ($this->postpropname) {
            $this->itemsposts->updatePosts($list, $this->postpropname);
        }

        $this->changed();
        $this->getApp()->cache->clear();
    }

    public function createNames($list)
    {
        if (is_string($list)) {
            $list = explode(',', trim($list));
        }

        $result = array();
        foreach ($list as $title) {
            $title = Filter::escape($title);
            if ($title == '') {
                continue;
            }

            $result[] = $this->add(0, $title);
        }

        return $result;
    }

    public function getNames(array $list)
    {
        $this->loadItems($list);
        $result = array();
        foreach ($list as $id) {
            if (!isset($this->items[$id])) {
                continue;
            }

            $result[] = $this->items[$id]['title'];
        }

        return $result;
    }

    public function getLinks(array $list)
    {
        if (!count($list)) {
            return array();
        }

        $this->loadItems($list);
        $result = array();
        foreach ($list as $id) {
            if (!isset($this->items[$id])) {
                continue;
            }

            $item = $this->items[$id];
            $result[] = sprintf('<a href="%1$s" title="%2$s">%2$s</a>', $this->getApp()->site->url . $item['url'], $item['title']);
        }

        return $result;
    }

    public function getSorted($parent, $sortname, $count)
    {
        $count = (int)$count;
        if ($sortname == 'count') {
            $sortname = 'itemscount';
        }

        if (!in_array($sortname, array(
            'title',
            'itemscount',
            'customorder',
            'id'
        ))) {
            $sortname = 'title';
        }

        $limit = $sortname == 'itemscount' ? "order by $this->thistable.$sortname desc" : "order by $this->thistable.$sortname asc";

        if ($count) {
            $limit.= " limit $count";
        }

        return $this->select($parent == - 1 ? '' : "$this->thistable.parent = $parent", $limit);
    }

    public function getIdPosts($id, $from, $perpage, $invertOrder)
    {
        $item = $this->getItem($id);
        $includeparents = (int)$item['includeparents'];
        $includechilds = (int)$item['includechilds'];
        $order = $invertOrder ? 'asc' : 'desc';
        $posts = $this->factory->getposts();

        $p = $posts->thistable;
        $t = $this->thistable;
        $ti = $this->itemsposts->thistable;
        $postprop = $this->itemsposts->postprop;
        $itemprop = $this->itemsposts->itemprop;

        if ($includeparents || $includechilds) {
            $this->loadAll();
            $all = array(
                $id
            );

            if ($includeparents) {
                $all = array_merge($all, $this->getParents($id));
            }

            if ($includechilds) {
                $all = array_merge($all, $this->getChilds($id));
            }

            $tags = sprintf('in (%s)', implode(',', $all));
        } else {
            $tags = " = $id";
        }

        $result = $this->db->res2id($this->db->query("select $ti.$postprop as $postprop, $p.id as id from $ti, $p
    where    $ti.$itemprop $tags and $p.id = $ti.$postprop and $p.status = 'published'
    order by $p.posted $order limit $from, $perpage"));

        $result = array_unique($result);
        $posts->loadItems($result);
        return $result;
    }

    public function getParents($id)
    {
        $result = array();
        while ($id = (int)$this->items[$id]['parent']) {
            //if (!isset($this->items[$id])) $this->error(sprintf('Parent category %d not exists', $id);
            $result[] = $id;
        }

        return $result;
    }

    public function getChilds($parent)
    {
        $result = array();
        foreach ($this->items as $id => $item) {
            if ($parent == $item['parent']) {
                $result[] = $id;
                $result = array_merge($result, $this->getchilds($id));
            }
        }
        return $result;
    }

    public function getSitemap($from, $count)
    {
        return $this->externalfunc(__class__, 'Getsitemap', array(
            $from,
            $count
        ));
    }

    public function getSortedPosts($id, $count, $invert)
    {
        $ti = $this->itemsposts->thistable;
        $posts = $this->factory->posts;
        $p = $posts->thistable;
        $order = $invert ? 'asc' : 'desc';
        $result = $this->db->res2id($this->db->query("select $p.id as id, $ti.post as post from $p, $ti
    where    $ti.item = $id and $p.id = $ti.post and $p.status = 'published'
    order by $p.posted $order limit 0, $count"));

        $posts->loadItems($result);
        return $result;
    }

}

