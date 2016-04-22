<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\post;
use litepubl\utils\LinkGenerator;
use litepubl\core\Str;
use litepubl\view\Args;
use litepubl\view\Theme;

class Posts extends \litepubl\core\Items
 {
    const POSTCLASS = 'litepubl\post\Post';
    public $itemcoclasses;
    public $archives;
    public $rawtable;
    public $childtable;

    public static function unsub($obj) {
        static ::i()->unbind($obj);
    }

    protected function create() {
        $this->dbversion = true;
        parent::create();
        $this->table = 'posts';
        $this->childtable = '';
        $this->rawtable = 'rawposts';
        $this->basename = 'posts/index';
        $this->addevents('edited', 'changed', 'singlecron', 'beforecontent', 'aftercontent', 'beforeexcerpt', 'afterexcerpt', 'onselect', 'onhead', 'onanhead', 'ontags');
        $this->data['archivescount'] = 0;
        $this->data['revision'] = 0;
        $this->data['syncmeta'] = false;
        $this->addmap('itemcoclasses', array());
    }

    public function getItem($id) {
        if ($result = tpost::i($id)) {
 return $result;
}


        $this->error("Item $id not found in class " . get_class($this));
    }

    public function finditems($where, $limit) {
        if (isset(titem::$instances['post']) && (count(titem::$instances['post']) > 0)) {
            $result = $this->db->idselect($where . ' ' . $limit);
            $this->loaditems($result);
            return $result;
        } else {
            return $this->select($where, $limit);
        }
    }

    public function loaditems(array $items) {
        //exclude already loaded items
        if (!isset(titem::$instances['post'])) titem::$instances['post'] = array();
        $loaded = array_keys(titem::$instances['post']);
        $newitems = array_diff($items, $loaded);
        if (!count($newitems)) {
 return $items;
}


        $newitems = $this->select(sprintf('%s.id in (%s)', $this->thistable, implode(',', $newitems)) , '');
        return array_merge($newitems, array_intersect($loaded, $items));
    }

    public function setAssoc(array $items) {
        if (count($items) == 0) {
 return array();
}


        $result = array();
        $t = new tposttransform();
        $fileitems = array();
        foreach ($items as $a) {
            $t->post = tpost::newpost($a['class']);
            $t->setassoc($a);
            $result[] = $t->post->id;
            $f = $t->post->files;
            if (count($f)) $fileitems = array_merge($fileitems, array_diff($f, $fileitems));
        }

        unset($t);
        if ($this->syncmeta) tmetapost::loaditems($result);
        if (count($fileitems)) tfiles::i()->preload($fileitems);
        $this->onselect($result);
        return $result;
    }

    public function select($where, $limit) {
        $db =  $this->getApp()->db;
        if ($this->childtable) {
            $childtable = $db->prefix . $this->childtable;
            return $this->setassoc($db->res2items($db->query("select $db->posts.*, $db->urlmap.url as url, $childtable.*
      from $db->posts, $db->urlmap, $childtable
      where $where and  $db->posts.id = $childtable.id and $db->urlmap.id  = $db->posts.idurl $limit")));
        }

        $items = $db->res2items($db->query("select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
    where $where and  $db->urlmap.id  = $db->posts.idurl $limit"));

        if (!count($items)) {
            return array();
        }

        $subclasses = array();
        foreach ($items as $id => $item) {
            if (empty($item['class'])) {
                $items[$id]['class'] = static ::POSTCLASS;
            } else if ($item['class'] != static ::POSTCLASS) {
                $subclasses[$item['class']][] = $id;
            }
        }

        foreach ($subclasses as $class => $list) {
            $subitems = call_user_func_array(array(
                str_replace('-', '\\', $class) ,
                'selectitems'
            ) , array(
                $list
            ));

            foreach ($subitems as $id => $subitem) {
                $items[$id] = array_merge($items[$id], $subitem);
            }
        }

        return $this->setassoc($items);
    }

    public function getCount() {
        return $this->db->getcount("status<> 'deleted'");
    }

    public function getChildscount($where) {
        if ($this->childtable == '') {
 return 0;
}


        $db =  $this->getApp()->db;
        $childtable = $db->prefix . $this->childtable;
        if ($res = $db->query("SELECT COUNT($db->posts.id) as count FROM $db->posts, $childtable
    where $db->posts.status <> 'deleted' and $childtable.id = $db->posts.id $where")) {
            if ($r = $db->fetchassoc($res)) {
 return $r['count'];
}


        }
        return 0;
    }

    public function getLinks($where, $tml) {
        $db = $this->db;
        $t = $this->thistable;
        $items = $db->res2assoc($db->query("select $t.id, $t.title, $db->urlmap.url as url  from $t, $db->urlmap
    where $t.status = 'published' and $where and $db->urlmap.id  = $t.idurl"));

        if (count($items) == 0) {
 return '';
}



        $result = '';
        $args = new Args();
        $theme = Theme::i();
        foreach ($items as $item) {
            $args->add($item);
            $result.= $theme->parsearg($tml, $args);
        }
        return $result;
    }

    private function beforechange($post) {
        $post->title = trim($post->title);
        $post->modified = time();
        $post->revision = $this->revision;
        $post->class = str_replace('\\', '-', get_class($post));
        if (($post->status == 'published') && ($post->posted > time())) {
            $post->status = 'future';
        } elseif (($post->status == 'future') && ($post->posted <= time())) {
            $post->status = 'published';
        }
    }

    public function add(tpost $post) {
        if ($post->posted == 0) $post->posted = time();
        $this->beforechange($post);
        if ($post->posted == 0) $post->posted = time();
        if ($post->posted <= time()) {
            if ($post->status == 'future') $post->status = 'published';
        } else {
            if ($post->status == 'published') $post->status = 'future';
        }

        if (($post->icon == 0) && ! $this->getApp()->options->icondisabled) {
            $icons = ticons::i();
            $post->icon = $icons->getid('post');
        }

        if ($post->idschema == 1) {
            $schemes = Schemes::i();
            if (isset($schemes->defaults['post'])) $post->id_view = $schemes->defaults['post'];
        }

        $post->url = LinkGenerator::i()->addurl($post, $post->schemalink);
        $id = $post->create_id();

        $this->updated($post);
        $this->cointerface('add', $post);
        $this->added($post->id);
        $this->changed();
         $this->getApp()->router->clearcache();
        return $post->id;
    }

    public function edit(tpost $post) {
        $this->beforechange($post);
        $linkgen = LinkGenerator::i();
        $linkgen->editurl($post, $post->schemalink);
        if ($post->posted <= time()) {
            if ($post->status == 'future') $post->status = 'published';
        } else {
            if ($post->status == 'published') $post->status = 'future';
        }
        $this->lock();
        $post->save();
        $this->updated($post);
        $this->cointerface('edit', $post);
        $this->unlock();
        $this->edited($post->id);
        $this->changed();

         $this->getApp()->router->clearcache();
    }

    public function delete($id) {
        if (!$this->itemexists($id)) {
 return false;
}


        $router = \litepubl\core\Router::i();
        $idurl = $this->db->getvalue($id, 'idurl');
        $this->db->setvalue($id, 'status', 'deleted');
        if ($this->childtable) {
            $db = $this->getdb($this->childtable);
            $db->delete("id = $id");
        }

        $this->lock();
        $this->PublishFuture();
        $this->UpdateArchives();
        $this->cointerface('delete', $id);
        $this->unlock();
        $this->deleted($id);
        $this->changed();
        $router->clearcache();
        return true;
    }

    public function updated(tpost $post) {
        $this->PublishFuture();
        $this->UpdateArchives();
        tcron::i()->add('single', get_class($this) , 'dosinglecron', $post->id);
    }

    public function UpdateArchives() {
        $this->archivescount = $this->db->getcount("status = 'published' and posted <= '" . Str::sqlDate() . "'");
    }

    public function dosinglecron($id) {
        $this->PublishFuture();
        Theme::$vars['post'] = tpost::i($id);
        $this->singlecron($id);
        unset(Theme::$vars['post']);
    }

    public function hourcron() {
        $this->PublishFuture();
    }

    private function publish($id) {
        $post = tpost::i($id);
        $post->status = 'published';
        $this->edit($post);
    }

    public function PublishFuture() {
        if ($list = $this->db->idselect(sprintf('status = \'future\' and posted <= \'%s\' order by posted asc', Str::sqlDate()))) {
            foreach ($list as $id) $this->publish($id);
        }
    }

    public function getRecent($author, $count) {
        $author = (int)$author;
        $where = "status != 'deleted'";
        if ($author > 1) $where.= " and author = $author";
        return $this->finditems($where, ' order by posted desc limit ' . (int)$count);
    }

    public function getPage($author, $page, $perpage, $invertorder) {
        $author = (int)$author;
        $from = ($page - 1) * $perpage;
        $t = $this->thistable;
        $where = "$t.status = 'published'";
        if ($author > 1) $where.= " and $t.author = $author";
        $order = $invertorder ? 'asc' : 'desc';
        return $this->finditems($where, " order by $t.posted $order limit $from, $perpage");
    }

    public function stripdrafts(array $items) {
        if (count($items) == 0) {
 return array();
}


        $list = implode(', ', $items);
        $t = $this->thistable;
        return $this->db->idselect("$t.status = 'published' and $t.id in ($list)");
    }

    //coclasses
    private function cointerface($method, $arg) {
        foreach ($this->coinstances as $coinstance) {
            if ($coinstance instanceof ipost) $coinstance->$method($arg);
        }
    }

    public function addrevision() {
        $this->data['revision']++;
        $this->save();
         $this->getApp()->router->clearcache();
    }

    public function getAnhead(array $items) {
        if (count($items) == 0) {
 return '';
}


        $this->loaditems($items);

        $result = '';
        foreach ($items as $id) {
            $result.= tpost::i($id)->anhead;
        }
        return $result;
    }

    //fix call reference
    public function beforecontent($post, &$result) {
        $this->callevent('beforecontent', array(
            $post, &$result
        ));
    }

    public function aftercontent($post, &$result) {
        $this->callevent('aftercontent', array(
            $post, &$result
        ));
    }

    public function beforeexcerpt($post, &$result) {
        $this->callevent('beforeexcerpt', array(
            $post, &$result
        ));
    }

    public function afterexcerpt($post, &$result) {
        $this->callevent('afterexcerpt', array(
            $post, &$result
        ));
    }

    public function getSitemap($from, $count) {
        return $this->externalfunc(__class__, 'Getsitemap', array(
            $from,
            $count
        ));
    }

} 