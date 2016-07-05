<?php
//Cats.php
namespace litepubl\tag;

use litepubl\widget\Cache;
use litepubl\widget\Cats as CatsWidget;

/**
 * This is the categories class
 *
 * @property int $defaultid
 */

class Cats extends Common
{
    protected function create()
    {
        parent::create();
        $this->table = 'categories';
        $this->contents->table = 'catscontent';
        $this->itemsposts->table = $this->table . 'items';
        $this->basename = 'categories';
        $this->data['defaultid'] = 0;
    }

    public function setDefaultid($id)
    {
        if (($id != $this->defaultid) && $this->itemExists($id)) {
            $this->data['defaultid'] = $id;
            $this->save();
        }
    }

    public function save()
    {
        parent::save();
        if (!$this->locked) {
            Cache::i()->removeWidget(CatsWidget::i());
        }
    }
}

//Common.php
namespace litepubl\tag;

use litepubl\core\Arr;
use litepubl\core\ItemsPosts;
use litepubl\utils\LinkGenerator;
use litepubl\view\Filter;
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
        $res = $db->query(
            "select $t.*, $u.url from $t, $u
    where $where $u.id = $t.idurl $limit"
        );

        return $this->res2items($res);
    }

    public function getUrl(int $id): string
    {
        $item = $this->getItem($id);
        return $item['url'];
    }

    public function getName(int $id): string
    {
        $item = $this->getItem($id);
        return $item['title'];
    }

    public function postEdited(int $idpost)
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

    public function postDeleted(int $idpost)
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
        $list = $db->res2assoc(
            $db->query(
                "select $itemstable.$itemprop as id, count($itemstable.$itemprop)as itemscount from $itemstable, $poststable
    where $itemstable.$itemprop in ($items)  and $itemstable.$postprop = $poststable.id and $poststable.status = 'published' group by $itemstable.$itemprop"
            )
        );

        $db->table = $this->table;
        foreach ($list as $item) {
            $db->setValue($item['id'], 'itemscount', $item['itemscount']);
        }
    }

    public function getUrlType(): string
    {
        return 'normal';
    }

    public function add(int $parent, string $title): int
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

        $url = LinkGenerator::i()->createUrl($title, $this->PermalinkIndex, true);
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
        $idurl = $this->getApp()->router->add($url, get_class($this), $id, $this->urltype);
        $this->setValue($id, 'idurl', $idurl);
        $this->items[$id]['url'] = $url;
        $this->added($id);
        $this->changed();
        $this->getApp()->cache->clear();
        return $id;
    }

    public function edit(int $id, string $title, string $url)
    {
        $item = $this->getItem($id);
        if (($item['title'] == $title) && ($item['url'] == $url)) {
            return;
        }

        $item['title'] = $title;
        $this->db->updateAssoc(
            array(
            'id' => $id,
            'title' => $title
            )
        );

        $app = $this->getApp();
        $linkgen = LinkGenerator::i();
        $url = trim($url);
        // try rebuild url
        if (!$url) {
            $url = $linkgen->createUrl($title, $this->PermalinkIndex, false);
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

    public function getNames(array $list): array
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

    public function getLinks(array $list): array
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

    public function getSorted(int $parent, string $sortname, int $count): array
    {
        $count = (int)$count;
        if ($sortname == 'count') {
            $sortname = 'itemscount';
        }

        if (!in_array(
            $sortname, array(
            'title',
            'itemscount',
            'customorder',
            'id'
            )
        )) {
            $sortname = 'title';
        }

        $limit = $sortname == 'itemscount' ? "order by $this->thistable.$sortname desc" : "order by $this->thistable.$sortname asc";

        if ($count) {
            $limit.= " limit $count";
        }

        return $this->select($parent == - 1 ? '' : "$this->thistable.parent = $parent", $limit);
    }

    public function getIdPosts(int $id, int $from, int $perpage, bool $invertOrder): array
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

        $result = $this->db->res2id(
            $this->db->query(
                "select $ti.$postprop as $postprop, $p.id as id from $ti, $p
    where    $ti.$itemprop $tags and $p.id = $ti.$postprop and $p.status = 'published'
    order by $p.posted $order limit $from, $perpage"
            )
        );

        $result = array_unique($result);
        $posts->loadItems($result);
        return $result;
    }

    public function getParents(int $id): array
    {
        $result = array();
        while ($id = (int)$this->items[$id]['parent']) {
            //if (!isset($this->items[$id])) $this->error(sprintf('Parent category %d not exists', $id);
            $result[] = $id;
        }

        return $result;
    }

    public function getChilds(int $parent): array
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

    public function getSitemap(int $from, int $count)
    {
        return $this->externalfunc(
            __class__, 'Getsitemap', array(
            $from,
            $count
            )
        );
    }

    public function getSortedPosts(int $id, int $count, bool $invert): array
    {
        $ti = $this->itemsposts->thistable;
        $posts = $this->factory->posts;
        $p = $posts->thistable;
        $order = $invert ? 'asc' : 'desc';
        $result = $this->db->res2id(
            $this->db->query(
                "select $p.id as id, $ti.post as post from $p, $ti
    where    $ti.item = $id and $p.id = $ti.post and $p.status = 'published'
    order by $p.posted $order limit 0, $count"
            )
        );

        $posts->loadItems($result);
        return $result;
    }
}

