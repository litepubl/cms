<?php
//Announce.php
namespace litepubl\post;

use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;
use litepubl\view\Vars;

class Announce
{
    public $theme;

    public function __construct(Theme $theme = null)
    {
        $this->theme = $theme ? $theme : Theme::context();
    }

    private function getKey($postanounce)
    {
        if (!$postanounce || $postanounce == 'excerpt' || $postanounce == 'default') {
            return 'excerpt';
        }

        if ($postanounce === true || $postanounce === 1 || $postanounce == 'lite') {
            return 'lite';
        }

        return 'card';
    }

    public function getPosts(array $items, $postanounce)
    {
        if (!count($items)) {
            return '';
        }

        $result = '';
        $keyTemplate = $this->getKey($postanounce);
        Posts::i()->loaditems($items);
        $this->theme->setVar('lang', Lang::i('default'));
        $vars = new Vars();
        $view = new View();
        $vars->post = $view;

        foreach ($items as $id) {
            $post = Post::i($id);
            $view->setPost($post);
            $result.= $view->getContExcerpt($keyTemplate);
            // has $author.* tags in tml
            if (isset($vars->author)) {
                unset($vars->author);
            }
        }

        if ($tml = $this->theme->templates['content.excerpts' . ($keyTemplate == 'excerpt' ? '' : '.' . $keyTemplate) ]) {
            $result = str_replace('$excerpt', $result, $this->theme->parse($tml));
        }

        return $result;
    }

    public function getPostsNavi(array $items, $url, $count, $postanounce, $perpage)
    {
        $result = $this->getPosts($items, $postanounce);

        $app = $this->theme->getApp();
        if (!$perpage) {
            $perpage = $app->options->perpage;
        }

        $result.= $this->theme->getPages($url, $app->context->request->page, ceil($count / $perpage));
        return $result;
    }

