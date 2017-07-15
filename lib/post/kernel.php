<?php
//Announce.php
namespace litepubl\post;

use litepubl\view\Lang;
use litepubl\view\Schema;
use litepubl\view\Vars;

/**
 * Post announces
 *
 * @property-write callable $before
 * @property-write callable $after
 * @property-write callable $onHead
 * @method         array before(array $params)
 * @method         array after(array $params)
 * @method         array onHead(array $params)
 */

class Announce extends \litepubl\core\Events
{
    use \litepubl\core\PoolStorageTrait;

    protected function create()
    {
        parent::create();
        $this->basename = 'announce';
        $this->addEvents('before', 'after', 'onhead');
    }

    public function getHead(array $items): string
    {
        $result = '';
        if (count($items)) {
            Posts::i()->loadItems($items);

            foreach ($items as $id) {
                $post = Post::i($id);
                $result.= $post->rawhead;
            }
        }

        $r = $this->onHead(['content' => $result, 'items' => $items]);
        return $r['content'];
    }

    public function getPosts(array $items, Schema $schema): string
    {
        $r = $this->before(['content' => '', 'items' => $items, 'schema' => $schema]);
        $result = $r['content'];
            $theme = $schema->theme;
        $items = $r['items'];
        if (count($items)) {
            Posts::i()->loadItems($items);

            $vars = new Vars();
            $vars->lang = Lang::i('default');

            foreach ($items as $id) {
                $post = Post::i($id);
                $view = $post->view;
                $vars->post = $view;
                $view->setTheme($theme);
                $result.= $view->getAnnounce($schema->postannounce);

                // has $author.* tags in tml
                if (isset($vars->author)) {
                    unset($vars->author);
                }
            }
        }

        if ($tmlContainer = $theme->templates['content.excerpts' . ($schema->postannounce == 'excerpt' ? '' : '.' . $schema->postannounce) ]) {
            $result = str_replace('$excerpt', $result, $theme->parse($tmlContainer));
        }

        $r = $this->after(['content' => $result, 'items' => $items, 'schema' => $schema]);
        return $r['content'];
    }

    public function getNavi(array $items, Schema $schema, string $url, int $count): string
    {
        $result = $this->getPosts($items, $schema);
        $result .= $this->getPages($schema, $url, $count);
        return $result;
    }

    public function getPages(Schema $schema, string $url, int $count): string
    {
        $app = $this->getApp();
        if ($schema->perpage) {
            $perpage = $schema->perpage;
        } else {
            $perpage = $app->options->perpage;
        }
        
        return $schema->theme->getPages($url, $app->context->request->page, ceil($count / $perpage));
    }