//Content.php
namespace litepubl\tag;

use litepubl\view\Filter;

class Content extends \litepubl\core\Data
{
    private $owner;
    private $items;

    public function __construct(Common $owner)
    {
        parent::__construct();
        $this->owner = $owner;
        $this->items = array();
    }

    public function getItem($id)
    {
        if (isset($this->items[$id])) {
            return $this->items[$id];
        }

        $item = array(
            'description' => '',
            'keywords' => '',
            'head' => '',
            'content' => '',
            'rawcontent' => ''
        );

        if ($r = $this->db->getitem($id)) {
            $item = $r;
        }

        $this->items[$id] = $item;
        return $item;
    }

    public function setItem($id, $item)
    {
        if (isset($this->items[$id]) && ($this->items[$id] == $item)) {
            return;
        }

        $this->items[$id] = $item;
        $item['id'] = $id;
        $this->db->addupdate($item);
    }

    public function edit($id, $content, $description, $keywords, $head)
    {
        $item = $this->getitem($id);
        $filter = Filter::i();
        $item = array(
            'content' => $filter->filter($content) ,
            'rawcontent' => $content,
            'description' => $description,
            'keywords' => $keywords,
            'head' => $head
        );
        $this->setitem($id, $item);
    }

    public function delete($id)
    {
        $this->db->iddelete($id);
    }

    public function getValue($id, $name)
    {
        $item = $this->getitem($id);
        return $item[$name];
    }

    public function setValue($id, $name, $value)
    {
        $item = $this->getitem($id);
        $item[$name] = $value;
        $this->setitem($id, $item);
    }

    public function getContent($id)
    {
        return $this->getvalue($id, 'content');
    }

    public function setContent($id, $content)
    {
        $item = $this->getitem($id);
        $filter = Filter::i();
        $item['rawcontent'] = $content;
        $item['content'] = $filter->filterpages($content);
        $item['description'] = Filter::getexcerpt($content, 80);
        $this->setitem($id, $item);
    }

    public function getDescription($id)
    {
        return $this->getvalue($id, 'description');
    }

    public function getKeywords($id)
    {
        return $this->getvalue($id, 'keywords');
    }

    public function getHead($id)
    {
        return $this->getvalue($id, 'head');
    }
}

//Factory.php
namespace litepubl\tag;

use litepubl\post\Post;
use litepubl\post\Posts;

class Factory
{
    public function __get($name)
    {
        return $this->{'get' . $name}();
    }

    public function getPosts()
    {
        return Posts::i();
    }

    public function getPost($id)
    {
        return Post::i($id);
    }
}

//Tags.php
namespace litepubl\tag;

use litepubl\widget\Tags as TagsWidget;
use litepubl\widget\Cache;

class Tags extends Common
{

    protected function create()
    {
        parent::create();
        $this->table = 'tags';
        $this->basename = 'tags';
        $this->PermalinkIndex = 'tag';
        $this->postpropname = 'tags';
        $this->contents->table = 'tagscontent';
        $this->itemsposts->table = $this->table . 'items';
    }

    public function save()
    {
        parent::save();
        if (!$this->locked) {
Cache::i()->removeWidget(TagsWidget::i());
        }
    }
}

//View.php
namespace litepubl\tag;

use litepubl\core\Context;
use litepubl\core\Str;
use litepubl\post\Announce;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Schema;
use litepubl\view\Theme;
use litepubl\view\Vars;