    public function getLinks($where, $tml)
    {
        $theme = $this->theme;
        $db = $theme->getApp()->db;
        $items = $db->res2assoc($db->query("select $t.id, $t.title, $db->urlmap.url as url  from $t, $db->urlmap
    where $t.status = 'published' and $where and $db->urlmap.id  = $t.idurl"));

        if (!count($items)) {
            return '';
        }

        $result = '';
        $args = new Args();
        foreach ($items as $item) {
            $args->add($item);
            $result.= $theme->parseArg($tml, $args);
        }
        return $result;
    }

    public function getAnHead(array $items)
    {
        if (!count($items)) {
            return '';
        }

        Posts::i()->loadItems($items);

        $result = '';
        $view = new View();
        foreach ($items as $id) {
            $view->setPost(Post::i($id));
            $result.= $view->anhead;
        }

        return $result;
    }

}

//Factory.php
namespace litepubl\post;

use litepubl\comments\Comments;
use litepubl\comments\Pingbacks;
use litepubl\core\Users;
use litepubl\pages\Users as UserPages;
use litepubl\tag\Cats;
use litepubl\tag\Tags;

class Factory
{
    use \litepubl\core\Singleton;

    public function __get($name)
    {
        return $this->{'get' . $name}();
    }

    public function getPosts()
    {
        return Posts::i();
    }

    public function getFiles()
    {
        return Files::i();
    }

    public function getTags()
    {
        return Tags::i();
    }

    public function getCats()
    {
        return Cats::i();
    }

    public function getCategories()
    {
        return $this->getcats();
    }

    public function getTemplatecomments()
    {
        return Templates::i();
    }

    public function getComments($id)
    {
        return Comments::i($id);
    }

    public function getPingbacks($id)
    {
        return Pingbacks::i($id);
    }

    public function getMeta($id)
    {
        return Meta::i($id);
    }

    public function getUsers()
    {
        return Users::i();
    }

    public function getUserpages()
    {
        return UserPages::i();
    }

    public function getView()
    {
        return View::i();
    }

}

//Files.php
namespace litepubl\post;

use litepubl\core\Str;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Theme;
use litepubl\view\Vars;

class Files extends \litepubl\core\Items
{
    public $cachetml;

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->basename = 'files';
        $this->table = 'files';
        $this->addevents('changed', 'edited', 'ongetfilelist', 'onlist');
        $this->cachetml = array();
    }

    public function getItemsposts()
    {
        return FilesItems::i();
    }

    public function preload(array $items)
    {
        $items = array_diff($items, array_keys($this->items));
        if (count($items)) {
            $this->select(sprintf('(id in (%1$s)) or (parent in (%1$s))', implode(',', $items)) , '');
        }
    }

    public function getUrl($id)
    {
        $item = $this->getitem($id);
        return $this->getApp()->site->files . '/files/' . $item['filename'];
    }

    public function getLink($id)
    {
        $item = $this->getitem($id);
        $icon = '';
        if (($item['icon'] != 0) && ($item['media'] != 'icon')) {
            $icon = $this->geticon($item['icon']);
        }
        return sprintf('<a href="%1$s/files/%2$s" title="%3$s">%4$s</a>', $this->getApp()->site->files, $item['filename'], $item['title'], $icon . $item['description']);
    }

    public function getIcon($id)
    {
        return sprintf('<img src="%s" alt="icon" />', $this->geturl($id));
    }

    public function getHash($filename)
    {
        return trim(base64_encode(md5_file($filename, true)) , '=');
    }

    public function additem(array $item)
    {
        $realfile = $this->getApp()->paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
        $item['author'] = $this->getApp()->options->user;
        $item['posted'] = Str::sqlDate();
        $item['hash'] = $this->gethash($realfile);
        $item['size'] = filesize($realfile);

        //fix empty props
        foreach (array(
            'mime',
            'title',
            'description',
            'keywords'
        ) as $prop) {
            if (!isset($item[$prop])) $item[$prop] = '';
        }
        return $this->insert($item);
    }

    public function insert(array $item)
    {
        $item = $this->escape($item);
        $id = $this->db->add($item);
        $this->items[$id] = $item;
        $this->changed();
        $this->added($id);
        return $id;
    }

    public function escape(array $item)
    {
        foreach (array(
            'title',
            'description',
            'keywords'
        ) as $name) {
            $item[$name] = Filter::escape(Filter::unescape($item[$name]));
        }
        return $item;
    }

    public function edit($id, $title, $description, $keywords)
    {
        $item = $this->getitem($id);
        if (($item['title'] == $title) && ($item['description'] == $description) && ($item['keywords'] == $keywords)) {
            return false;
        }

        $item['title'] = $title;
        $item['description'] = $description;
        $item['keywords'] = $keywords;
        $item = $this->escape($item);
        $this->items[$id] = $item;
        $this->db->updateassoc($item);
        $this->changed();
        $this->edited($id);
        return true;
    }

    public function delete($id)
    {
        if (!$this->itemExists($id)) {
            return false;
        }

        $list = $this->itemsposts->getposts($id);
        $this->itemsposts->deleteitem($id);
        $this->itemsposts->updateposts($list, 'files');

        $item = $this->getitem($id);
        if ($item['idperm'] == 0) {
            @unlink($this->getApp()->paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']));
        } else {
            @unlink($this->getApp()->paths->files . 'private' . DIRECTORY_SEPARATOR . basename($item['filename']));
            $this->getApp()->router->delete('/files/' . $item['filename']);
        }

        parent::delete($id);

        if ((int)$item['preview']) {
            $this->delete($item['preview']);
        }

        if ((int)$item['midle']) {
            $this->delete($item['midle']);
        }

        $this->getdb('imghashes')->delete("id = $id");
        $this->changed();
        return true;
    }

    public function setContent($id, $content)
    {
        if (!$this->itemExists($id)) {
            return false;
        }

        $item = $this->getitem($id);
        $realfile = $this->getApp()->paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
        if (file_put_contents($realfile, $content)) {
            $item['hash'] = $this->gethash($realfile);
            $item['size'] = filesize($realfile);
            $this->items[$id] = $item;
            if ($this->dbversion) {
                $item['id'] = $id;
                $this->db->updateassoc($item);
            } else {
                $this->save();
            }
        }
    }

    public function exists($filename)
    {
        return $this->indexof('filename', $filename);
    }

    public function getFilelist(array $list, $excerpt)
    {
        if ($result = $this->ongetfilelist($list, $excerpt)) {
            return $result;
        }

        if (count($list) == 0) {
            return '';
        }

        return $this->getlist($list, $excerpt ? $this->gettml('content.excerpts.excerpt.filelist') : $this->gettml('content.post.filelist'));
    }

    public function getTml($basekey)
    {
        if (isset($this->cachetml[$basekey])) {
            return $this->cachetml[$basekey];
        }

        $theme = Theme::i();
        $result = array(
            'container' => $theme->templates[$basekey],
        );

        $key = $basekey . '.';
        foreach ($theme->templates as $k => $v) {
            if (Str::begin($k, $key)) {
                $result[substr($k, strlen($key)) ] = $v;
            }
        }

        $this->cachetml[$basekey] = $result;
        return $result;
    }

    public function getList(array $list, array $tml)
    {
        if (!count($list)) {
            return '';
        }

        $this->onlist($list);
        $result = '';
        $this->preload($list);

        //sort by media type
        $items = array();
        foreach ($list as $id) {
            if (!isset($this->items[$id])) {
                continue;
            }

            $item = $this->items[$id];
            $type = $item['media'];
            if (isset($tml[$type])) {
                $items[$type][] = $id;
            } else {
                $items['file'][] = $id;
            }
        }

        $theme = Theme::i();
        $args = new Args();
        $args->count = count($list);

        $url = $this->getApp()->site->files . '/files/';

        $preview = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
        Theme::$vars['preview'] = $preview;
        $midle = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
        Theme::$vars['midle'] = $midle;

        $index = 0;

        foreach ($items as $type => $subitems) {
            $args->subcount = count($subitems);
            $sublist = '';
            foreach ($subitems as $typeindex => $id) {
                $item = $this->items[$id];
                $args->add($item);
                $args->link = $url . $item['filename'];
                $args->id = $id;
                $args->typeindex = $typeindex;
                $args->index = $index++;
                $args->preview = '';
                $preview->exchangeArray([]);

                if ($idmidle = (int)$item['midle']) {
                    $midle->exchangeArray($this->getitem($idmidle));
                    $midle->link = $url . $midle->filename;
                    $midle->json = $this->getjson($idmidle);
                } else {
                    $midle->exchangeArray([]);
                    $midle->link = '';
                    $midle->json = '';
                }

                if ((int)$item['preview']) {
                    $preview->exchangeArray($this->getitem($item['preview']));
                } elseif ($type == 'image') {
                    $preview->exchangeArray($item);
                    $preview->id = $id;
                } elseif ($type == 'video') {
                    $args->preview = $theme->parseArg($tml['videos.fallback'], $args);
                    $preview->exchangeArray([]);
                }

                if ($preview->count()) {
                    $preview->link = $url . $preview->filename;
                    $args->preview = $theme->parseArg($tml['preview'], $args);
                }

                $args->json = $this->getjson($id);
                $sublist.= $theme->parseArg($tml[$type], $args);
            }

            $args->__set($type, $sublist);
            $result.= $theme->parseArg($tml[$type . 's'], $args);
        }

        unset(Theme::$vars['preview'], $preview, Theme::$vars['midle'], $midle);
        $args->files = $result;
        return $theme->parseArg($tml['container'], $args);
    }

    public function postedited($idpost)
    {
        $post = Post::i($idpost);
        $this->itemsposts->setitems($idpost, $post->files);
    }

    public function getFirstimage(array $items)
    {
        foreach ($items as $id) {
            $item = $this->getitem($id);
            if (('image' == $item['media']) && ($idpreview = (int)$item['preview'])) {
                $baseurl = $this->getApp()->site->files . '/files/';
                $args = new Args();
                $args->add($item);
                $args->link = $baseurl . $item['filename'];
                $args->json = $this->getjson($id);

                $preview = new \ArrayObject($this->getitem($idpreview) , \ArrayObject::ARRAY_AS_PROPS);
                $preview->link = $baseurl . $preview->filename;

                $midle = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
                if ($idmidle = (int)$item['midle']) {
                    $midle->exchangeArray($this->getitem($idmidle));
                    $midle->link = $baseurl . $midle->filename;
                    $midle->json = $this->getjson($idmidle);
                } else {
                    $midle->json = '';
                }

                $vars = new Vars();
                $vars->preview = $preview;
                $vars->midle = $midle;
                $theme = Theme::i();
                return $theme->parseArg($theme->templates['content.excerpts.excerpt.firstimage'], $args);
            }
        }

        return '';
    }

    public function getJson($id)
    {
        $item = $this->getitem($id);
        return Str::jsonAttr(array(
            'id' => $id,
            'link' => $this->getApp()->site->files . '/files/' . $item['filename'],
            'width' => $item['width'],
            'height' => $item['height'],
            'size' => $item['size'],
            'midle' => $item['midle'],
            'preview' => $item['preview'],
        ));
    }

}

//FilesItems.php
namespace litepubl\post;

class FilesItems extends \litepubl\core\ItemsPosts
{
    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->basename = 'fileitems';
        $this->table = 'filesitemsposts';
    }

}

//Meta.php
namespace litepubl\post;

use litepubl\core\Str;

class Meta extends \litepubl\core\Item
{

