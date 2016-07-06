<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\post;

use litepubl\core\Cron;
use litepubl\core\Str;
use litepubl\utils\LinkGenerator;
use litepubl\view\Schemes;

class Posts extends \litepubl\core\Items
{
    const POSTCLASS = __NAMESPACE__ . '/Post';
    public $itemcoclasses;
    public $archives;
    public $rawtable;
    public $childTable;

    public static function unsub($obj)
    {
        static ::i()->unbind($obj);
    }

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->table = 'posts';
        $this->childTable = '';
        $this->rawtable = 'rawposts';
        $this->basename = 'posts/index';
        $this->addevents('edited', 'changed', 'singlecron', 'beforecontent', 'aftercontent', 'beforeexcerpt', 'afterexcerpt', 'onselect', 'onhead', 'onanhead', 'ontags');
        $this->data['archivescount'] = 0;
        $this->data['revision'] = 0;
        $this->data['syncmeta'] = false;
        $this->addmap('itemcoclasses', array());
    }

    public function getItem($id)
    {
        if ($result = Post::i($id)) {
            return $result;
        }

        $this->error("Item $id not found in class " . get_class($this));
    }

    public function findItems(string $where, string $limit): array
    {
        if (isset(Post::$instances['post']) && (count(Post::$instances['post']))) {
            $result = $this->db->idSelect($where . ' ' . $limit);
            $this->loadItems($result);
            return $result;
        } else {
            return $this->select($where, $limit);
        }
    }

    public function loadItems(array $items)
    {
        //exclude already loaded items
        if (!isset(Post::$instances['post'])) {
            Post::$instances['post'] = array();
        }

        $loaded = array_keys(Post::$instances['post']);
        $newitems = array_diff($items, $loaded);
        if (!count($newitems)) {
            return $items;
        }

        $newitems = $this->select(sprintf('%s.id in (%s)', $this->thistable, implode(',', $newitems)), 'limit ' . count($newitems));

        return array_merge($newitems, array_intersect($loaded, $items));
    }

    public function setAssoc(array $items)
    {
        if (!count($items)) {
            return array();
        }

        $result = array();
        $fileitems = array();
        foreach ($items as $a) {
            $post = Post::newPost($a['class']);
            $post->setAssoc($a);
            $post->afterLoad();
            $result[] = $post->id;

            $f = $post->files;
            if (count($f)) {
                $fileitems = array_merge($fileitems, array_diff($f, $fileitems));
            }
        }

        if ($this->syncmeta) {
            Meta::loadItems($result);
        }

        if (count($fileitems)) {
            Files::i()->preLoad($fileitems);
        }

        $this->onselect($result);
        return $result;
    }

    public function select(string $where, string $limit): array
    {
        $db = $this->getApp()->db;
        if ($this->childTable) {
            $childTable = $db->prefix . $this->childTable;
            return $this->setAssoc(
                $db->res2items(
                    $db->query(
                        "select $db->posts.*, $childTable.*, $db->urlmap.url as url
      from $db->posts, $childTable, $db->urlmap
      where $where and  $db->posts.id = $childTable.id and $db->urlmap.id  = $db->posts.idurl $limit"
                    )
                )
            );
        }

        $items = $db->res2items(
            $db->query(
                "select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
    where $where and  $db->urlmap.id  = $db->posts.idurl $limit"
            )
        );

        if (!count($items)) {
            return array();
        }

        $subclasses = array();
        foreach ($items as $id => $item) {
            if (empty($item['class'])) {
                $items[$id]['class'] = static ::POSTCLASS;
            } elseif ($item['class'] != static ::POSTCLASS) {
                $subclasses[$item['class']][] = $id;
            }
        }

        foreach ($subclasses as $class => $list) {
            $class = str_replace('-', '\\', $class);
            $childDataItems = $class::selectChildItems($class::getChildTable(), $list);
            foreach ($childDataItems as $id => $childData) {
                $items[$id] = array_merge($items[$id], $childData);
            }
        }

        return $this->setAssoc($items);
    }

    public function getCount()
    {
        return $this->db->getcount("status<> 'deleted'");
    }

    public function getChildsCount($where)
    {
        if (!$this->childTable) {
            return 0;
        }

        $db = $this->getApp()->db;
        $childTable = $db->prefix . $this->childTable;
        $res = $db->query(
            "SELECT COUNT($db->posts.id) as count FROM $db->posts, $childTable
    where $db->posts.status <> 'deleted' and $childTable.id = $db->posts.id $where"
        );

        if ($res && ($r = $db->fetchassoc($res))) {
            return $r['count'];
        }

        return 0;
    }

    private function beforeChange($post)
    {
        $post->title = trim($post->title);
        $post->modified = time();
        $post->revision = $this->revision;
        $post->class = str_replace('\\', '-', ltrim(get_class($post), '\\'));
        if (($post->status == 'published') && ($post->posted > time())) {
            $post->status = 'future';
        } elseif (($post->status == 'future') && ($post->posted <= time())) {
            $post->status = 'published';
        }
    }

    public function add(Post $post): int
    {
        $this->beforeChange($post);
        if (!$post->posted) {
            $post->posted = time();
        }

        if ($post->posted <= time()) {
            if ($post->status == 'future') {
                $post->status = 'published';
            }
        } else {
            if ($post->status == 'published') {
                $post->status = 'future';
            }
        }

        if ($post->idschema == 1) {
            $schemes = Schemes::i();
            if (isset($schemes->defaults['post'])) {
                $post->data['idschema'] = $schemes->defaults['post'];
            }
        }

        $post->url = LinkGenerator::i()->addUrl($post, $post->schemaLink);
        $id = $post->add();

        $this->updated($post);
        $this->coInstanceCall('add', [$post]);
        $this->added($id);
        $this->changed();
        $this->getApp()->cache->clear();
        return $id;
    }

    public function edit(Post $post)
    {
        $this->beforeChange($post);
        $linkgen = LinkGenerator::i();
        $linkgen->editurl($post, $post->schemaLink);
        if ($post->posted <= time()) {
            if ($post->status == 'future') {
                $post->status = 'published';
            }
        } else {
            if ($post->status == 'published') {
                $post->status = 'future';
            }
        }

        $this->lock();
        $post->save();
        $this->updated($post);
        $this->coInstanceCall('edit', [$post]);
        $this->unlock();
        $this->edited($post->id);
        $this->changed();

        $this->getApp()->cache->clear();
    }

    public function delete($id)
    {
        if (!$this->itemExists($id)) {
            return false;
        }

        $this->db->setvalue($id, 'status', 'deleted');
        if ($this->childTable) {
            $db = $this->getdb($this->childTable);
            $db->delete("id = $id");
        }

        $this->lock();
        $this->PublishFuture();
        $this->UpdateArchives();
        $this->coInstanceCall('delete', [$id]);
        $this->unlock();
        $this->deleted($id);
        $this->changed();
        $this->getApp()->cache->clear();
        return true;
    }

    public function updated(Post $post)
    {
        $this->PublishFuture();
        $this->UpdateArchives();
        Cron::i()->add('single', get_class($this), 'dosinglecron', $post->id);
    }

    public function UpdateArchives()
    {
        $this->archivescount = $this->db->getcount("status = 'published' and posted <= '" . Str::sqlDate() . "'");
    }

    public function dosinglecron($id)
    {
        $this->PublishFuture();
        Theme::$vars['post'] = Post::i($id);
        $this->singlecron($id);
        unset(Theme::$vars['post']);
    }

    public function hourcron()
    {
        $this->PublishFuture();
    }

    private function publish($id)
    {
        $post = Post::i($id);
        $post->status = 'published';
        $this->edit($post);
    }

    public function PublishFuture()
    {
        if ($list = $this->db->idselect(sprintf('status = \'future\' and posted <= \'%s\' order by posted asc', Str::sqlDate()))) {
            foreach ($list as $id) {
                $this->publish($id);
            }
        }
    }

    public function getRecent($author, $count)
    {
        $author = (int)$author;
        $where = "status != 'deleted'";
        if ($author > 1) {
            $where.= " and author = $author";
        }

        return $this->findItems($where, ' order by posted desc limit ' . (int)$count);
    }

    public function getPage(int $author, int $page, int $perpage, bool $invertorder): array
    {
        $from = ($page - 1) * $perpage;
        $t = $this->thistable;
        $where = "$t.status = 'published'";
        if ($author > 1) {
            $where.= " and $t.author = $author";
        }

        $order = $invertorder ? 'asc' : 'desc';
        return $this->findItems($where, " order by $t.posted $order limit $from, $perpage");
    }

    public function stripDrafts(array $items)
    {
        if (count($items) == 0) {
            return array();
        }

        $list = implode(', ', $items);
        $t = $this->thistable;
        return $this->db->idSelect("$t.status = 'published' and $t.id in ($list)");
    }

    public function addRevision()
    {
        $this->data['revision']++;
        $this->save();
        $this->getApp()->cache->clear();
    }

    //fix call reference
    public function beforecontent($post, &$result)
    {
        $this->callevent(
            'beforecontent', array(
            $post, &$result
            )
        );
    }

    public function aftercontent($post, &$result)
    {
        $this->callevent(
            'aftercontent', array(
            $post, &$result
            )
        );
    }

    public function beforeexcerpt($post, &$result)
    {
        $this->callevent(
            'beforeexcerpt', array(
            $post, &$result
            )
        );
    }

    public function afterexcerpt($post, &$result)
    {
        $this->callevent(
            'afterexcerpt', array(
            $post, &$result
            )
        );
    }

    public function getSitemap($from, $count)
    {
        return $this->externalfunc(
            __class__, 'Getsitemap', array(
            $from,
            $count
            )
        );
    }
}
