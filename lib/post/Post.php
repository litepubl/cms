<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\post;

use litepubl\core\Arr;
use litepubl\core\Str;
use litepubl\view\Filter;

class Post extends \litepubl\core\Item
{
    protected $childTable;
    protected $rawTable;
    protected $pagesTable;
    protected $childData;
    protected $cacheData;
    protected $rawData;
    protected $factory;
    private $metaInstance;
    private $onIdCallback;

    public static function i($id = 0)
    {
        if ($id = (int)$id) {
            if (isset(static ::$instances['post'][$id])) {
                $result = static ::$instances['post'][$id];
            } else if ($result = static ::loadPost($id)) {
                // nothing: set $instances in afterLoad method
            } else {
                $result = null;
            }
        } else {
            $result = parent::itemInstance(get_called_class() , $id);
        }

        return $result;
    }

    public static function loadPost(int $id)
    {
        if ($a = static ::loadAssoc($id)) {
            $self = static ::newPost($a['class']);
            $self->setAssoc($a);

            if ($table = $self->getChildTable()) {
                $items = static ::selectChildItems($table, [$id]);
                $self->childData = $items[$id];
unset($self->childData['id']);
            }

$self->afterLoad();
            return $self;
        }

        return false;
    }

    public static function loadAssoc(int $id)
    {
        $db = static ::getAppInstance()->db;
        $table = static ::getChildTable();
        if ($table) {
            return $db->selectAssoc(
"select $db->posts.*, $db->prefix$table.*, $db->urlmap.url as url 
 from $db->posts, $db->prefix$table, $db->urlmap
    where $db->posts.id = $id and $db->prefix$table.id = $id and $db->urlmap.id  = $db->posts.idurl limit 1");
        } else {
            return $db->selectAssoc(
"select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
    where $db->posts.id = $id and  $db->urlmap.id  = $db->posts.idurl limit 1"
);
        }
    }

    public static function newPost(string $classname): Post
    {
        $classname = $classname ? str_replace('-', '\\', $classname) : get_called_class();
        return new $classname();
    }

    public static function getInstanceName(): string
    {
        return 'post';
    }

    public static function getChildTable(): string
    {
        return '';
    }

    public static function selectChildItems(string $table, array $items): array
    {
        if (!$table || !count($items)) {
            return array();
        }

        $db = static ::getAppInstance()->db;
        $childTable = $db->prefix . $table;
        $list = implode(',', $items);
        $count = count($items);
static::getappinstance()->getlogmanager()->trace($list);
        return $db->res2items($db->query(
"select $childTable.* from $childTable where id in ($list) limit $count"
));
    }

    protected function create()
    {
        $this->table = 'posts';
        $this->rawTable = 'rawposts';
        $this->pagesTable = 'pages';
        $this->childTable = static ::getChildTable();

        $options = $this->getApp()->options;
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
            'posted' => static ::ZERODATE,
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
            'comstatus' => $options->comstatus,
            'pingenabled' => $options->pingenabled ? '1' : '0',
            'password' => '',
            'commentscount' => 0,
            'pingbackscount' => 0,
            'pagescount' => 0,
        );

        $this->rawData = [];
        $this->childData = [];
$this->cacheData = [
'posted' => 0,
'categories' => [],
'tags' => [],
'files' => [],
            'url' => '',
'created' => 0,
            'modified' => 0,
                                    'pages' => [],
];


        $this->factory = $this->getfactory();
$this->factory->createCoInstances($this);
    }

    public function getFactory()
    {
        return Factory::i();
    }

    public function getView(): View
    {
        $view = $this->factory->getView();
        $view->setPost($this);
        return $view;
    }

    public function __get($name)
    {
        if ($name == 'id') {
            $result = (int)$this->data['id'];
        } elseif (method_exists($this, $get = 'get' . $name)) {
            $result = $this->$get();
        } elseif (array_key_exists($name, $this->cacheData)) {
            $result = $this->cacheData[$name];
        } elseif (method_exists($this, $get = 'getCache' . $name)) {
            $result = $this->$get();
            $this->cacheData[$name] = $result;
        } elseif (array_key_exists($name, $this->data)) {
            $result = $this->data[$name];
        } elseif (array_key_exists($name, $this->childData)) {
            $result = $this->childData[$name];
        } elseif (array_key_exists($name, $this->rawData)) {
            $result = $this->rawData[$name];
        } else {
            $result = parent::__get($name);
        }

        return $result;
    }

