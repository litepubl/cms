<?php
//Ajax.php
namespace litepubl\widget;

use litepubl\core\Context;
use litepubl\core\Response;
use litepubl\core\litepubl;
use litepubl\view\Schema;
use litepubl\view\Theme;

class Ajax implements \litepubl\core\ResponsiveInterface
{
    public $url = '/getwidget.htm';

    public function request(Context $context)
    {
        $response = $context->response;
        $response->cache = false;
        $id = (int)$context->request->getArg('id');
        $sidebar = (int)$context->request->getArg('sidebar');
        $idurl = (int)$context->request->getArg('idurl');

        $widgets = Widgets::i();
        if (!$id || !$widgets->itemExists($id)) {
            return $this->errorRequest('Invalid params');
        }

        $themename = $context->request->getArg('themename', Schema::i(1)->themename);
        if (!preg_match('/^\w[\w\.\-_]*+$/', $themename) || !Theme::exists($themename)) {
            $themename = Schema::i(1)->themename;
        }

        try {
            $theme = Theme::getTheme($themename);

            $widgets->onFindContextCallback = function ($class) use ($idurl) {
            
                if (($item = litepubl::$app->router->getItem($idurl)) && is_a($class, $item['class'], true)) {
                    if (is_a($item['class'], 'litepubl\core\Item', true)) {
                        return ($item['class']) ::i($item['arg']);
                    } else {
                        return litepubl::$app->classes->getInstance($item['class']);
                    }
                }
            };

            $response->body = $widgets->getWidgetContent($id, $sidebar);
        } catch (\Exception $e) {
            return $this->errorRequest('Cant get widget content');
        }
    }

    private function errorRequest(Response $response, $mesg)
    {
        $response->status = 400;
        $response->body = $mesg;
    }
}

//Archives.php
namespace litepubl\widget;

use litepubl\post\Archives as Arch;
use litepubl\view\Args;
use litepubl\view\Lang;

class Archives extends Widget
{

    protected function create()
    {
        parent::create();
        $this->basename = 'widget.archives';
        $this->template = 'archives';
        $this->adminclass = '\litepubl\admin\widget\ShowCount';
        $this->data['showcount'] = false;
    }

    public function getDeftitle()
    {
        return Lang::get('default', 'archives');
    }

    protected function setShowcount($value)
    {
        if ($value != $this->showcount) {
            $this->data['showcount'] = $value;
            $this->Save();
        }
    }

    public function getContent($id, $sidebar)
    {
        $arch = Arch::i();
        if (!count($arch->items)) {
            return '';
        }

        $result = '';
        $view = new View();
        $tml = $view->getItem('archives', $sidebar);
        if ($this->showcount) {
            $counttml = $view->getTml($sidebar, 'archives', 'subcount');
        }

        $args = new Args();
        $args->icon = '';
        $args->subcount = '';
        $args->subitems = '';
        $args->rel = 'archives';
        foreach ($arch->items as $date => $item) {
            $args->add($item);
            $args->text = $item['title'];
            if ($this->showcount) {
                $args->subcount = str_replace('$itemscount', $item['count'], $counttml);
            }

            $result.= $view->theme->parseArg($tml, $args);
        }

        return $view->getContent($result, 'archives', $sidebar);
    }
}

//Cache.php
namespace litepubl\widget;

use litepubl\view\Theme;

class Cache extends \litepubl\core\Items
{
    private $modified;

    protected function create()
    {
        $this->dbversion = false;
        parent::create();
        $this->modified = false;
    }

    public function getBasename()
    {
        $theme = Theme::i();
        return 'widgetscache.' . $theme->name;
    }

    public function load()
    {
        if ($data = $this->getApp()->cache->get($this->getbasename())) {
            $this->data = $data;
            $this->afterload();
            return true;
        }

        return false;
    }

    public function savemodified()
    {
        if ($this->modified) {
            $this->modified = false;
            $this->getApp()->cache->set($this->getbasename(), $this->data);
        }
    }

    public function save()
    {
        if (!$this->modified) {
            $this->modified = true;
            $this->getApp()->onClose->on($this, 'saveModified');
        }
    }

    public function getContent($id, $sidebar, $onlybody = true)
    {
        if (isset($this->items[$id][$sidebar])) {
            return $this->items[$id][$sidebar];
        }

        return $this->setcontent($id, $sidebar, $onlybody);
    }

    public function setContent($id, $sidebar, $onlybody = true)
    {
        $widget = Widgets::i()->getwidget($id);

        if ($onlybody) {
            $result = $widget->getcontent($id, $sidebar);
        } else {
            $result = $widget->getwidget($id, $sidebar);
        }

        $this->items[$id][$sidebar] = $result;
        $this->save();
        return $result;
    }

    public function expired($id)
    {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();
        }
    }

    public function onclearcache()
    {
        $this->items = array();
        $this->modified = false;
    }
}