class View extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
    public $id;
    private $tags;
    private $cachedIdPosts;
    private $context;

    protected function create()
    {
        parent::create();
        $this->addEvents('onbeforecontent', 'oncontent');
        $this->cachedIdPosts = array();
    }

    public function setTags(Common $tags)
    {
        $this->tags = $tags;
    }

    public function getSorted(array $tml, $parent, $sortname, $count, $showcount)
    {
        $sorted = $this->tags->getSorted($parent, $sortname, $count);
        if (!count($sorted)) {
            return '';
        }

        $result = '';
        $theme = Theme::i();
        $tags = $this->tags;
        $args = new Args();
        $args->rel = $tags->PermalinkIndex;
        $args->parent = $parent;

        foreach ($sorted as $id) {
            $item = $tags->getItem($id);
            $args->add($item);
            $args->icon = '';
            $args->subcount = $showcount ? $theme->parseArg($tml['subcount'], $args) : '';
            $args->subitems = $tml['subitems'] ? $this->getSorted($tml, $id, $sortname, $count, $showcount) : '';

            $result.= $theme->parseArg($tml['item'], $args);
        }

        if (!$parent) {
            return $result;
        }

        $args->parent = $parent;
        $args->item = $result;
        return $theme->parseArg($tml['subitems'], $args);
    }

    public function getValue($name)
    {
        return $this->tags->getValue($this->id, $name);
    }

    public function getPostPropName()
    {
        return $this->tags->postpropname;
    }

    public function request(Context $context)
    {
        if ($this->id = (int)$context->itemRoute['arg']) {
            try {
                $item = $this->tags->getItem($this->id);
            } catch (\Exception $e) {
                $context->response->status = 404;
                return;
            }

            $schema = Schema::getSchema($this);
            $perpage = $schema->perpage ? $schema->perpage : $this->getApp()->options->perpage;
            $pages = (int)ceil($item['itemscount'] / $perpage);
            if (($context->request->page > 1) && ($context->request->page > $pages)) {
                $context->response->redir($item['url']);
                return;
            }
        }

        $this->context = $context;
    }

    public function getTitle(): string
    {
        if ($this->id) {
            return $this->getValue('title');
        }

        return Lang::i()->categories;
    }

    public function getHead(): string
    {
        if ($this->id) {
            $result = $this->tags->contents->getValue($this->id, 'head');

            $theme = Schema::getSchema($this)->theme;
            $result.= $theme->templates['head.tags'];

            $list = $this->getIdPosts($this->id);
            $announce = new Announce($theme);
            $result.= $announce->getAnHead($list);

            return $theme->parse($result);
        }

        return '';
    }

    public function getKeywords(): string
    {
        if ($this->id) {
            $result = $this->tags->contents->getValue($this->id, 'keywords');
            if (!$result) {
                $result = $this->title;
            }

            return $result;
        }

        return '';
    }

    public function getDescription(): string
    {
        if ($this->id) {
            $result = $this->tags->contents->getvalue($this->id, 'description');
            if (!$result) {
                $result = $this->title;
            }

            return $result;
        }

        return '';
    }

    public function getIdschema(): int
    {
        if ($this->id) {
            return $this->getValue('idschema');
        }

        return 1;
    }

    public function setIdSchema(int $id)
    {
        if ($id != $this->idschema) {
            $this->tags->setValue($this->id, 'idschema', $id);
        }
    }

    public function getIdPerm(): int
    {
        if ($this->id) {
            $item = $this->tags->getItem($this->id);
            return isset($item['idperm']) ? (int)$item['idperm'] : 0;
        }

        return 0;
    }

    public function getIndex_tml()
    {
        $theme = Theme::i();
        if (!empty($theme->templates['index.tag'])) {
            return $theme->templates['index.tag'];
        }

        return false;
    }

    public function getContent()
    {
        if ($s = $this->tags->contents->getcontent($this->id)) {
            $pages = explode('<!--nextpage-->', $s);
            $page = $this->context->request->page - 1;
            if (isset($pages[$page])) {
                return $pages[$page];
            }
        }

        return '';
    }

    public function getCont(): string
    {
        $result = new Str('');
        $this->onbeforecontent($result);

        if (!$this->id) {
            $result->value.= $this->getcont_all();
        } else {
            $schema = Schema::getSchema($this);
            $theme = $schema->theme;

            if ($this->getContent()) {
                $vars = new Vars();
                $vars->menu = $this;
                $result->value.= $theme->parse($theme->templates['content.menu']);
            }

            $list = $this->getIdPosts($this->id);
            $item = $this->tags->getItem($this->id);
            $announce = new Announce($theme);
            $result->value.= $announce->getPostsNavi($list, $item['url'], $item['itemscount'], $schema->postanounce, $schema->perpage);
        }

        $this->oncontent($result);
        return $result->value;
    }

    public function getCont_all()
    {
        return sprintf(
            '<ul>%s</ul>', $this->getSorted(
                array(
                'item' => '<li><a href="$link" title="$title">$icon$title</a>$subcount</li>',
                'subcount' => '<strong>($itemscount)</strong>',
                'subitems' => '<ul>$item</ul>'
                ), 0, 'count', 0, 0, false
            )
        );
    }

    public function getIdPosts($id)
    {
        if (isset($this->cachedIdPosts[$id])) {
            return $this->cachedIdPosts[$id];
        }

        $schema = Schema::i($this->tags->getValue($id, 'idschema'));
        $perpage = $schema->perpage ? $schema->perpage : $this->getApp()->options->perpage;
        $from = ($this->context->request->page - 1) * $perpage;

        $result = $this->tags->getIdPosts($id, $from, $perpage, $schema->invertorder);
        $this->cachedIdPosts[$id] = $result;
        return $result;
    }
}