    public function __set($name, $value)
    {
        if ($name == 'id') {
            $this->setId($value);
        } elseif (method_exists($this, $set = 'set' . $name)) {
            $this->$set($value);
        } elseif (array_key_exists($name, $this->cacheData)) {
            $this->cacheData[$name] = $value;
        } elseif (array_key_exists($name, $this->data)) {
            $this->data[$name] = $value;
        } elseif (array_key_exists($name, $this->childData)) {
            $this->childData[$name] = $value;
        } elseif (array_key_exists($name, $this->rawData)) {
            $this->rawData[$name] = $value;
        } else {
            return parent::__set($name, $value);
        }

        return true;
    }

    public function __isset($name)
    {
        return parent::__isset($name) || array_key_exists($name, $this->cacheData) || array_key_exists($name, $this->childData) || array_key_exists($name, $this->rawData);
    }

    public function load()
    {
return true;
}

public function afterLoad()
{
static::$instances['post'][$this->id] = $this;
parent::afterLoad();
}

    public function setAssoc(array $a)
    {
        $this->cacheData = [];
        foreach ($a as $k => $v) {
            if (array_key_exists($k, $this->data)) {
                $this->data[$k] = $v;
            } elseif (array_key_exists($k, $this->childData)) {
                $this->childData[$k] = $v;
            } elseif (array_key_exists($k, $this->rawData)) {
                $this->rawData[$k] = $v;
            } else {
                $this->cacheData[$k] = $v;
            }
        }
    }

    public function save()
    {
        if ($this->lockcount > 0) {
            return;
        }

        $this->saveToDB();

            $this->coInstanceCall('save()', []);
    }

    protected function saveToDB()
    {
        if ($this->id) {
            $this->db->updateAssoc($this->data);

            $this->modified = time();
            $this->getDB($this->rawTable)->setValues($this->id, $this->rawData);
        } else {
        }

        if ($this->childTable) {
            $this->getDB($this->childTable)->setValues($this->id, $this->childData);
        }
    }

    public function add()
    {
        $a = $this->data;
        unset($a['id']);
        $id = $this->db->add($a);

        $rawData = $this->prepareRawData();
        $rawData['id'] = $id;
        $this->getDB($this->rawTable)->insert($rawData);

        $this->setId($id);

        $this->savePages();
        if ($this->childTable) {
            $childData = $this->childData;
            $childData['id'] = $id;
            $this->getDB($this->childTable)->insert($childData);
        }

        $this->idurl = $this->createUrl();
        $this->db->setValue($id, 'idurl', $this->idurl);

        $this->onId();
        return $id;
    }

    protected function prepareRawData()
    {
        if (!$this->created) {
            $this->created = time();
        }

        if (!$this->modified) {
            $this->modified = time();
        }

        if (!isset($this->rawData['rawcontent'])) {
            $this->rawData['rawcontent'] = '';
        }

        return $this->rawData;
    }

    public function createUrl()
    {
        return $this->getApp()->router->add($this->url, get_class($this) , (int)$this->id);
    }

    public function onId()
    {
        if ($this->onIdCallback) {
            $this->onIdCallback->fire();
            $this->onIdCallback = null;
        }

        if (isset($this->metaInstance)) {
            $this->metaInstance->id = $this->id;
            $this->metaInstance->save();
        }
    }

    public function setOnId($callback)
    {
        if (!$this->onIdCallback) {
            $this->onIdCallback = new Callback();
        }

        $this->onIdCallback->add($call);
    }

    public function free()
    {
            $this->coInstanceCall('free', []);

        if (isset($this->metaInstance)) {
            $this->metaInstance->free();
        }

        parent::free();
    }

