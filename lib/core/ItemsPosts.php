<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\core;

class ItemsPosts extends Items
{
    public $tablepost;
    public $postprop;
    public $itemprop;

    protected function create()
    {
        parent::create();
        $this->basename = 'itemsposts';
        $this->table = 'itemsposts';
        $this->tablepost = 'posts';
        $this->postprop = 'post';
        $this->itemprop = 'item';
    }

    public function add($idpost, $iditem)
    {
        $this->db->insert(
            array(
            $this->postprop => $idpost,
            $this->itemprop => $iditem
            )
        );
        $this->added();
    }

    public function exists($idpost, $iditem)
    {
        return $this->db->exists("$this->postprop = $idpost and $this->itemprop = $iditem");
    }

    public function remove($idpost, $iditem)
    {
        return $this->db->delete("$this->postprop = $idpost and $this->itemprop = $iditem");
    }

    public function delete($idpost)
    {
        return $this->deletepost($idpost);
    }

    public function deletepost($idpost)
    {
        $db = $this->db;
        $result = $db->res2id($db->query("select $this->itemprop from $this->thistable where $this->postprop = $idpost"));
        $db->delete("$this->postprop = $idpost");
        return $result;
    }

    public function deleteitem($iditem)
    {
        $this->db->delete("$this->itemprop = $iditem");
        $this->deleted();
    }

    public function setItems($idpost, array $items)
    {
        Arr::clean($items);
        $db = $this->db;
        $old = $this->getitems($idpost);
        $add = array_diff($items, $old);
        $delete = array_diff($old, $items);

        if (count($delete)) {
            $db->delete("$this->postprop = $idpost and $this->itemprop in (" . implode(', ', $delete) . ')');
        }
        if (count($add)) {
            $vals = array();
            foreach ($add as $iditem) {
                $vals[] = "($idpost, $iditem)";
            }
            $db->exec("INSERT INTO $this->thistable ($this->postprop, $this->itemprop) values " . implode(',', $vals));
        }

        return array_merge($old, $add);
    }

    public function getItems($idpost)
    {
        return $this->getApp()->db->res2id($this->getApp()->db->query("select $this->itemprop from $this->thistable where $this->postprop = $idpost"));
    }

    public function getPosts($iditem)
    {
        return $this->getApp()->db->res2id($this->getApp()->db->query("select $this->postprop from $this->thistable where $this->itemprop = $iditem"));
    }

    public function getPostscount($ititem)
    {
        $db = $this->getdb($this->tablepost);
        return $db->getcount("$db->prefix$this->tablepost.status = 'published' and id in (select $this->postprop from $this->thistable where $this->itemprop = $ititem)");
    }

    public function updateposts(array $list, $propname)
    {
        $db = $this->db;
        foreach ($list as $idpost) {
            $items = $this->getitems($idpost);
            $db->table = $this->tablepost;
            $db->setvalue($idpost, $propname, implode(', ', $items));
        }
    }
}