//Cats.php
namespace litepubl\widget;

use litepubl\tag\Cats as Owner;
use litepubl\view\Lang;

class Cats extends CommonTags
{

    protected function create()
    {
        parent::create();
        $this->basename = 'widget.categories';
        $this->template = 'categories';
    }

    public function getDeftitle()
    {
        return Lang::get('default', 'categories');
    }

    public function getOwner()
    {
        return Owner::i();
    }
}

//Comments.php
namespace litepubl\widget;

use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Lang;

class Comments extends Widget
{

    protected function create()
    {
        parent::create();
        $this->basename = 'widget.comments';
        $this->cache = 'include';
        $this->template = 'comments';
        $this->adminclass = '\litepubl\admin\widget\MaxCount';
        $this->data['maxcount'] = 7;
    }

    public function getDeftitle()
    {
        return Lang::get('default', 'recentcomments');
    }

    public function getContent($id, $sidebar)
    {
        $recent = $this->getrecent($this->maxcount);
        if (!count($recent)) {
            return '';
        }

        $result = '';
        $view = new View();
        $tml = $view->getItem('comments', $sidebar);
        $url = $this->getApp()->site->url;
        $args = new Args();
        $args->onrecent = Lang::get('comment', 'onrecent');
        foreach ($recent as $item) {
            $args->add($item);
            $args->link = $url . $item['posturl'];
            $args->content = Filter::getexcerpt($item['content'], 120);
            $result.= $view->theme->parseArg($tml, $args);
        }
        return $view->getContent($result, 'comments', $sidebar);
    }

    public function changed()
    {
        $this->expire();
    }

    public function getRecent($count, $status = 'approved')
    {
        $db = $this->getApp()->db;
        $result = $db->res2assoc($db->query("select $db->comments.*,
    $db->users.name as name, $db->users.email as email, $db->users.website as url,
    $db->posts.title as title, $db->posts.commentscount as commentscount,
    $db->urlmap.url as posturl
    from $db->comments, $db->users, $db->posts, $db->urlmap
    where $db->comments.status = '$status' and
    $db->users.id = $db->comments.author and
    $db->posts.id = $db->comments.post and
    $db->urlmap.id = $db->posts.idurl and
    $db->posts.status = 'published' and
    $db->posts.idperm = 0
    order by $db->comments.posted desc limit $count"));

        if ($this->getApp()->options->commentpages && !$this->getApp()->options->comments_invert_order) {
            foreach ($result as $i => $item) {
                $page = ceil($item['commentscount'] / $this->getApp()->options->commentsperpage);
                if ($page > 1) {
                    $result[$i]['posturl'] = rtrim($item['posturl'], '/') . "/page/$page/";
                }
            }
        }
        return $result;
    }
}

//Custom.php
namespace litepubl\widget;

class Custom extends Widget
{
    public $items;

    protected function create()
    {
        parent::create();
        $this->basename = 'widgets.custom';
        $this->adminclass = '\litepubl\admin\widget\Custom';
        $this->addmap('items', array());
        $this->addevents('added', 'deleted');
    }

    public function getWidget($id, $sidebar)
    {
        if (!isset($this->items[$id])) {
            return '';
        }

        $item = $this->items[$id];
        if ($item['template'] == '') {
            return $item['content'];
        }

        $view = new View();
        return $view->getWidget($item['title'], $item['content'], $item['template'], $sidebar);
    }

    public function getTitle($id)
    {
        return $this->items[$id]['title'];
    }

    public function getContent($id, $sidebar)
    {
        return $this->items[$id]['content'];
    }

    public function add($idschema, $title, $content, $template)
    {
        $widgets = Widgets::i();
        $widgets->lock();
        $id = $widgets->addext($this, $title, $template);
        $this->items[$id] = array(
            'title' => $title,
            'content' => $content,
            'template' => $template
        );

        $sidebars = Sidebars::i($idschema);
        $sidebars->add($id);
        $widgets->unlock();
        $this->save();
        $this->added($id);
        return $id;
    }

    public function edit($id, $title, $content, $template)
    {
        $this->items[$id] = array(
            'title' => $title,
            'content' => $content,
            'template' => $template
        );
        $this->save();

        $widgets = Widgets::i();
        $widgets->items[$id]['title'] = $title;
        $widgets->save();
        $this->expired($id);
        $this->getApp()->cache->clear();
    }

    public function delete($id)
    {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();

            $widgets = Widgets::i();
            $widgets->delete($id);
            $this->deleted($id);
        }
    }

    public function widgetdeleted($id)
    {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();
        }
    }
}

//Depended.php
namespace litepubl\widget;

class Depended extends Widget
{
    private $item;

    private function isvalue($name)
    {
        return in_array($name, array(
            'ajax',
            'order',
            'sidebar'
        ));
    }