    public function getComments()
    {
        return $this->factory->getcomments($this->id);
    }

    public function getPingbacks()
    {
        return $this->factory->getpingbacks($this->id);
    }

    public function getMeta()
    {
        if (!isset($this->metaInstance)) {
            $this->metaInstance = $this->factory->getmeta($this->id);
        }

        return $this->metaInstance;
    }

    //props
    protected function setDataProp($name, $value, $sql)
    {
        $this->cacheData[$name] = $value;
        if (array_key_exists($name, $this->data)) {
            $this->data[$name] = $sql;
        } elseif (array_key_exists($name, $this->childData)) {
            $this->childData[$name] = $sql;
        } elseif (array_key_exists($name, $this->rawData)) {
            $this->rawData[$name] = $sql;
        }
    }

    protected function setArrProp($name, array $list)
    {
        Arr::clean($list);
        $this->setDataProp($name, $list, implode(',', $list));
    }

    protected function setBoolProp($name, $value)
    {
        $this->setDataProp($name, $value, $value ? '1' : '0');
    }

    //cache props
    protected function getArrProp($name)
    {
        if ($s = $this->data[$name]) {
            return explode(',', $s);
        } else {
            return [];
        }
    }

    protected function getCacheCategories()
    {
        return $this->getArrProp('categories');
    }

    protected function getCacheTags()
    {
        return $this->getArrProp('tags');
    }

    protected function getCacheFiles()
    {
        return $this->getArrProp('files');
    }

    public function setFiles(array $list)
    {
        $this->setArrProp('files', $list);
    }

    public function setCategories(array $list)
    {
        $this->setArrProp('categories', $list);
    }

    public function setTags(array $list)
    {
        $this->setArrProp('tags', $list);
    }

    protected function getCachePosted()
    {
        return $this->data['posted'] == static ::ZERODATE ? 0 : strtotime($this->data['posted']);
    }

    public function setPosted($timestamp)
    {
        $this->data['posted'] = Str::sqlDate($timestamp);
        $this->cacheData['posted'] = $timestamp;
    }

    protected function getCacheModified()
    {
        return !isset($this->rawData['modified']) || $this->rawData['modified'] == static ::ZERODATE ? 0 : strtotime($this->rawData['modified']);
    }

    public function setModified($timestamp)
    {
        $this->rawData['modified'] = Str::sqlDate($timestamp);
        $this->cacheData['modified'] = $timestamp;
    }

    protected function getCacheCreated()
    {
        return !isset($this->rawData['created']) || $this->rawData['created'] == static ::ZERODATE ? 0 : strtotime($this->rawData['created']);
    }

    public function setCreated($timestamp)
    {
        $this->rawData['created'] = Str::sqlDate($timestamp);
        $this->cacheData['created'] = $timestamp;
    }

    public function Getlink()
    {
        return $this->getApp()->site->url . $this->url;
    }

    public function Setlink($link)
    {
        if ($a = @parse_url($link)) {
            if (empty($a['query'])) {
                $this->url = $a['path'];
            } else {
                $this->url = $a['path'] . '?' . $a['query'];
            }
        }
    }

    public function setTitle($title)
    {
        $this->data['title'] = Filter::escape(Filter::unescape($title));
    }

    public function getIsoDate()
    {
        return date('c', $this->posted);
    }

    public function getPubDate()
    {
        return date('r', $this->posted);
    }

    public function setPubDate($date)
    {
        $this->setDateProp('posted', strtotime($date));
    }

    public function getSqlDate()
    {
        return $this->data['posted'];
    }

    public function getTagnames()
    {
        if (count($this->tags)) {
            $tags = $this->factory->tags;
            return implode(', ', $tags->getnames($this->tags));
        }

        return '';
    }

    public function setTagnames($names)
    {
        $tags = $this->factory->tags;
        $this->tags = $tags->createnames($names);
    }

    public function getCatnames()
    {
        if (count($this->categories)) {
            $categories = $this->factory->categories;
            return implode(', ', $categories->getnames($this->categories));
        }

        return '';
    }