    public static function getInstancename()
    {
        return 'postmeta';
    }

    protected function create()
    {
        $this->table = 'postsmeta';
    }

    public function getDbversion()
    {
        return true;
    }

    public function __set($name, $value)
    {
        if ($name == 'id') {
            return $this->setid($value);
        }

        $exists = isset($this->data[$name]);
        if ($exists && ($this->data[$name] == $value)) {
            return true;
        }

        $this->data[$name] = $value;
        $name = Str::quote($name);
        $value = Str::quote($value);
        if ($exists) {
            $this->db->update("value = $value", "id = $this->id and name = $name");
        } else {
            $this->db->insertrow("(id, name, value) values ($this->id, $name, $value)");
        }
    }

    public function __unset($name)
    {
        $this->remove($name);
    }

    //db
    public function load()
    {
        $this->LoadFromDB();
        return true;
    }

    protected function LoadFromDB()
    {
        $db = $this->db;
        $res = $db->select("id = $this->id");
        if (is_object($res)) {
            while ($r = $res->fetch_assoc()) {
                $this->data[$r['name']] = $r['value'];
            }
        }
        return true;
    }

    protected function SaveToDB()
    {
        $db = $this->db;
        $db->delete("id = $this->id");
        foreach ($this->data as $name => $value) {
            if ($name == 'id') {
                continue;
            }

            $name = Str::quote($name);
            $value = Str::quote($value);
            $this->db->insertrow("(id, name, value) values ($this->id, $name, $value)");
        }
    }