    public function __get($name)
    {
        if ($this->isvalue($name)) {
            if (!$this->item) {
                $widgets = Widgets::i();
                $this->item = & $widgets->finditem($widgets->find($this));
            }
            return $this->item[$name];
        }
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if ($this->isvalue($name)) {
            if (!$this->item) {
                $widgets = Widgets::i();
                $this->item = & $widgets->finditem($widgets->find($this));
            }
            $this->item[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function save()
    {
        parent::save();
        Widgets::i()->save();
    }
}

//Links.php
namespace litepubl\widget;

use litepubl\core\Context;
use litepubl\core\Str;
use litepubl\view\Args;
use litepubl\view\Lang;

class Links extends Widget implements \litepubl\core\ResponsiveInterface
{
    public $items;
    public $autoid;
    public $redirlink;

    protected function create()
    {
        parent::create();
        $this->addevents('added', 'deleted');
        $this->basename = 'widgets.links';
        $this->template = 'links';
        $this->adminclass = '\litepubl\admin\widget\Links';
        $this->addmap('items', array());
        $this->addmap('autoid', 0);
        $this->redirlink = '/linkswidget/';
        $this->data['redir'] = false;
    }

    public function getDeftitle()
    {
        return Lang::get('default', 'links');
    }

    public function getContent($id, $sidebar)
    {
        if (count($this->items) == 0) {
            return '';
        }

        $result = '';
        $view = new View();
        $tml = $view->getItem('links', $sidebar);
        $redirlink = $this->getApp()->site->url . $this->redirlink . $this->getApp()->site->q . 'id=';
        $url = $this->getApp()->site->url;
        $args = new Args();
        $args->subcount = '';
        $args->subitems = '';
        $args->icon = '';
        $args->rel = 'link';
        foreach ($this->items as $id => $item) {
            $args->add($item);
            $args->id = $id;
            if ($this->redir && !Str::begin($item['url'], $url)) {
                $args->link = $redirlink . $id;
            } else {
                $args->link = $item['url'];
            }
            $result.= $view->theme->parseArg($tml, $args);
        }

        return $view->getContent($result, 'links', $sidebar);
    }

    public function add($url, $title, $text)
    {
        $this->items[++$this->autoid] = array(
            'url' => $url,
            'title' => $title,
            'text' => $text
        );

        $this->save();
        $this->added($this->autoid);
        return $this->autoid;
    }

    public function edit($id, $url, $title, $text)
    {
        $id = (int)$id;
        if (!isset($this->items[$id])) {
            return false;
        }

        $this->items[$id] = array(
            'url' => $url,
            'title' => $title,
            'text' => $text
        );
        $this->save();
    }

    public function delete($id)
    {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();
            $this->getApp()->cache->clear();
        }
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $response->cache = false;
        $id = empty($_GET['id']) ? 1 : (int)$_GET['id'];
        if (!isset($this->items[$id])) {
            $response->status = 404;
            return;
        }

        $response->redir($this->items[$id]['url']);
    }
}

//Meta.php
namespace litepubl\widget;

use litepubl\view\Args;
use litepubl\view\Lang;

class Meta extends Widget
{
    public $items;

    protected function create()
    {
        parent::create();
        $this->basename = 'widget.meta';
        $this->template = 'meta';
        $this->adminclass = '\litepubl\admin\widget\Meta';
        $this->addmap('items', array());
    }

    public function getDeftitle()
    {
        return Lang::get('default', 'subscribe');
    }

    public function add($name, $url, $title)
    {
        $this->items[$name] = array(
            'enabled' => true,
            'url' => $url,
            'title' => $title
        );
        $this->save();
    }

    public function delete($name)
    {
        if (isset($this->items[$name])) {
            unset($this->items[$name]);
            $this->save();
        }
    }

    public function getContent($id, $sidebar)
    {
        $result = '';
        $view = new View();
        $tml = $view->getItem('meta', $sidebar);
        $metaclasses = $view->getTml($sidebar, 'meta', 'classes');
        $args = new Args();
        foreach ($this->items as $name => $item) {
            if (!$item['enabled']) {
                continue;
            }

            $args->add($item);
            $args->icon = '';
            $args->subcount = '';
            $args->subitems = '';
            $args->rel = $name;
            if ($name == 'profile') {
                $args->rel = 'author profile';
            }
            $args->class = isset($metaclasses[$name]) ? $metaclasses[$name] : '';
            $result.= $view->theme->parseArg($tml, $args);
        }

        if ($result == '') {
            return '';
        }

        return $view->getContent($result, 'meta', $sidebar);
    }
}

//Order.php
namespace litepubl\widget;

use litepubl\core\Arr;

class Order extends Widget
{

    protected function create()
    {
        parent::create();
        unset($this->id);
        $this->data['id'] = 0;
        $this->data['ajax'] = false;
        $this->data['order'] = 0;
        $this->data['sidebar'] = 0;
    }

    public function onsidebar(array & $items, $sidebar)
    {
        if ($sidebar != $this->sidebar) {
            return;
        }

        $order = $this->order;
        if (($order < 0) || ($order >= count($items))) {
            $order = count($items);
        }
        Arr::insert($items, array(
            'id' => $this->id,
            'ajax' => $this->ajax
        ), $order);
    }
}

//Posts.php
namespace litepubl\widget;

use litepubl\post\Posts as PostItems;
use litepubl\view\Lang;

class Posts extends Widget
{

    protected function create()
    {
        parent::create();
        $this->basename = 'widget.posts';
        $this->template = 'posts';
        $this->adminclass = '\litepubl\admin\widget\MaxCount';
        $this->data['maxcount'] = 10;
    }

    public function getDeftitle()
    {
        return Lang::get('default', 'recentposts');
    }

    public function getContent($id, $sidebar)
    {
        $posts = PostItems::i();
        $list = $posts->getpage(0, 1, $this->maxcount, false);
        $view = new View();
        return $view->getPosts($list, $sidebar, '');
    }
}

//Sidebars.php
namespace litepubl\widget;

use litepubl\core\Arr;
use litepubl\view\Schema;
use litepubl\view\Schemes;

class Sidebars extends \litepubl\core\Data
{
    public $items;

    public static function i($id = 0)
    {
        $result = static ::iGet(get_called_class());
        if ($id) {
            $schema = Schema::i((int)$id);
            $result->items = & $schema->sidebars;
        }

        return $result;
    }

    protected function create()
    {
        parent::create();
        $schema = Schema::i();
        $this->items = & $schema->sidebars;
    }

    public function load()
    {
    }

    public function save()
    {
        Schema::i()->save();
    }

    public function add($id)
    {
        $this->insert($id, false, 0, -1);
    }

    public function insert($id, $ajax, $index, $order)
    {
        if (!isset($this->items[$index])) {
            return $this->error("Unknown sidebar $index");
        }

        $item = array(
            'id' => $id,
            'ajax' => $ajax
        );
        if (($order < 0) || ($order > count($this->items[$index]))) {
            $this->items[$index][] = $item;
        } else {
            Arr::insert($this->items[$index], $item, $order);
        }
        $this->save();
    }

    public function remove($id)
    {
        if ($pos = static ::getpos($this->items, $id)) {
            Arr::delete($this->items[$pos[0]], $pos[1]);
            $this->save();
            return $pos[0];
        }
    }

    public function delete($id, $index)
    {
        if ($i = $this->indexof($id, $index)) {
            Arr::delete($this->items[$index], $i);
            $this->save();
            return $i;
        }
        return false;
    }

    public function deleteClass($classname)
    {
        if ($id = Widgets::i()->class2id($classname)) {
            Schemes::i()->widgetdeleted($id);
        }
    }

    public function indexOf($id, $index)
    {
        foreach ($this->items[$index] as $i => $item) {
            if ($id == $item['id']) {
                return $i;
            }
        }

        return false;
    }

    public function setAjax($id, $ajax)
    {
        foreach ($this->items as $index => $items) {
            if ($pos = $this->indexof($id, $index)) {
                $this->items[$index][$pos]['ajax'] = $ajax;
            }
        }
    }

    public function move($id, $index, $neworder)
    {
        if ($old = $this->indexof($id, $index)) {
            if ($old != $newindex) {
                Arr::move($this->items[$index], $old, $neworder);
                $this->save();
            }
        }
    }

    public static function getPos(array & $sidebars, $id)
    {
        foreach ($sidebars as $i => $sidebar) {
            foreach ($sidebar as $j => $item) {
                if ($id == $item['id']) {
                    return array(
                        $i,
                        $j
                    );
                }
            }
        }
        return false;
    }

    public static function setPos(array & $items, $id, $newsidebar, $neworder)
    {
        if ($pos = static ::getpos($items, $id)) {
            list($oldsidebar, $oldorder) = $pos;
            if (($oldsidebar != $newsidebar) || ($oldorder != $neworder)) {
                $item = $items[$oldsidebar][$oldorder];
                Arr::delete($items[$oldsidebar], $oldorder);
                if (($neworder < 0) || ($neworder > count($items[$newsidebar]))) {
                    $neworder = count($items[$newsidebar]);
                }
                Arr::insert($items[$newsidebar], $item, $neworder);
            }
        }
    }

    public static function fix()
    {
        $widgets = Widgets::i();
        foreach ($widgets->classes as $classname => & $items) {
            foreach ($items as $i => $item) {
                if (!isset($widgets->items[$item['id']])) {
                    unset($items[$i]);
                }
            }
        }

        $schemes = Schemes::i();
        foreach ($schemes->items as & $schemaItem) {
            if (($schemaItem['id'] != 1) && !$schemaItem['customsidebar']) {
                continue;
            }

            unset($sidebar);
            foreach ($schemaItem['sidebars'] as & $sidebar) {
                for ($i = count($sidebar) - 1; $i >= 0; $i--) {
                    if (!isset($widgets->items[$sidebar[$i]['id']])) {
                        Arr::delete($sidebar, $i);
                    }
                }
            }
        }
        $schemes->save();
    }
}

//Tags.php
namespace litepubl\widget;

use litepubl\tag\Tags as Owner;
use litepubl\view\Lang;

class Tags extends CommonTags
{

    protected function create()
    {
        parent::create();
        $this->basename = 'widget.tags';
        $this->template = 'tags';
        $this->sortname = 'title';
        $this->showcount = false;
    }

    public function getDeftitle()
    {
        return Lang::get('default', 'tags');
    }

    public function getOwner()
    {
        return Owner::i();
    }
}

//View.php
namespace litepubl\widget;

use litepubl\post\Post;
use litepubl\view\Args;
use litepubl\view\Theme;
use litepubl\view\Vars;

class View
{
    public $theme;

    public function __construct(Theme $theme = null)
    {
        $this->theme = $theme ? $theme : Theme::context();
    }

    public function getPosts(array $items, $sidebar, $tml)
    {
        if (!count($items)) {
            return '';
        }

        $result = '';
        if (!$tml) {
            $tml = $this->getItem('posts', $sidebar);
        }

        $vars = new Vars();
        foreach ($items as $id) {
            $vars->post = Post::i($id)->getView();
            $result.= $this->theme->parse($tml);
        }

        return str_replace('$item', $result, $this->getItems('posts', $sidebar));
    }

    public function getContent($items, $name, $sidebar)
    {
        return str_replace('$item', $items, $this->getItems($name, $sidebar));
    }

    public function getWidget($title, $content, $template, $sidebar)
    {
        $args = new Args();
        $args->title = $title;
        $args->items = $content;
        $args->sidebar = $sidebar;
        return $this->theme->parseArg($this->getTml($sidebar, $template, ''), $args);
    }

    public function getWidgetId($id, $title, $content, $template, $sidebar)
    {
        $args = new Args();
        $args->id = $id;
        $args->title = $title;
        $args->items = $content;
        $args->sidebar = $sidebar;
        return $this->theme->parseArg($this->getTml($sidebar, $template, ''), $args);
    }

    public function getItem($name, $index)
    {
        return $this->getTml($index, $name, 'item');
    }

    public function getItems($name, $index)
    {
        return $this->getTml($index, $name, 'items');
    }

    public function getTml($index, $name, $tml)
    {
        $count = count($this->theme->templates['sidebars']);
        if ($index >= $count) {
            $index = $count - 1;
        }

        $widgets = $this->theme->templates['sidebars'][$index];
        if (($tml != '') && ($tml[0] != '.')) {
            $tml = '.' . $tml;
        }

        if (isset($widgets[$name . $tml])) {
            return $widgets[$name . $tml];
        }

        if (isset($widgets['widget' . $tml])) {
            return $widgets['widget' . $tml];
        }

        $this->error("Unknown widget '$name' and template '$tml' in $index sidebar");
    }

    public function getAjax($id, $title, $sidebar, $tml)
    {
        $args = new Args();
        $args->title = $title;
        $args->id = $id;
        $args->sidebar = $sidebar;
        return $this->theme->parseArg($this->theme->templates[$tml], $args);
    }
}

//Widgets.php
namespace litepubl\widget;

use litepubl\core\Arr;
use litepubl\core\Context;
use litepubl\core\Str;
use litepubl\view\Schema;
use litepubl\view\ViewInterface;

class Widgets extends \litepubl\core\Items
{
    use \litepubl\core\PoolStorageTrait;

    public $classes;
    public $currentSidebar;
    public $idwidget;
    public $onFindContextCallback;

    protected function create()
    {
        $this->dbversion = false;
        parent::create();
        $this->addevents('onwidget', 'onadminlogged', 'onadminpanel', 'ongetwidgets', 'onsidebar');
        $this->basename = 'widgets';
        $this->currentSidebar = 0;
        $this->addMap('classes', array());
    }

    public function add(Widget $widget)
    {
        return $this->addItem(array(
            'class' => get_class($widget) ,
            'cache' => $widget->cache,
            'title' => $widget->getTitle(0) ,
            'template' => $widget->template
        ));
    }

    public function addExt(Widget $widget, $title, $template)
    {
        return $this->addItem(array(
            'class' => get_class($widget) ,
            'cache' => $widget->cache,
            'title' => $title,
            'template' => $template
        ));
    }

    public function addClass(Widget $widget, $class)
    {
        $this->lock();
        $id = $this->add($widget);
        if (!isset($this->classes[$class])) {
            $this->classes[$class] = array();
        }

        $this->classes[$class][] = array(
            'id' => $id,
            'order' => 0,
            'sidebar' => 0,
            'ajax' => false
        );

        $this->unlock();
        return $id;
    }

    public function subClass($id)
    {
        foreach ($this->classes as $class => $items) {
            foreach ($items as $item) {
                if ($id == $item['id']) {
                    return $class;
                }
            }
        }

        return false;
    }

    public function delete($id)
    {
        if (!isset($this->items[$id])) {
            return false;
        }

        foreach ($this->classes as $class => $items) {
            foreach ($items as $i => $item) {
                if ($id == $item['id']) {
                    Arr::delete($this->classes[$class], $i);
                }
            }
        }

        unset($this->items[$id]);
        $this->deleted($id);
        $this->save();
        return true;
    }

    public function deleteClass($class)
    {
        $this->unbind($class);
        $deleted = array();
        foreach ($this->items as $id => $item) {
            if ($class == $item['class']) {
                unset($this->items[$id]);
                $deleted[] = $id;
            }
        }

        if (count($deleted) > 0) {
            foreach ($this->classes as $name => $items) {
                foreach ($items as $i => $item) {
                    if (in_array($item['id'], $deleted)) {
                        Arr::delete($this->classes[$name], $i);
                    }
                }

                if (!count($this->classes[$name])) {
                    unset($this->classes[$name]);
                }
            }
        }

        if (isset($this->classes[$class])) {
            unset($this->classes[$class]);
        }

        $this->save();

        foreach ($deleted as $id) {
            $this->deleted($id);
        }
    }

    public function class2id($class)
    {
        foreach ($this->items as $id => $item) {
            if ($class == $item['class']) {
                return $id;
            }
        }

        return false;
    }

    public function getWidget($id)
    {
        if (!isset($this->items[$id])) {
            return $this->error("The requested $id widget not found");
        }

        $class = $this->items[$id]['class'];
        if (!class_exists($class)) {
            $this->delete($id);
            return $this->error("The $class class not found");
        }

        $result = static ::iGet($class);
        $result->id = $id;
        return $result;
    }

    public function getSidebar(ViewInterface $view)
    {
        return $this->getSidebarIndex($view, $this->currentSidebar++);
    }

    public function getSidebarIndex(ViewInterface $view, $sidebar)
    {
        $items = new \ArrayObject($this->getWidgets($view, $sidebar), \ArrayObject::ARRAY_AS_PROPS);
        if ($view instanceof WidgetsInterface) {
            $view->getWidgets($items, $sidebar);
        }

        $options = $this->getApp()->options;
        if ($options->adminFlag && $options->group == 'admin') {
            $this->onadminlogged($items, $sidebar);
        }

        /*
        if ( $router->adminpanel) {
            $this->onadminpanel($items, $sidebar);
        }
        */
        $this->ongetwidgets($items, $sidebar);

        $schema = Schema::getSchema($view);
        $result = $this->getSidebarContent($items, $sidebar, !$schema->customsidebar && $schema->disableajax);

        $str = new Str($result);
        if ($view instanceof WidgetsInterface) {
            $view->getSidebar($str, $sidebar);
        }

        $this->onsidebar($str, $sidebar);
        return $str->value;
    }

    private function getWidgets(ViewInterface $view, $sidebar)
    {
        $schema = Schema::getSchema($view);
        $theme = $schema->theme;
        if (($schema->id > 1) && !$schema->customsidebar) {
            $schema = Schema::i(1);
        }

        $items = isset($schema->sidebars[$sidebar]) ? $schema->sidebars[$sidebar] : array();

        $subItems = $this->getSubItems($view, $sidebar);
        $items = $this->joinItems($items, $subItems);
        if ($sidebar + 1 == $theme->sidebarsCount) {
            for ($i = $sidebar + 1; $i < count($schema->sidebars); $i++) {
                $subItems = $this->joinItems($schema->sidebars[$i], $this->getSubItems($view, $i));

                //delete copies
                foreach ($subItems as $index => $subItem) {
                    $id = $subItem['id'];
                    foreach ($items as $item) {
                        if ($id == $item['id']) {
                            Arr::delete($subItems, $index);
                        }
                    }
                }

                foreach ($subItems as $item) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    private function getSubItems(ViewInterface $view, $sidebar)
    {
        $result = array();
        foreach ($this->classes as $class => $items) {
            if ($view instanceof $class) {
                foreach ($items as $item) {
                    if ($sidebar == $item['sidebar']) {
                        $result[] = $item;
                    }
                }
            }
        }

        return $result;
    }

    private function joinItems(array $items, array $subitems)
    {
        if (count($subitems) == 0) {
            return $items;
        }

        if (count($items)) {
            //delete copies
            for ($i = count($items) - 1; $i >= 0; $i--) {
                $id = $items[$i]['id'];
                foreach ($subitems as $subitem) {
                    if ($id == $subitem['id']) {
                        Arr::delete($items, $i);
                    }
                }
            }
        }
        //join
        foreach ($subitems as $item) {
            $count = count($items);
            $order = $item['order'];
            if (($order < 0) || ($order >= $count)) {
                $items[] = $item;
            } else {
                Arr::insert($items, $item, $order);
            }
        }

        return $items;
    }

    protected function getSidebarContent(\ArrayObject $items, $sidebar, $disableajax)
    {
        $result = '';
        //for call event  getwidget
        $str = new Str();

        foreach ($items as $item) {
            $id = $item['id'];
            if (!isset($this->items[$id])) {
                continue;
            }

            $cachetype = $this->items[$id]['cache'];
            if ($disableajax) {
                $item['ajax'] = false;
            }

            if ($item['ajax'] === 'inline') {
                switch ($cachetype) {
                    case 'cache':
                    case 'nocache':
                    case false:
                        $content = $this->getInline($id, $sidebar);
                        break;


                    default:
                        $content = $this->getAjax($id, $sidebar);
                        break;
                }
            } elseif ($item['ajax']) {
                $content = $this->getAjax($id, $sidebar);
            } else {
                switch ($cachetype) {
                    case 'cache':
                        $content = Cache::i()->getContent($id, $sidebar, false);
                        break;


                    case 'include':
                        $content = $this->includeWidget($id, $sidebar);
                        break;


                    case 'nocache':
                    case false:
                        $widget = $this->getWidget($id);
                        $content = $widget->getWidget($id, $sidebar);
                        break;


                    case 'code':
                        $content = $this->getCode($id, $sidebar);
                        break;
                }
            }

            $str->value = $content;
            $this->onwidget($id, $str);
            $result.= $str->value;
        }

        return $result;
    }

    public function getAjax($id, $sidebar)
    {
        $view = new View();
        $title = $view->getAjax($id, $this->items[$id]['title'], $sidebar, 'ajaxwidget');
        $content = "<!--widgetcontent-$id-->";
        return $view->getWidgetId($id, $title, $content, $this->items[$id]['template'], $sidebar);
    }

    public function getInline($id, $sidebar)
    {
        $view = new View();
        $title = $view->getAjax($id, $this->items[$id]['title'], $sidebar, 'inlinewidget');
        if ('cache' == $this->items[$id]['cache']) {
            $cache = Cache::i();
            $content = $cache->getContent($id, $sidebar);
        } else {
            $widget = $this->getWidget($id);
            $content = $widget->getContent($id, $sidebar);
        }

        $content = sprintf('<!--%s-->', $content);
        return $view->getWidgetId($id, $title, $content, $this->items[$id]['template'], $sidebar);
    }

    private function includeWidget($id, $sidebar)
    {
        $filename = Widget::getCacheFilename($id, $sidebar);
        $cache = $this->getApp()->cache;
        if (!$cache->exists($filename)) {
            $widget = $this->getWidget($id);
            $content = $widget->getContent($id, $sidebar);
            $cache->setString($filename, $content);
        }

        $view = new View();
        return $view->getWidgetId($id, $this->items[$id]['title'], "\n<?php echo litepubl::\$app->cache->getString('$filename'); ?>\n", $this->items[$id]['template'], $sidebar);
    }

    private function getCode($id, $sidebar)
    {
        $class = $this->items[$id]['class'];
        return "\n<?php
    \$widget = $class::i();
    \$widget->id = \$id;
    echo \$widget->getwidget($id, $sidebar);
    ?>\n";
    }

    public function find(Widget $widget)
    {
        $class = get_class($widget);
        foreach ($this->items as $id => $item) {
            if ($class == $item['class']) {
                return $id;
            }
        }
        return false;
    }

    public function getWidgetContent($id, $sidebar)
    {
        if (!isset($this->items[$id])) {
            return false;
        }

        switch ($this->items[$id]['cache']) {
            case 'cache':
                $cache = Cache::i();
                $result = $cache->getcontent($id, $sidebar);
                break;


            case 'include':
                $filename = Widget::getCacheFilename($id, $sidebar);
                $result = $this->getApp()->cache->getString($filename);
                if (!$result) {
                    $widget = $this->getWidget($id);
                    $result = $widget->getContent($id, $sidebar);
                    $this->getApp()->cache->setString($filename, $result);
                }
                break;


            case 'nocache':
            case 'code':
            case false:
                $widget = $this->getwidget($id);
                $result = $widget->getcontent($id, $sidebar);
                break;
        }

        return $result;
    }

    public function getPos($id)
    {
        return Sidebars::getpos($this->sidebars, $id);
    }

    public function &finditem($id)
    {
        foreach ($this->classes as $class => $items) {
            foreach ($items as $i => $item) {
                if ($id == $item['id']) {
                    return $this->classes[$class][$i];
                }
            }
        }
        $item = null;
        return $item;
    }

    public function findContext($class)
    {
        $app = $this->getApp();
        if ($app->context->view instanceof $class) {
            return $app->context->view;
        } elseif ($app->context->model instanceof $class) {
            return $app->context->model;
        }

        if (is_callable($this->onFindContextCallback)) {
            return call_user_func_array($this->onFindContextCallback, [$class]);
        }

        return false;
    }
}

//WidgetsInterface.php
namespace litepubl\widget;

use ArrayObject;
use litepubl\core\Str;

interface WidgetsInterface
{
    public function getWidgets(ArrayObject $items, $sidebar);
    public function getSidebar(Str $str, $sidebar);
}

//Widget.php
namespace litepubl\widget;

use litepubl\view\Schema;
use litepubl\view\Theme;
use litepubl\view\Vars;

class Widget extends \litepubl\core\Events
{
    public $id;
    public $template;
    protected $adminclass;
    protected $adminInstance;

    protected function create()
    {
        parent::create();
        $this->basename = 'widget';
        $this->cache = 'cache';
        $this->id = 0;
        $this->template = 'widget';
        $this->adminclass = '\litepubl\admin\widget\Widget';
    }

    public function addToSidebar($sidebar)
    {
        $widgets = Widgets::i();
        $id = $widgets->add($this);
        $sidebars = Sidebars::i();
        $sidebars->insert($id, false, $sidebar, -1);

        $this->getApp()->cache->clear();
        return $id;
    }

    protected function getAdmin()
    {
        if (!$this->adminInstance) {
            $this->adminInstance = $this->getApp()->classes->getinstance($this->adminclass);
            $this->adminInstance->widget = $this;
        }

        return $this->adminInstance;
    }

    public function getWidget($id, $sidebar)
    {
        $vars = new Vars();
        $vars->widget = $this;

        try {
            $title = $this->gettitle($id);
            $content = $this->getcontent($id, $sidebar);
        } catch (\Exception $e) {
            $this->getApp()->logException($e);
            return '';
        }
        $view = new View();
        return $view->getWidgetId($id, $title, $content, $this->template, $sidebar);
    }

    public function getDeftitle()
    {
        return '';
    }

    public function getTitle($id)
    {
        if (!isset($id)) {
            $this->error('no id');
        }
        $widgets = Widgets::i();
        if (isset($widgets->items[$id])) {
            return $widgets->items[$id]['title'];
        }
        return $this->getdeftitle();
    }

    public function setTitle($id, $title)
    {
        $widgets = Widgets::i();
        if (isset($widgets->items[$id]) && ($widgets->items[$id]['title'] != $title)) {
            $widgets->items[$id]['title'] = $title;
            $widgets->save();
        }
    }

    public function getContent($id, $sidebar)
    {
        return '';
    }

    public static function getCachefilename($id)
    {
        $theme = Theme::context();
        return sprintf('widget.%s.%d.php', $theme->name, $id);
    }

    public function expired($id)
    {
        switch ($this->cache) {
            case 'cache':
                Cache::i()->expired($id);
                break;


            case 'include':
                $sidebar = static ::findsidebar($id);
                $filename = static ::getCacheFilename($id, $sidebar);
                $this->getApp()->cache->setString($filename, $this->getContent($id, $sidebar));
                break;
        }
    }

    public static function findsidebar($id)
    {
        $schema = Schema::i();
        foreach ($schema->sidebars as $i => $sidebar) {
            foreach ($sidebar as $item) {
                if ($id == $item['id']) {
                    return $i;
                }
            }
        }

        return 0;
    }

    public function expire()
    {
        $widgets = Widgets::i();
        foreach ($widgets->items as $id => $item) {
            if ($this instanceof $item['class']) {
                $this->expired($id);
            }
        }
    }
}

//CommonTags.php
namespace litepubl\widget;

class CommonTags extends Widget
{

    protected function create()
    {
        parent::create();
        $this->adminclass = '\litepubl\admin\widget\Tags';
        $this->data['sortname'] = 'count';
        $this->data['showcount'] = true;
        $this->data['showsubitems'] = true;
        $this->data['maxcount'] = 0;
    }

    public function getOwner()
    {
        return false;
    }

    public function getContent($id, $sidebar)
    {
        $view = new View();
        $items = $this->owner->getView()->getSorted(array(
            'item' => $view->getItem($this->template, $sidebar) ,
            'subcount' => $view->getTml($sidebar, $this->template, 'subcount') ,
            'subitems' => $this->showsubitems ? $view->getTml($sidebar, $this->template, 'subitems') : ''
        ), 0, $this->sortname, $this->maxcount, $this->showcount);

        return str_replace('$parent', 0, $view->getContent($items, $this->template, $sidebar));
    }
}