    //used in plugins such as singlecat
    public function getLinks(string $where, string $tml): string
    {
        $db = $this->getApp()->db;
        $t = $db->posts;
        $items = $db->res2assoc(
            $db->query(
                "select $t.id, $t.title, $db->urlmap.url as url  from $t, $db->urlmap
    where $t.status = 'published' and $where and $db->urlmap.id  = $t.idurl"
            )
        );

        if (!count($items)) {
            return '';
        }

        $result = '';
        $args = new Args();
        $theme = Theme::i();
        foreach ($items as $item) {
            $args->add($item);
            $result.= $theme->parseArg($tml, $args);
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

    public function getFileView()
    {
        return FileView::i();
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

use litepubl\core\Event;
use litepubl\core\Str;
use litepubl\view\Filter;

/**
 * Manage uploaded files
 *
 * @property-read  FilesItems $itemsPosts
 * @property-write callable $changed
 * @property-write callable $edited
 * @method         array changed(array $params)
 * @method         array edited(array $params)
 */

class Files extends \litepubl\core\Items
{

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->basename = 'files';
        $this->table = 'files';
        $this->addEvents('changed', 'edited');
    }

    public function getItemsPosts(): FilesItems
    {
        return FilesItems::i();
    }

    public function preload(array $items)
    {
        $items = array_diff($items, array_keys($this->items));
        if (count($items)) {
            $this->select(sprintf('(id in (%1$s)) or (parent in (%1$s))', implode(',', $items)), '');
        }
    }

    public function getUrl(int $id): string
    {
        $item = $this->getItem($id);
        return $this->getApp()->site->files . '/files/' . $item['filename'];
    }

    public function getLink(int $id): string
    {
        $item = $this->getItem($id);
        return sprintf('<a href="%1$s/files/%2$s" title="%3$s">%4$s</a>', $this->getApp()->site->files, $item['filename'], $item['title'], $item['description']);
    }

    public function getHash(string $filename): string
    {
        return trim(base64_encode(md5_file($filename, true)), '=');
    }

    public function addItem(array $item): int
    {
        $realfile = $this->getApp()->paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
        $item['author'] = $this->getApp()->options->user;
        $item['posted'] = Str::sqlDate();
        $item['hash'] = $this->gethash($realfile);
        $item['size'] = filesize($realfile);

        //fix empty props
        foreach (['mime', 'title', 'description', 'keywords'] as $prop) {
            if (!isset($item[$prop])) {
                $item[$prop] = '';
            }
        }

        return $this->insert($item);
    }

    public function insert(array $item): int
    {
        $item = $this->escape($item);
        $id = $this->db->add($item);
        $this->items[$id] = $item;
        $this->changed([]);
        $this->added(['id' => $id]);
        return $id;
    }

    public function escape(array $item): array
    {
        foreach (['title', 'description', 'keywords'] as $name) {
            $item[$name] = Filter::escape(Filter::unescape($item[$name]));
        }
        return $item;
    }

    public function edit(int $id, string $title, string $description, string $keywords)
    {
        $item = $this->getItem($id);
        if (($item['title'] == $title) && ($item['description'] == $description) && ($item['keywords'] == $keywords)) {
            return false;
        }

        $item['title'] = $title;
        $item['description'] = $description;
        $item['keywords'] = $keywords;
        $item = $this->escape($item);
        $this->items[$id] = $item;
        $this->db->updateassoc($item);
        $this->changed([]);
        $this->edited(['id' => $id]);
        return true;
    }

    public function delete($id)
    {
        if (!$this->itemExists($id)) {
            return false;
        }

        $list = $this->itemsposts->getposts($id);
        $this->itemsPosts->deleteItem($id);
        $this->itemsPosts->updatePosts($list, 'files');

        $item = $this->getItem($id);
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
        $this->changed([]);
        $this->deleted(['id' => $id]);
        return true;
    }

    public function setContent(int $id, string $content): bool
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
                $item['id'] = $id;
                $this->db->updateassoc($item);
        }

        return true;
    }

    public function exists(string $filename): bool
    {
        return $this->indexOf('filename', $filename);
    }

    public function postEdited(Event $event)
    {
        $post = Post::i($event->id);
        $this->itemsPosts->setItems($post->id, $post->files);
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

//FileView.php
namespace litepubl\post;

use litepubl\core\Str;
use litepubl\view\Args;
use litepubl\view\Theme;
use litepubl\view\Vars;

/**
 * View file list
 *
 * @property-write callable $onGetFilelist
 * @property-write callable $onlist
 * @method         array onGetFilelist(array $params)
 * @method         array onlist(array $params)
 */

class FileView extends \litepubl\core\Events
{
    protected $templates;

    protected function create()
    {
        parent::create();
        $this->basename = 'fileview';
        $this->addEvents('ongetfilelist', 'onlist');
        $this->templates = [];
    }

    public function getFiles(): Files
    {
        return Files::i();
    }

    public function getFileList(array $list, bool $excerpt, Theme $theme): string
    {
        $r = $this->onGetFilelist(['list' => $list, 'excerpt' => $excerpt, 'result' => false]);
        if ($r['result']) {
            return $r['result'];
        }

        if (!count($list)) {
            return '';
        }

        $tml = $excerpt ? $this->getTml($theme, 'content.excerpts.excerpt.filelist') : $this->getTml($theme, 'content.post.filelist');
        return $this->getList($list, $tml);
    }

    public function getTml(Theme $theme, string $basekey): array
    {
        if (isset($this->templates[$theme->name][$basekey])) {
            return $this->templates[$theme->name][$basekey];
        }

        $result = [
            'container' => $theme->templates[$basekey],
        ];

        $key = $basekey . '.';
        foreach ($theme->templates as $k => $v) {
            if (Str::begin($k, $key)) {
                $result[substr($k, strlen($key)) ] = $v;
            }
        }

        if (!isset($this->templates[$theme->name])) {
                $this->templates[$theme->name] = [];
        }

        $this->templates[$theme->name][$basekey] = $result;
        return $result;
    }

    public function getList(array $list, array $tml): string
    {
        if (!count($list)) {
            return '';
        }

        $this->onList(['list' => $list]);
        $result = '';
        $files = $this->getFiles();
        $files->preLoad($list);

        //sort by media type
        $items = [];
        foreach ($list as $id) {
            if (!isset($files->items[$id])) {
                continue;
            }

            $item = $files->items[$id];
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
        $vars = new Vars();
        $vars->preview = $preview;
        $midle = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
        $vars->midle = $midle;

        $index = 0;
        foreach ($items as $type => $subitems) {
            $args->subcount = count($subitems);
            $sublist = '';
            foreach ($subitems as $typeindex => $id) {
                $item = $files->items[$id];
                $args->add($item);
                $args->link = $url . $item['filename'];
                $args->id = $id;
                $args->typeindex = $typeindex;
                $args->index = $index++;
                $args->preview = '';
                $preview->exchangeArray([]);

                if ($idmidle = (int)$item['midle']) {
                    $midle->exchangeArray($files->getItem($idmidle));
                    $midle->link = $url . $midle->filename;
                    $midle->json = $this->getJson($idmidle);
                } else {
                    $midle->exchangeArray([]);
                    $midle->link = '';
                    $midle->json = '';
                }

                if ((int)$item['preview']) {
                    $preview->exchangeArray($files->getItem($item['preview']));
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

                $args->json = $this->getJson($id);
                $sublist.= $theme->parseArg($tml[$type], $args);
            }

            $args->__set($type, $sublist);
            $result.= $theme->parseArg($tml[$type . 's'], $args);
        }

        $args->files = $result;
        return $theme->parseArg($tml['container'], $args);
    }

    public function getFirstImage(array $items): string
    {
        $files = $this->getFiles();
        foreach ($items as $id) {
            $item = $files->getItem($id);
            if (('image' == $item['media']) && ($idpreview = (int)$item['preview'])) {
                $baseurl = $this->getApp()->site->files . '/files/';
                $args = new Args();
                $args->add($item);
                $args->link = $baseurl . $item['filename'];
                $args->json = $this->getJson($id);

                $preview = new \ArrayObject($files->getItem($idpreview), \ArrayObject::ARRAY_AS_PROPS);
                $preview->link = $baseurl . $preview->filename;

                $midle = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
                if ($idmidle = (int)$item['midle']) {
                    $midle->exchangeArray($files->getItem($idmidle));
                    $midle->link = $baseurl . $midle->filename;
                    $midle->json = $this->getJson($idmidle);
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

    public function getJson(int $id): string
    {
        $item = $this->getFiles()->getItem($id);
        return Str::jsonAttr(
            [
            'id' => $id,
            'link' => $this->getApp()->site->files . '/files/' . $item['filename'],
            'width' => $item['width'],
            'height' => $item['height'],
            'size' => $item['size'],
            'midle' => $item['midle'],
            'preview' => $item['preview'],
            ]
        );
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
            return $this->setId($value);
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
            static ::$instances['postmeta'] = [];
        }

        $instances = & static ::$instances['postmeta'];
        $db = static::getAppInstance()->db;
        $db->table = 'postsmeta';
        $res = $db->select(sprintf('id in (%s)', implode(',', $items)));
        while ($row = $db->fetchassoc($res)) {
            $id = (int)$row['id'];
            if (!isset($instances[$id])) {
                $instances[$id] = new static();
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

/**
 * This is the post base class
 *
 * @property      int $idschema
 * @property      int $idurl
 * @property      int $parent
 * @property      int $author
 * @property      int $revision
 * @property      int $idperm
 * @property      string $class
 * @property      int $posted timestamp
 * @property      string $title
 * @property      string $title2
 * @property      string $filtered
 * @property      string $excerpt
 * @property      string $keywords
 * @property      string $description
 * @property      string $rawhead
 * @property      string $moretitle
 * @property      array $categories
 * @property      array $tags
 * @property      array $files
 * @property      string $status enum
 * @property      string $comstatus enum
 * @property      int $pingenabled bool
 * @property      string $password
 * @property      int $commentscount
 * @property      int $pingbackscount
 * @property      int $pagescount
 * @property      string $url
 * @property      int $created timestamp
 * @property      int $modified timestamp
 * @property      array $pages
 * @property      string $rawcontent
 * @property-read string $instanceName
 * @property-read string $childTable
 * @property-read Factory $factory
 * @property-read View $view
 * @property-read Meta $meta
 * @property      string $link absolute url
 * @property-read string isoDate
 * @property      string pubDate
 * @property-read string sqlDate
 * @property      string $tagNames tags title separated by ,
 * @property      string $catNames categories title separated by ,
 * @property-read string $category first category title
 * @property-read int $idCat first category ID
 * @property-read bool $hasPages true if has content or comments pages
 * @property-read int $pagesCount index from 1
 * @property-read int $countPages maximum of content or comments pages
 * @property-read int commentPages
 * @property-read string lastCommentUrl
 * @property-read string schemaLink to generate new url
 */

class Post extends \litepubl\core\Item
{
    use \litepubl\core\Callbacks;

    protected $childTable;
    protected $rawTable;
    protected $pagesTable;
    protected $childData;
    protected $cacheData;
    protected $rawData;
    protected $factory;
    private $metaInstance;

    public static function i($id = 0)
    {
        if ($id = (int) $id) {
            if (isset(static::$instances['post'][$id])) {
                $result = static::$instances['post'][$id];
            } elseif ($result = static::loadPost($id)) {
                // nothing: set $instances in afterLoad method
            } else {
                $result = null;
            }
        } else {
            $result = parent::itemInstance(get_called_class(), $id);
        }
        
        return $result;
    }

    public static function loadPost(int $id)
    {
        if ($a = static::loadAssoc($id)) {
            $self = static::newPost($a['class']);
            $self->setAssoc($a);
            
            if ($table = $self->getChildTable()) {
                $items = static::selectChildItems(
                    $table,
                    [
                    $id
                    ]
                );
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
        $db = static::getAppInstance()->db;
        $table = static::getChildTable();
        if ($table) {
            return $db->selectAssoc(
                "select $db->posts.*, $db->prefix$table.*, $db->urlmap.url as url 
 from $db->posts, $db->prefix$table, $db->urlmap
    where $db->posts.id = $id and $db->prefix$table.id = $id and $db->urlmap.id  = $db->posts.idurl limit 1"
            );
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
        if (! $table || ! count($items)) {
            return [];
        }
        
        $db = static::getAppInstance()->db;
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
        $this->childTable = static::getChildTable();
        
        $options = $this->getApp()->options;
        $this->data = [
            'id' => 0,
            'idschema' => 1,
            'idurl' => 0,
            'parent' => 0,
            'author' => 0,
            'revision' => 0,
            'idperm' => 0,
            'class' => str_replace('\\', '-', get_class($this)),
            'posted' => static::ZERODATE,
            'title' => '',
            'title2' => '',
            'filtered' => '',
            'excerpt' => '',
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
            'pagescount' => 0
        ];
        
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
            'pages' => []
        ];
        
        $this->factory = $this->getfactory();
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
            $result = (int) $this->data['id'];
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
        return parent::__isset($name) || array_key_exists($name, $this->cacheData) || array_key_exists($name, $this->childData) || array_key_exists($name, $this->rawData) || method_exists($this, 'getCache' . $name);
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
    }

    protected function saveToDB()
    {
        if (! $this->id) {
            return $this->add();
        }
        
        $this->db->updateAssoc($this->data);
        
        $this->modified = time();
        $this->getDB($this->rawTable)->setValues($this->id, $this->rawData);
        
        if ($this->childTable) {
            $this->getDB($this->childTable)->setValues($this->id, $this->childData);
        }
    }

    public function add(): int
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
        $this->triggerOnId();
        return $id;
    }

    protected function prepareRawData()
    {
        if (! $this->created) {
            $this->created = time();
        }
        
        if (! $this->modified) {
            $this->modified = time();
        }
        
        if (! isset($this->rawData['rawcontent'])) {
            $this->rawData['rawcontent'] = '';
        }
        
        return $this->rawData;
    }

    public function createUrl()
    {
        return $this->getApp()->router->add($this->url, get_class($this), (int) $this->id);
    }

    public function onId(callable $callback)
    {
        $this->addCallback('onid', $callback);
    }

    protected function triggerOnId()
    {
        $this->triggerCallback('onid');
        $this->clearCallbacks('onid');

        if (isset($this->metaInstance)) {
            $this->metaInstance->id = $this->id;
            $this->metaInstance->save();
        }
    }

    public function free()
    {
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
        if (! isset($this->metaInstance)) {
            $this->metaInstance = $this->factory->getmeta($this->id);
        }
        
        return $this->metaInstance;
    }
    
    // props
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
    
    // cache props
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
        return $this->data['posted'] == static::ZERODATE ? 0 : strtotime($this->data['posted']);
    }

    public function setPosted($timestamp)
    {
        $this->data['posted'] = Str::sqlDate($timestamp);
        $this->cacheData['posted'] = $timestamp;
    }

    protected function getCacheModified()
    {
        return ! isset($this->rawData['modified']) || $this->rawData['modified'] == static::ZERODATE ? 0 : strtotime($this->rawData['modified']);
    }

    public function setModified($timestamp)
    {
        $this->rawData['modified'] = Str::sqlDate($timestamp);
        $this->cacheData['modified'] = $timestamp;
    }

    protected function getCacheCreated()
    {
        return ! isset($this->rawData['created']) || $this->rawData['created'] == static::ZERODATE ? 0 : strtotime($this->rawData['created']);
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

    public function setTagNames(string $names)
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
        
        if (! count($catItems)) {
            $defaultid = $categories->defaultid;
            if ($defaultid > 0) {
                $catItems[] = $defaultid;
            }
        }
        
        $this->categories = $catItems;
    }

    public function getCategory(): string
    {
        if ($idcat = $this->getidcat()) {
            return $this->factory->categories->getName($idcat);
        }
        
        return '';
    }

    public function getIdCat(): int
    {
        if (($cats = $this->categories) && count($cats)) {
            return $cats[0];
        }
        
        return 0;
    }

    public function checkRevision()
    {
        $this->updateRevision((int) $this->factory->posts->revision);
    }

    public function updateRevision($value)
    {
        if ($value != $this->revision) {
            $this->updateFiltered();
            $posts = $this->factory->posts;
            $this->revision = (int) $posts->revision;
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
        
        if (! $this->id) {
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
            $this->getdb($this->pagesTable)->insert(
                [
                'id' => $this->id,
                'page' => $this->data['pagescount'] - 1,
                'content' => $s
                ]
            );
        }
    }

    public function deletePages()
    {
        $this->cacheData['pages'] = [];
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
                $db->insert(
                    [
                    'id' => $this->id,
                    'page' => $index,
                    'content' => $content
                    ]
                );
            }
        }
    }

    public function getHasPages(): bool
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
        if (! $options->commentpages || ($this->commentscount <= $options->commentsperpage)) {
            return 1;
        }
        
        return ceil($this->commentscount / $options->commentsperpage);
    }

    public function getLastCommentUrl()
    {
        $c = $this->commentpages;
        $url = $this->url;
        if (($c > 1) && ! $this->getApp()->options->comments_invert_order) {
            $url = rtrim($url, '/') . "/page/$c/";
        }
        
        return $url;
    }

    public function clearCache()
    {
        $this->getApp()->cache->clearUrl($this->url);
    }

    public function getSchemalink(): string
    {
        return 'post';
    }

    public function setContent($s)
    {
        if (! is_string($s)) {
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

/**
 * Main class to manage posts
 *
 * @property       int $archivescount
 * @property       int $revision
 * @property       bool $syncmeta
 * @property-write callable $edited
 * @property-write callable $changed
 * @property-write callable $singleCron
 * @property-write callable $onSelect
 * @method         array edited(array $params)
 * @method         array changed(array $params)
 * @method         array singleCron(array $params)
 * @method         array onselect(array $params)
 */

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
        $this->addEvents('edited', 'changed', 'singlecron', 'onselect');
         $this->data['archivescount'] = 0;
        $this->data['revision'] = 0;
        $this->data['syncmeta'] = false;
        $this->addmap('itemcoclasses', []);
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
            Post::$instances['post'] = [];
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
            return [];
        }

        $result = [];
        $fileitems = [];
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

        $this->onSelect(['items' => $result]);
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
            return [];
        }

        $subclasses = [];
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
        $this->added(['id' => $id]);
        $this->changed([]);
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
        $this->unlock();
        $this->edited(['id' => $post->id]);
        $this->changed([]);

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
        $this->unlock();
        $this->deleted(['id' => $id]);
        $this->changed([]);
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

    public function doSingleCron($id)
    {
        $this->PublishFuture();
        Theme::$vars['post'] = Post::i($id);
        $this->singleCron(['id' => $id]);
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

    public function stripDrafts(array $items): array
    {
        if (count($items) == 0) {
            return [];
        }

        $list = implode(',', $items);
        $t = $this->thistable;
        return $this->db->idSelect("$t.status = 'published' and $t.id in ($list)");
    }

    public function addRevision(): int
    {
        $this->data['revision']++;
        $this->save();
        $this->getApp()->cache->clear();
        return $this->data['revision'];
    }

    public function getSitemap($from, $count)
    {
        return $this->externalfunc(
            __class__,
            'Getsitemap',
            [
            $from,
            $count
            ]
        );
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
use litepubl\view\Schema;
use litepubl\view\Theme;

/**
 * Post view
 *
 * @property-write callable $beforeContent
 * @property-write callable $afterContent
 * @property-write callable $beforeExcerpt
 * @property-write callable $afterExcerpt
 * @property-write callable $onHead
 * @property-write callable $onTags
 * @method         array beforeContent(array $params)
 * @method         array afterContent(array $params)
 * @method         array beforeExcerpt(array $params)
 * @method         array afterExcerpt(array $params)
 * @method         array onHead(array $params)
 * @method         array onTags(array $params)
 */

class View extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
    use \litepubl\core\PoolStorageTrait;

    public $post;
    public $context;
    private $prevPost;
    private $nextPost;
    private $themeInstance;

    protected function create()
    {
        parent::create();
        $this->basename = 'postview';
        $this->addEvents('beforecontent', 'aftercontent', 'beforeexcerpt', 'afterexcerpt', 'onhead', 'ontags');
        $this->table = 'posts';
    }

    public function setPost(Post $post)
    {
        $this->post = $post;
        $this->themeInstance = null;
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

    public function getTheme(): Theme
    {
        if (!$this->themeInstance) {
            $this->themeInstance = $this->post ? $this->schema->theme : Theme::context();
        }

        $this->themeInstance->setvar('post', $this);
        return $this->themeInstance;
    }

    public function setTheme(Theme $theme)
    {
        $this->themeInstance = $theme;
    }

    public function parseTml(string $path): string
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
        return $this->theme->parse($this->theme->templates['content.post.bookmark']);
    }

    public function getRssComments(): string
    {
        return $this->getApp()->site->url . "/comments/$this->id.xml";
    }

    public function getIdImage(): int
    {
        if (!count($this->files)) {
            return 0;
        }

        $files = $this->factory->files;
        foreach ($this->files as $id) {
            $item = $files->getItem($id);
            if ('image' == $item['media']) {
                return $id;
            }
        }

        return 0;
    }

    public function getImage(): string
    {
        if ($id = $this->getIdImage()) {
            return $this->factory->files->getUrl($id);
        }

        return '';
    }

    public function getThumb(): string
    {
        if (!count($this->files)) {
            return '';
        }

        $files = $this->factory->files;
        foreach ($this->files as $id) {
            $item = $files->getItem($id);
            if ((int)$item['preview']) {
                return $files->getUrl($item['preview']);
            }
        }

        return '';
    }

    public function getFirstImage(): string
    {
        $items = $this->files;
        if (count($items)) {
            return $this->factory->fileView->getFirstImage($items);
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
        $list = [];
        foreach ($items as $id) {
            if ($id && ($item = $tags->getItem($id))) {
                $args->add($item);
                $list[] = $theme->parseArg($tmlitem, $args);
            }
        }

        $args->items = ' ' . implode($theme->templates[$tmlpath . '.divider'], $list);
        $result = $theme->parseArg($theme->templates[$tmlpath], $args);
        $r = $this->onTags(['tags' => $tags, 'excerpt' => $excerpt, 'content' => $result]);
        return $r['content'];
    }

    public function getDate(): string
    {
        return Lang::date($this->posted, $this->theme->templates['content.post.date']);
    }

    public function getExcerptDate(): string
    {
        return Lang::date($this->posted, $this->theme->templates['content.excerpts.excerpt.date']);
    }

    public function getDay(): string
    {
        return date($this->posted, 'D');
    }

    public function getMonth(): string
    {
        return Lang::date($this->posted, 'M');
    }

    public function getYear(): string
    {
        return date($this->posted, 'Y');
    }

    public function getMoreLink(): string
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

    public function getPage(): int
    {
        return $this->context->request->page;
    }

    public function getTitle(): string
    {
        return $this->post->title;
    }

    public function getHead(): string
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
        $r = $this->onHead(['post' => $this->post, 'content' => $result]);
        return $r['content'];
    }

    public function getKeywords(): string
    {
        if ($result = $this->post->keywords) {
            return $result;
        } else {
            return $this->Gettagnames();
        }
    }

    public function getDescription(): string
    {
        return $this->post->description;
    }

    public function getIdSchema(): int
    {
        return $this->post->idschema;
    }

    public function setIdSchema(int $id)
    {
        if ($id != $this->idschema) {
            $this->post->idschema = $id;
            if ($this->id) {
                $this->post->db->setvalue($this->id, 'idschema', $id);
            }
        }
    }

    public function getSchema(): Schema
    {
        return Schema::getSchema($this);
    }

    //to override schema in post, id schema not changed
    public function getFileList(): string
    {
        $items = $this->files;
        if (!count($items) || (($this->page > 1) && $this->getApp()->options->hidefilesonpage)) {
            return '';
        }

        $fileView = $this->factory->fileView;
        return $fileView->getFileList($items, false, $this->theme);
    }

    public function getExcerptFileList(): string
    {
        $items = $this->files;
        if (count($items) == 0) {
            return '';
        }

        $fileView = $this->factory->fileView;
        return $fileView->getFileList($items, true, $this->theme);
    }

    public function getIndexTml()
    {
        $theme = $this->theme;
        if (!empty($theme->templates['index.post'])) {
            return $theme->templates['index.post'];
        }

        return false;
    }

    public function getCont(): string
    {
        return $this->parsetml('content.post');
    }

    public function getRssLink(): string
    {
        if ($this->hascomm) {
            return $this->parseTml('content.post.rsslink');
        }
        return '';
    }

    public function onRssItem($item)
    {
    }

    public function getRss(): string
    {
        $this->getApp()->getLogManager()->trace('get rss deprecated post property');
        return $this->post->excerpt;
    }

    public function getPrevNext(): string
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

        $result = strtr(
            $theme->parse($theme->templates['content.post.prevnext']),
            [
            '$prev' => $prev,
            '$next' => $next
            ]
        );
        unset(Theme::$vars['prevpost'], Theme::$vars['nextpost']);
        return $result;
    }

    public function getCommentsLink(): string
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

    public function getCmtCount(): string
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

    public function getTemplateComments(): string
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

    public function getHasComm(): bool
    {
        return ($this->comstatus != 'closed') && ((int)$this->commentscount > 0);
    }

    public function getAnnounce(string $announceType): string
    {
            $tmlKey = 'content.excerpts.' . ($announceType == 'excerpt' ? 'excerpt' : $announceType . '.excerpt');
        return $this->parseTml($tmlKey);
    }

    public function getExcerptContent(): string
    {
        $this->post->checkRevision();
        $r = $this->beforeExcerpt(['post' => $this->post, 'content' => $this->excerpt]);
        $result = $this->replaceMore($r['content'], true);
        if ($this->getApp()->options->parsepost) {
            $result = $this->theme->parse($result);
        }

        $r = $this->afterExcerpt(['post' => $this->post, 'content' => $result]);
        return $r['content'];
    }

    public function replaceMore(string $content, string $excerpt): string
    {
        $more = $this->parseTml($excerpt ? 'content.excerpts.excerpt.morelink' : 'content.post.more');
        $tag = '<!--more-->';
        if (strpos($content, $tag)) {
            return str_replace($tag, $more, $content);
        } else {
            return $excerpt ? $content : $more . $content;
        }
    }

    protected function getTeaser(): string
    {
        $content = $this->filtered;
        $tag = '<!--more-->';
        if ($i = strpos($content, $tag)) {
            $content = substr($content, $i + strlen($tag));
            if (!Str::begin($content, '<p>')) {
                $content = '<p>' . $content;
            }
            return $content;
        }
        return '';
    }

    protected function getContentPage(int $page): string
    {
        $result = '';
        if ($page == 1) {
            $result.= $this->filtered;
            $result = $this->replaceMore($result, false);
        } elseif ($s = $this->post->getPage($page - 2)) {
            $result.= $s;
        } elseif ($page <= $this->commentpages) {
        } else {
            $result.= Lang::i()->notfound;
        }

        return $result;
    }

    public function getContent(): string
    {
        $this->post->checkRevision();
        $r = $this->beforeContent(['post' => $this->post, 'content' => '']);
        $result = $r['content'];
        $result.= $this->getContentPage($this->page);

        if ($this->getApp()->options->parsepost) {
            $result = $this->theme->parse($result);
        }

        $r = $this->afterContent(['post' => $this->post, 'content' => $result]);
        return $r['content'];
    }

    //author
    protected function getAuthorName(): string
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