    public function setCatNames($names)
    {
        $categories = $this->factory->categories;
        $catItems = $categories->createnames($names);

        if (!count($catItems)) {
            $defaultid = $categories->defaultid;
            if ($defaultid > 0) {
                $catItems[] = $defaultid;
            }
        }

        $this->categories = $catItems;
    }

    public function getCategory()
    {
        if ($idcat = $this->getidcat()) {
            return $this->factory->categories->getName($idcat);
        }

        return '';
    }

    public function getIdcat()
    {
        if (($cats = $this->categories) && count($cats)) {
            return $cats[0];
        }

        return 0;
    }

    public function checkRevision()
    {
        $this->updateRevision((int)$this->factory->posts->revision);
    }

    public function updateRevision($value)
    {
        if ($value != $this->revision) {
            $this->updateFiltered();
            $posts = $this->factory->posts;
            $this->revision = (int)$posts->revision;
            if ($this->id > 0) {
                $this->save();
            }
        }
    }

    public function updateFiltered()
    {
        Filter::i()->filterPost($this, $this->rawcontent);
    }

    public function setRawContent($s)
    {
        $this->rawData['rawcontent'] = $s;
    }

    public function getRawContent()
    {
        if (isset($this->rawData['rawcontent'])) {
            return $this->rawData['rawcontent'];
        }

        if (!$this->id) {
            return '';
        }

        $this->rawData = $this->getDB($this->rawTable)->getItem($this->id);
        unset($this->rawData['id']);
        return $this->rawData['rawcontent'];
    }

    public function getPage(int $i)
    {
        if (isset($this->cacheData['pages'][$i])) {
            return $this->cacheData['pages'][$i];
        }

        if ($this->id > 0) {
            if ($r = $this->getdb($this->pagesTable)->getAssoc("(id = $this->id) and (page = $i) limit 1")) {
                $s = $r['content'];
            } else {
                $s = false;
            }

            $this->cacheData['pages'][$i] = $s;
            return $s;
        }
        return false;
    }

    public function addPage($s)
    {
        $this->cacheData['pages'][] = $s;
        $this->data['pagescount'] = count($this->cacheData['pages']);
        if ($this->id > 0) {
            $this->getdb($this->pagesTable)->insert(array(
                'id' => $this->id,
                'page' => $this->data['pagescount'] - 1,
                'content' => $s
            ));
        }
    }

    public function deletePages()
    {
        $this->cacheData['pages'] = array();
        $this->data['pagescount'] = 0;
        if ($this->id > 0) {
            $this->getdb($this->pagesTable)->idDelete($this->id);
        }
    }

    public function savePages()
    {
        if (isset($this->cacheData['pages'])) {
            $db = $this->getDB($this->pagesTable);
            foreach ($this->cacheData['pages'] as $index => $content) {
                $db->insert(array(
                    'id' => $this->id,
                    'page' => $index,
                    'content' => $content,
                ));
            }
        }
    }

    public function getHasPages()
    {
        return ($this->pagescount > 1) || ($this->commentpages > 1);
    }

    public function getPagesCount()
    {
        return $this->data['pagescount'] + 1;
    }

    public function getCountPages()
    {
        return max($this->pagescount, $this->commentpages);
    }

    public function getCommentPages()
    {
        $options = $this->getApp()->options;
        if (!$options->commentpages || ($this->commentscount <= $options->commentsperpage)) {
            return 1;
        }

        return ceil($this->commentscount / $options->commentsperpage);
    }

    public function getLastCommentUrl()
    {
        $c = $this->commentpages;
        $url = $this->url;
        if (($c > 1) && !$this->getApp()->options->comments_invert_order) {
            $url = rtrim($url, '/') . "/page/$c/";
        }

        return $url;
    }

    public function clearCache()
    {
        $this->getApp()->cache->clearUrl($this->url);
    }

    public function getSchemalink()
    {
        return 'post';
    }

    public function setContent($s)
    {
        if (!is_string($s)) {
            $this->error('Error! Post content must be string');
        }

        $this->rawcontent = $s;
        Filter::i()->filterpost($this, $s);
    }
}