    public function remove($name)
    {
        if ($name == 'id') {
            return;
        }

        unset($this->data[$name]);
        $this->db->delete("id = $this->id and name = '$name'");
    }

    public static function loaditems(array $items)
    {
        if (!count($items)) {
            return;
        }

        //exclude already loaded items
        if (isset(static ::$instances['postmeta'])) {
            $items = array_diff($items, array_keys(static ::$instances['postmeta']));
            if (!count($items)) {
                return;
            }

        } else {
            static ::$instances['postmeta'] = array();
        }

        $instances = & static ::$instances['postmeta'];
        $db = $this->getApp()->db;
        $db->table = 'postsmeta';
        $res = $db->select(sprintf('id in (%s)', implode(',', $items)));
        while ($row = $db->fetchassoc($res)) {
            $id = (int)$row['id'];
            if (!isset($instances[$id])) {
                $instances[$id] = new self();
                $instances[$id]->data['id'] = $id;
            }

            $instances[$id]->data[$row['name']] = $row['value'];
        }

        return $items;
    }

}

//Post.php
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
                static ::$instances['post'][$id] = $result;
            } else {
                $result = null;
            }
        } else {
            $result = parent::itemInstance(get_called_class() , $id);
        }

        return $result;
    }

    public static function loadPost($id)
    {
        if ($a = static ::loadAssoc($id)) {
            $self = static ::newPost($a['class']);
            $self->setAssoc($a);

            if (get_class($self) != get_called_class()) {
                $items = static ::selectChildItems($self->getChildTable() , [$id]);
                $self->setAssoc($items[0]);
            }

            return $self;
        }

        return false;
    }

    public static function loadAssoc($id)
    {
        $db = static ::getAppInstance()->db;
        $table = static ::getChildTable();
        if ($table) {
            return $db->selectAssoc("select $db->posts.*, $db->prefix$table.*, $db->urlmap.url as url 
 from $db->posts, $table, $db->urlmap
    where $db->posts.id = $id and $table.id = $id and $db->urlmap.id  = $db->posts.idurl limit 1");
        } else {
            return $db->selectAssoc("select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
    where $db->posts.id = $id and  $db->urlmap.id  = $db->posts.idurl limit 1");
        }
    }

    public static function newPost($classname)
    {
        $classname = $classname ? str_replace('-', '\\', $classname) : get_called_class();
        return new $classname();
    }

    public static function getInstanceName()
    {
        return 'post';
    }

    public static function getChildTable()
    {
        return '';
    }

    public static function loadChildData(array $items)
    {
        if ($table = static ::getChildTable()) {
            return static ::selectChildItems($table, $items);
        } else {
            return [];
        }
    }

    protected static function selectChildItems($table, array $items)
    {
        if (!$table || !count($items)) {
            return array();
        }

        $db = static ::getAppInstance()->db;
        $childTable = $db->prefix . $table;
        $list = implode(',', $items);
        $count = count($items);
        return $db->res2items($db->query("select $childTable.* from $childTable where id in ($list) limit $count"));
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
        $this->cacheData = ['posted' => 0, 'categories' => [], 'tags' => [], 'files' => [], 'url' => '', 'created' => 0, 'modified' => 0, 'pages' => [], ];

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

    public function getFactory()
    {
        return Factory::i();
    }

    public function getView()
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
        if ($result = $this->loadFromDB()) {
            foreach ($this->coinstances as $coinstance) {
                $coinstance->load();
            }
        }
        return $result;
    }

    protected function loadFromDB()
    {
        if ($a = static ::loadAssoc($this->id)) {
            $this->setAssoc($a);
            return true;
        }

        return false;
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

        foreach ($this->coinstances as $coinstance) {
            $coinstance->save();
        }
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
        foreach ($this->coinstances as $coinstance) {
            $coinstance->free();
        }
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

    public function getPage($i)
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
            $this->childData['pages'][$i] = $s;
            return $s;
        }
        return false;
    }

    public function addPage($s)
    {
        $this->childData['pages'][] = $s;
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
        $this->childData['pages'] = array();
        $this->data['pagescount'] = 0;
        if ($this->id > 0) {
            $this->getdb($this->pagesTable)->iddelete($this->id);
        }
    }

    public function savePages()
    {
        if (isset($this->childData['pages'])) {
            $db = $this->getDB($this->pagesTable);
            foreach ($this->childData['pages'] as $index => $content) {
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

//Posts.php
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

    public function findItems($where, $limit)
    {
        if (isset(Post::$instances['post']) && (count(Post::$instances['post']) > 0)) {
            $result = $this->db->idselect($where . ' ' . $limit);
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

        $newitems = $this->select(sprintf('%s.id in (%s)', $this->thistable, implode(',', $newitems)) , '');
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
            Files::i()->preload($fileitems);
        }

        $this->onselect($result);
        return $result;
    }

    public function select($where, $limit)
    {
        $db = $this->getApp()->db;
        if ($this->childTable) {
            $childTable = $db->prefix . $this->childTable;
            return $this->setAssoc($db->res2items($db->query("select $db->posts.*, $childTable.*, $db->urlmap.url as url
      from $db->posts, $childTable, $db->urlmap
      where $where and  $db->posts.id = $childTable.id and $db->urlmap.id  = $db->posts.idurl $limit")));
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
            $class = str_replace('-', '\\', $class);
            $subitems = $class::loadChildData($list);
            foreach ($subitems as $id => $subitem) {
                $items[$id] = array_merge($items[$id], $subitem);
            }
        }

        return $this->setAssoc($items);
    }

    public function getCount()
    {
        return $this->db->getcount("status<> 'deleted'");
    }

    public function getChildscount($where)
    {
        if (!$this->childTable) {
            return 0;
        }

        $db = $this->getApp()->db;
        $childTable = $db->prefix . $this->childTable;
        $res = $db->query("SELECT COUNT($db->posts.id) as count FROM $db->posts, $childTable
    where $db->posts.status <> 'deleted' and $childTable.id = $db->posts.id $where");

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
        $post->class = str_replace('\\', '-', ltrim(get_class($post) , '\\'));
        if (($post->status == 'published') && ($post->posted > time())) {
            $post->status = 'future';
        } elseif (($post->status == 'future') && ($post->posted <= time())) {
            $post->status = 'published';
        }
    }

    public function add(Post $post)
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
        $this->cointerface('add', $post);
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
        $this->cointerface('edit', $post);
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
        $this->cointerface('delete', $id);
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
        Cron::i()->add('single', get_class($this) , 'dosinglecron', $post->id);
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

    public function getPage($author, $page, $perpage, $invertorder)
    {
        $author = (int)$author;
        $from = ($page - 1) * $perpage;
        $t = $this->thistable;
        $where = "$t.status = 'published'";
        if ($author > 1) {
            $where.= " and $t.author = $author";
        }

        $order = $invertorder ? 'asc' : 'desc';
        return $this->finditems($where, " order by $t.posted $order limit $from, $perpage");
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

    //coclasses
    private function coInterface($method, $arg)
    {
        foreach ($this->coinstances as $coinstance) {
            if ($coinstance instanceof ipost) {
                $coinstance->$method($arg);
            }
        }
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
        $this->callevent('beforecontent', array(
            $post, &$result
        ));
    }

    public function aftercontent($post, &$result)
    {
        $this->callevent('aftercontent', array(
            $post, &$result
        ));
    }

    public function beforeexcerpt($post, &$result)
    {
        $this->callevent('beforeexcerpt', array(
            $post, &$result
        ));
    }

    public function afterexcerpt($post, &$result)
    {
        $this->callevent('afterexcerpt', array(
            $post, &$result
        ));
    }

    public function getSitemap($from, $count)
    {
        return $this->externalfunc(__class__, 'Getsitemap', array(
            $from,
            $count
        ));
    }

}

//View.php
namespace litepubl\post;

use litepubl\comments\Templates;
use litepubl\core\Context;
use litepubl\core\Str;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\MainView;
use litepubl\view\Theme;

class View extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
    public $post;
    public $context;
    private $prevPost;
    private $nextPost;
    private $themeInstance;

    protected function create()
    {
        parent::create();
        $this->table = 'posts';
    }

    public function setPost(Post $post)
    {
        $this->post = $post;
    }

    public function getView()
    {
        return $this;
    }

    public function __get($name)
    {
        if (method_exists($this, $get = 'get' . $name)) {
            $result = $this->$get();
        } else {
            switch ($name) {
                case 'catlinks':
                    $result = $this->get_taglinks('categories', false);
                    break;


                case 'taglinks':
                    $result = $this->get_taglinks('tags', false);
                    break;


                case 'excerptcatlinks':
                    $result = $this->get_taglinks('categories', true);
                    break;


                case 'excerpttaglinks':
                    $result = $this->get_taglinks('tags', true);
                    break;


                default:
                    if (isset($this->post->$name)) {
                        $result = $this->post->$name;
                    } else {
                        $result = parent::__get($name);
                    }
            }
        }

        return $result;
    }

    public function __set($name, $value)
    {
        if (parent::__set($name, $value)) {
            return true;
        }

        if (isset($this->post->$name)) {
            $this->post->$name = $value;
            return true;
        }

        return false;
    }

    public function __call($name, $args)
    {
        if (method_exists($this->post, $name)) {
            return call_user_func_array([$this->post, $name], $args);
        } else {
            return parent::__call($name, $args);
        }
    }

    public function getPrev()
    {
        if (!is_null($this->prevPost)) {
            return $this->prevPost;
        }

        $this->prevPost = false;
        if ($id = $this->db->findid("status = 'published' and posted < '$this->sqldate' order by posted desc")) {
            $this->prevPost = Post::i($id);
        }
        return $this->prevPost;
    }

    public function getNext()
    {
        if (!is_null($this->nextPost)) {
            return $this->nextPost;
        }

        $this->nextPost = false;
        if ($id = $this->db->findid("status = 'published' and posted > '$this->sqldate' order by posted asc")) {
            $this->nextPost = Post::i($id);
        }
        return $this->nextPost;
    }

    public function getTheme()
    {
        if ($this->themeInstance) {
            $this->themeInstance->setvar('post', $this);
            return $this->themeInstance;
        }

        $mainview = MainView::i();
        $this->themeInstance = $mainview->schema ? $mainview->schema->theme : Schema::getSchema($this)->theme;
        $this->themeInstance->setvar('post', $this);
        return $this->themeInstance;
    }

    public function parseTml($path)
    {
        $theme = $this->theme;
        return $theme->parse($theme->templates[$path]);
    }

    public function getExtra()
    {
        $theme = $this->theme;
        return $theme->parse($theme->extratml);
    }

    public function getBookmark()
    {
        return $this->theme->parse('<a href="$post.link" rel="bookmark" title="$lang.permalink $post.title">$post.title</a>');
    }

    public function getRsscomments()
    {
        return $this->getApp()->site->url . "/comments/$this->id.xml";
    }

    public function getIdimage()
    {
        if (!count($this->files)) {
            return false;
        }

        $files = $this->factory->files;
        foreach ($this->files as $id) {
            $item = $files->getitem($id);
            if ('image' == $item['media']) {
                return $id;
            }
        }

        return false;
    }

    public function getImage()
    {
        if ($id = $this->getidimage()) {
            return $this->factory->files->geturl($id);
        }

        return false;
    }

    public function getThumb()
    {
        if (count($this->files) == 0) {
            return false;
        }

        $files = $this->factory->files;
        foreach ($this->files as $id) {
            $item = $files->getitem($id);
            if ((int)$item['preview']) {
                return $files->geturl($item['preview']);
            }
        }

        return false;
    }

    public function getFirstImage()
    {
        if (count($this->files)) {
            return $this->factory->files->getfirstimage($this->files);
        }

        return '';
    }

    //template
    protected function get_taglinks($name, $excerpt)
    {
        $items = $this->__get($name);
        if (!count($items)) {
            return '';
        }

        $theme = $this->theme;
        $tmlpath = $excerpt ? 'content.excerpts.excerpt' : 'content.post';
        $tmlpath.= $name == 'tags' ? '.taglinks' : '.catlinks';
        $tmlitem = $theme->templates[$tmlpath . '.item'];

        $tags = Str::begin($name, 'tag') ? $this->factory->tags : $this->factory->categories;
        $tags->loaditems($items);

        $args = new Args();
        $list = array();

        foreach ($items as $id) {
            $item = $tags->getitem($id);
            $args->add($item);
            if (($item['icon'] == 0) || $this->getApp()->options->icondisabled) {
                $args->icon = '';
            } else {
                $files = $this->factory->files;
                if ($files->itemExists($item['icon'])) {
                    $args->icon = $files->geticon($item['icon']);
                } else {
                    $args->icon = '';
                }
            }
            $list[] = $theme->parseArg($tmlitem, $args);
        }

        $args->items = ' ' . implode($theme->templates[$tmlpath . '.divider'], $list);
        $result = $theme->parseArg($theme->templates[$tmlpath], $args);
        $this->factory->posts->callevent('ontags', array(
            $tags,
            $excerpt, &$result
        ));
        return $result;
    }

    public function getDate()
    {
        return Lang::date($this->posted, $this->theme->templates['content.post.date']);
    }

    public function getExcerptDate()
    {
        return Lang::date($this->posted, $this->theme->templates['content.excerpts.excerpt.date']);
    }

    public function getDay()
    {
        return date($this->posted, 'D');
    }

    public function getMonth()
    {
        return Lang::date($this->posted, 'M');
    }

    public function getYear()
    {
        return date($this->posted, 'Y');
    }

    public function getMoreLink()
    {
        if ($this->moretitle) {
            return $this->parsetml('content.excerpts.excerpt.morelink');
        }

        return '';
    }

    public function request(Context $context)
    {
        $app = $this->getApp();
        if ($this->status != 'published') {
            if (!$app->options->show_draft_post) {
                $context->response->status = 404;
                return;
            }

            $groupname = $app->options->group;
            if (($groupname == 'admin') || ($groupname == 'editor')) {
                return;
            }

            if ($this->author == $app->options->user) {
                return;
            }

            $context->response->status = 404;
            return;
        }

        $this->context = $context;
    }

    public function getPage()
    {
        return $this->context->request->page;
    }

    public function getTitle()
    {
        return $this->post->title;
    }

    public function getHead()
    {
        $result = $this->rawhead;
        MainView::i()->ltoptions['idpost'] = $this->id;
        $theme = $this->theme;
        $result.= $theme->templates['head.post'];
        if ($prev = $this->prev) {
            Theme::$vars['prev'] = $prev;
            $result.= $theme->templates['head.post.prev'];
        }

        if ($next = $this->next) {
            Theme::$vars['next'] = $next;
            $result.= $theme->templates['head.post.next'];
        }

        if ($this->hascomm) {
            Lang::i('comment');
            $result.= $theme->templates['head.post.rss'];
        }

        $result = $theme->parse($result);
        $this->factory->posts->callevent('onhead', array(
            $this, &$result
        ));

        return $result;
    }

    public function getAnhead()
    {
        $result = '';
        $this->factory->posts->callevent('onanhead', array(
            $this, &$result
        ));
        return $result;
    }

    public function getKeywords()
    {
        if ($result = $this->post->keywords) {
            return $result;
        } else {
            return $this->Gettagnames();
        }
    }

    public function getDescription()
    {
        return $this->post->description;
    }

    public function getIdSchema()
    {
        return $this->post->idschema;
    }

    public function setIdSchema($id)
    {
        if ($id != $this->idschema) {
            $this->post->idschema = $id;
            if ($this->id) {
                $this->post->db->setvalue($this->id, 'idschema', $id);
            }
        }
    }

    public function getFileList()
    {
        if (!count($this->files) || (($this->page > 1) && $this->getApp()->options->hidefilesonpage)) {
            return '';
        }

        $files = $this->factory->files;
        return $files->getFileList($this->files, false);
    }

    public function getExcerptFileList()
    {
        if (count($this->files) == 0) {
            return '';
        }

        $files = $this->factory->files;
        return $files->getfilelist($this->files, true);
    }

    public function getIndexTml()
    {
        $theme = $this->theme;
        if (!empty($theme->templates['index.post'])) {
            return $theme->templates['index.post'];
        }

        return false;
    }

    public function getCont()
    {
        return $this->parsetml('content.post');
    }

    public function getContExcerpt($tml_name)
    {
        Theme::$vars['post'] = $this;
        //no use self theme because post in other context
        $theme = Theme::i();
        $tml_key = $tml_name == 'excerpt' ? 'excerpt' : $tml_name . '.excerpt';
        return $theme->parse($theme->templates['content.excerpts.' . $tml_key]);
    }

    public function getRssLink()
    {
        if ($this->hascomm) {
            return $this->parsetml('content.post.rsslink');
        }
        return '';
    }

    public function onRssItem($item)
    {
    }

    public function getPrevNext()
    {
        $prev = '';
        $next = '';
        $theme = $this->theme;
        if ($prevpost = $this->prev) {
            Theme::$vars['prevpost'] = $prevpost;
            $prev = $theme->parse($theme->templates['content.post.prevnext.prev']);
        }
        if ($nextpost = $this->next) {
            Theme::$vars['nextpost'] = $nextpost;
            $next = $theme->parse($theme->templates['content.post.prevnext.next']);
        }

        if (($prev == '') && ($next == '')) {
            return '';
        }

        $result = strtr($theme->parse($theme->templates['content.post.prevnext']) , array(
            '$prev' => $prev,
            '$next' => $next
        ));
        unset(Theme::$vars['prevpost'], Theme::$vars['nextpost']);
        return $result;
    }

    public function getCommentsLink()
    {
        $tml = sprintf('<a href="%s%s#comments">%%s</a>', $this->getApp()->site->url, $this->getlastcommenturl());
        if (($this->comstatus == 'closed') || !$this->getApp()->options->commentspool) {
            if (($this->commentscount == 0) && (($this->comstatus == 'closed'))) {
                return '';
            }

            return sprintf($tml, $this->getcmtcount());
        }

        //inject php code
        return sprintf('<?php echo litepubl\comments\Pool::i()->getLink(%d, \'%s\'); ?>', $this->id, $tml);
    }

    public function getCmtCount()
    {
        $l = Lang::i()->ini['comment'];
        switch ($this->commentscount) {
            case 0:
                return $l[0];

            case 1:
                return $l[1];

            default:
                return sprintf($l[2], $this->commentscount);
        }
    }

    public function getTemplateComments()
    {
        $result = '';
        $countpages = $this->countpages;
        if ($countpages > 1) {
            $result.= $this->theme->getpages($this->url, $this->page, $countpages);
        }

        if (($this->commentscount > 0) || ($this->comstatus != 'closed') || ($this->pingbackscount > 0)) {
            if (($countpages > 1) && ($this->commentpages < $this->page)) {
                $result.= $this->getCommentsLink();
            } else {
                $result.= Templates::i()->getcomments($this);
            }
        }

        return $result;
    }

    public function getHascomm()
    {
        return ($this->comstatus != 'closed') && ((int)$this->commentscount > 0);
    }

    public function getExcerptContent()
    {
        $posts = $this->factory->posts;
        if ($this->revision < $posts->revision) {
            $this->updateRevision($posts->revision);
        }
        $result = $this->excerpt;
        $posts->beforeexcerpt($this, $result);
        $result = $this->replacemore($result, true);
        if ($this->getApp()->options->parsepost) {
            $result = $this->theme->parse($result);
        }
        $posts->afterexcerpt($this, $result);
        return $result;
    }

    public function replaceMore($content, $excerpt)
    {
        $more = $this->parsetml($excerpt ? 'content.excerpts.excerpt.morelink' : 'content.post.more');
        $tag = '<!--more-->';
        if ($i = strpos($content, $tag)) {
            return str_replace($tag, $more, $content);
        } else {
            return $excerpt ? $content : $more . $content;
        }
    }

    protected function getTeaser()
    {
        $content = $this->filtered;
        $tag = '<!--more-->';
        if ($i = strpos($content, $tag)) {
            $content = substr($content, $i + strlen($tag));
            if (!Str::begin($content, '<p>')) $content = '<p>' . $content;
            return $content;
        }
        return '';
    }

    protected function getContentPage($page)
    {
        $result = '';
        if ($page == 1) {
            $result.= $this->filtered;
            $result = $this->replacemore($result, false);
        } elseif ($s = $this->getpage($page - 2)) {
            $result.= $s;
        } elseif ($page <= $this->commentpages) {
        } else {
            $result.= Lang::i()->notfound;
        }

        return $result;
    }

    public function getContent()
    {
        $result = '';
        $posts = $this->factory->posts;
        $posts->beforecontent($this, $result);
        if ($this->revision < $posts->revision) {
            $this->updateRevision($posts->revision);
        }

        $result.= $this->getContentPage($this->page);
        if ($this->getApp()->options->parsepost) {
            $result = $this->theme->parse($result);
        }
        $posts->aftercontent($this, $result);
        return $result;
    }

    //author
    protected function getAuthorname()
    {
        return $this->getusername($this->author, false);
    }

    protected function getAuthorLink()
    {
        return $this->getusername($this->author, true);
    }

    protected function getUserName($id, $link)
    {
        if ($id <= 1) {
            if ($link) {
                return sprintf('<a href="%s/" rel="author" title="%2$s">%2$s</a>', $this->getApp()->site->url, $this->getApp()->site->author);
            } else {
                return $this->getApp()->site->author;
            }
        } else {
            $users = $this->factory->users;
            if (!$users->itemExists($id)) {
                return '';
            }

            $item = $users->getitem($id);
            if (!$link || ($item['website'] == '')) {
                return $item['name'];
            }

            return sprintf('<a href="%s/users.htm%sid=%s">%s</a>', $this->getApp()->site->url, $this->getApp()->site->q, $id, $item['name']);
        }
    }

    public function getAuthorPage()
    {
        $id = $this->author;
        if ($id <= 1) {
            return sprintf('<a href="%s/" rel="author" title="%2$s">%2$s</a>', $this->getApp()->site->url, $this->getApp()->site->author);
        } else {
            $pages = $this->factory->userpages;
            if (!$pages->itemExists($id)) {
                return '';
            }

            $pages->id = $id;
            if ($pages->url == '') {
                return '';
            }

            return sprintf('<a href="%s%s" title="%3$s" rel="author"><%3$s</a>', $this->getApp()->site->url, $pages->url, $pages->name);
        }
    }

}
{

}

