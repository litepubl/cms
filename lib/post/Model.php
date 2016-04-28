<?php

namespace litepubl\post;

class Post extends \litepubl\core\Item
{
    protected $childData;
    protected $childTable;
protected $cacheData;
    public $factory;
    private $metaInstance;
    private $_onid;

    public static function i($id = 0) {
        $id = (int)$id;
        if ($id > 0) {
            if (isset(static ::$instances['post'][$id])) {
                $result = static ::$instances['post'][$id];
            } else if ($result = static ::loadpost($id)) {
                static ::$instances['post'][$id] = $result;
            } else {
                $result = null;
            }
        } else {
            $result = parent::itemInstance(get_called_class() , $id);
        }

        return $result;
    }

    public static function getInstancename() {
        return 'post';
    }

    public static function getChildTable() {
        return '';
    }

    public static function selectItems(array $items) {
        return array();
    }

    public static function select_child_items($table, array $items) {
        if (!$table || !count($items)) {
            return array();
        }

        $db =  $this->getApp()->db;
        $childtable = $db->prefix . $table;
        $list = implode(',', $items);
        return $db->res2items($db->query("select $childtable.*
    from $childtable where id in ($list)"));
    }

    public static function newPost($classname) {
        $classname = $classname ? str_replace('-', '\\', $classname) : get_called_class();
        return new $classname();
    }

    protected function create() {
        $this->table = 'posts';
        $this->childTable = static ::getChildTable();

        $this->data = array(
            'id' => 0,
            'idschema' => 1,
            'idurl' => 0,
            'parent' => 0,
            'author' => 0,
            'revision' => 0,
            'icon' => 0,
            'idperm' => 0,
            'class' => str_replace('\\', '-', get_class($this)) ,
            'posted' => static::ZERODATE,
            'modified' => 0,
            'title' => '',
            'title2' => '',
            'filtered' => '',
            'excerpt' => '',
            'rss' => '',
            'keywords' => '',
            'description' => '',
            'rawhead' => '',
            'moretitle' => '',
            'categories' => '',
            'tags' => '',
            'files' => '',
            'status' => 'published',
            'comstatus' =>  $this->getApp()->options->comstatus,
            'pingenabled' =>  $this->getApp()->options->pingenabled,
            'password' => '',
            'commentscount' => 0,
            'pingbackscount' => 0,
            'pagescount' => 0,
        );

$this->childData = [];
$this->cacheData = [
'posted' => 0,
'categories' => [],
'tags' => [],
'files' => [],
            'url' => '',
                        'rawcontent' => false,
                                    'pages' => [],
];

        $this->factory = $this->getfactory();
/*
        $posts = $this->factory->posts;
        foreach ($posts->itemcoclasses as $class) {
            $coinstance =  $this->getApp()->classes->newinstance($class);
            $coinstance->post = $this;
            $this->coinstances[] = $coinstance;
        }
*/
    }

    public function getFactory() {
        return Factory::i();
    }

    public function __get($name) {
            if ($name == 'id') {
                $result = $this->data['id'];
            } elseif (method_exists($this, $get = 'get' . $name)) {
                $result = $this->$get();
}            elseif (array_key_exists($name, $this->cacheData)) {
$result = $this->cacheData[$name];
            } elseif (array_key_exists($name, $this->data)) {
$result = $this->data[$name;
            } elseif (array_key_exists($name, $this->childData)) {
                $result = $this->childData[$name];
} else {
                $result = parent::__get($name);
}

return $result;
    }

    public function __set($name, $value) {
            if ($name == 'id') {
                $this->setId($value);
            } elseif (method_exists($this, $set = 'set' . $name)) {
                $this->$set($value);
            }elseif (array_key_exists($name, $this->cacheData)) {
$this->cacheData[$name] = $value;
            }elseif (array_key_exists($name, $this->data)) {
$this->data[$name] = $value;
            }elseif (array_key_exists($name, $this->childData)) {
                $this->childData[$name] = $value;
        } else {
        return parent::__set($name, $value);
}

return true;
    }

    public function __isset($name) {
        return parent::__isset($name)
 || array_key_exists($name, $this->cacheData)
 || array_key_exists($name, $this->childData);
    }

    //db
    public function afterDB() {
    }

    public function beforeDB() {
    }

    public function load() {
        if ($result = $this->LoadFromDB()) {
            foreach ($this->coinstances as $coinstance) $coinstance->load();
        }
        return $result;
    }

    protected function loadFromDB() {
        if ($a = static ::getAssoc($this->id)) {
            $this->setAssoc($a);
            return true;
        }
        return false;
    }

    public static function loadPost($id) {
        if ($a = static ::getAssoc($id)) {
            $self = static ::newPost($a['class']);
            $self->setAssoc($a);
            return $self;
        }
        return false;
    }

    public static function getAssoc($id) {
        $db =  static::getAppInstance()->db;
        return $db->selectAssoc("select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
    where $db->posts.id = $id and  $db->urlmap.id  = $db->posts.idurl limit 1");
    }

    public function setAssoc(array $a) {
        $trans = $this->factory->gettransform($this);
        $trans->setassoc($a);
        if ($this->childtable) {
            if ($a = $this->getdb($this->childtable)->getitem($this->id)) {
                $this->childdata = $a;
                $this->afterdb();
            }
        }
    }

    public function save() {
        if ($this->lockcount > 0) {
            return;
        }

        $this->saveToDB();
        foreach ($this->coinstances as $coinstance) {
            $coinstance->save();
        }
    }

    protected function SaveToDB() {
        $this->factory->gettransform($this)->save($this);
        if ($this->childtable) {
            $this->beforedb();
            $this->childdata['id'] = $this->id;
            $this->getdb($this->childtable)->updateassoc($this->childdata);
        }
    }

    public function createId() {
        $id = $this->factory->add($this);
        $this->setid($id);
        if ($this->childtable) {
            $this->beforeDB();
            $this->childData['id'] = $id;
            $this->getdb($this->childtable)->insert($this->childdata);
        }

        $this->idurl = $this->createUrl();
        $this->db->setvalue($id, 'idurl', $this->idurl);
        $this->onid();

        return $id;
    }

    public function createUrl() {
        return  $this->getApp()->router->add($this->url, get_class($this) , (int)$this->id);
    }

    public function onId() {
        if (isset($this->_onid) && count($this->_onid) > 0) {
            foreach ($this->_onid as $call) {
                try {
                    call_user_func($call, $this);
                }
                catch(Exception $e) {
                     $this->getApp()->options->handexception($e);
                }
            }
            unset($this->_onid);
        }

        if (isset($this->metaInstance)) {
            $this->metaInstance->id = $this->id;
            $this->metaInstance->save();
        }
    }

    public function setOnId($call) {
        if (!is_callable($call)) $this->error('Event onid not callable');
        if (isset($this->_onid)) {
            $this->_onid[] = $call;
        } else {
            $this->_onid = array(
                $call
            );
        }
    }

    public function free() {
        foreach ($this->coinstances as $coinstance) $coinstance->free();
        if (isset($this->metaInstance)) $this->metaInstance->free();
        unset($this->aprev, $this->anext, $this->metaInstance, $this->themeInstance, $this->_onid);
        parent::free();
    }

    public function getComments() {
        return $this->factory->getcomments($this->id);
    }

    public function getPingbacks() {
        return $this->factory->getpingbacks($this->id);
    }


    public function getMeta() {
        if (!isset($this->metaInstance)) $this->metaInstance = $this->factory->getmeta($this->id);
        return $this->metaInstance;
    }

    public function Getlink() {
        return  $this->getApp()->site->url . $this->url;
    }

    public function Setlink($link) {
        if ($a = @parse_url($link)) {
            if (empty($a['query'])) {
                $this->url = $a['path'];
            } else {
                $this->url = $a['path'] . '?' . $a['query'];
            }
        }
    }

    public function setTitle($title) {
        $this->data['title'] = Filter::escape(Filter::unescape($title));
    }

    public function Getisodate() {
        return date('c', $this->posted);
    }

    public function Getpubdate() {
        return date('r', $this->posted);
    }

    public function Setpubdate($date) {
        $this->data['posted'] = strtotime($date);
    }

    public function getSqlDate() {
        return Str::sqlDate($this->posted);
    }

    public function getTagnames() {
        if (count($this->tags)) {
            $tags = $this->factory->tags;
            return implode(', ', $tags->getnames($this->tags));
        }

        return '';
    }

    public function setTagnames($names) {
        $tags = $this->factory->tags;
        $this->tags = $tags->createnames($names);
    }

    public function getCatnames() {
        if (count($this->categories)) {
            $categories = $this->factory->categories;
            return implode(', ', $categories->getnames($this->categories));
        }

        return '';
    }

    public function setCatnames($names) {
        $categories = $this->factory->categories;
        $this->categories = $categories->createnames($names);
        if (count($this->categories) == 0) {
            $defaultid = $categories->defaultid;
            if ($defaultid > 0) $this->data['categories '][] = $dfaultid;
        }
    }

    public function getCategory() {
        if ($idcat = $this->getidcat()) {
            return $this->factory->categories->getname($idcat);
        }

        return '';
    }

    public function getIdcat() {
        if (($cats = $this->categories) && count($cats)) {
            return $cats[0];
        }

        return 0;
    }

    public function setFiles(array $list) {
        Arr::clean($list);
        $this->data['files'] = $list;
    }

    public function update_revision($value) {
        if ($value != $this->revision) {
            $this->updatefiltered();
            $posts = $this->factory->posts;
            $this->revision = (int)$posts->revision;
            if ($this->id > 0) $this->save();
        }
    }

    public function updatefiltered() {
        Filter::i()->filterpost($this, $this->rawcontent);
    }

    public function getRawcontent() {
        if (($this->id > 0) && ($this->data['rawcontent'] === false)) {
            $this->data['rawcontent'] = $this->rawdb->getvalue($this->id, 'rawcontent');
        }

        return $this->data['rawcontent'];
    }

    protected function getRawdb() {
        return $this->getdb('rawposts');
    }

    public function getPage($i) {
        if (isset($this->data['pages'][$i])) {
 return $this->data['pages'][$i];
}


        if ($this->id > 0) {
            if ($r = $this->getdb('pages')->getassoc("(id = $this->id) and (page = $i) limit 1")) {
                $s = $r['content'];
            } else {
                $s = false;
            }
            $this->data['pages'][$i] = $s;
            return $s;
        }
        return false;
    }

    public function addpage($s) {
        $this->data['pages'][] = $s;
        $this->data['pagescount'] = count($this->data['pages']);
        if ($this->id > 0) {
            $this->getdb('pages')->insert(array(
                'id' => $this->id,
                'page' => $this->data['pagescount'] - 1,
                'content' => $s
            ));
        }
    }

    public function deletepages() {
        $this->data['pages'] = array();
        $this->data['pagescount'] = 0;
        if ($this->id > 0) $this->getdb('pages')->iddelete($this->id);
    }

    public function getHaspages() {
        return ($this->pagescount > 1) || ($this->commentpages > 1);
    }

    public function getPagescount() {
        return $this->data['pagescount'] + 1;
    }

    public function getCountpages() {
        return max($this->pagescount, $this->commentpages);
    }

    public function getCommentpages() {
        if (! $this->getApp()->options->commentpages || ($this->commentscount <=  $this->getApp()->options->commentsperpage)) {
 return 1;
}


        return ceil($this->commentscount /  $this->getApp()->options->commentsperpage);
    }

    public function getLastcommenturl() {
        $c = $this->commentpages;
        $url = $this->url;
        if (($c > 1) && ! $this->getApp()->options->comments_invert_order) $url = rtrim($url, '/') . "/page/$c/";
        return $url;
    }

    public function clearCache() {
         $this->getApp()->cache->clearUrl($this->url);
    }

    public function getSchemalink() {
        return 'post';
    }

