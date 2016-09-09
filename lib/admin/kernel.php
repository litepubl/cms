<?php
//AdminInterface.php
namespace litepubl\admin;

interface AdminInterface
{
    public function getContent(): string;
    public function processForm();
}

//AuthorRights.php
namespace litepubl\admin;

/**
 *  Events for author rights on post, files
 *
 * @property-write callable $onCan
 * @property-write callable $add
 * @property-write callable $status
 * @property-write callable $upload
 * @property-write callable $deleteFile
 * @method         array onCan(array $params)
 * @method         array add(array $params)
 * @method         array status(array $params)
 * @method         array upload(array $params)
 * @method         array deleteFile(array $params)
 */

class AuthorRights extends \litepubl\core\Events
{
    public $message = '';
    public $result = true;

    public static function __callStatic( string $name , array $args)
    {
        return static::i()->can($name, count($args) ? $args[0] : null);
    }

    public static function getMessage(): string
    {
        return static::i()->message;
    }

    protected function create()
    {
        parent::create();
        $this->basename = 'authorrights';
        $this->addEvents('onCan', 'add', 'status', 'upload', 'deleteFile');
    }

    public function can(string $action, $arg = null): bool
    {
        $action = strtolower($action);
        if (Str::begin($action, 'can')) {
            $action = substr($action, 3);
        }

        if (!in_array($name, $this->eventnames)) {
            $this->error(sprintf('Unknown % action', $action));
        }

        if ($name != 'oncan') {
            $r = $this->callEvent(
                'oncan', [
                'result' => true,
                'message' => '',
                'action' => $name,
                'arg' => $arg,
                ]
            );

            if ($r['result']) {
                $r = $this->callEvent(
                    $name, [
                    'result' => true,
                    'message' => '',
                    'arg' => $arg,
                    ]
                );
            }
        } else {
            $r = $this->callEvent(
                $name, [
                'result' => true,
                'message' => '',
                'arg' => $arg,
                ]
            );
        }

        $this->result = $r['result'];
        $this->message = $r['message'];
        return $r['result'];
    }
}

//DateFilter.php
namespace litepubl\admin;

class DateFilter
{
    //only date without time
    public static $format = 'd.m.Y';
    public static $timeformat = 'H:i';

    public static function timestamp($date)
    {
        if (is_numeric($date)) {
            $date = (int)$date;
        } elseif ($date == '0000-00-00 00:00:00') {
            $date = 0;
        } elseif ($date == '0000-00-00') {
            $date = 0;
        } elseif ($date = trim($date)) {
            $date = strtotime($date);
        } else {
            $date = 0;
        }

        return $date;
    }

    public static function getDate($name, $format = false)
    {
        if (empty($_POST[$name])) {
            return 0;
        }

        $date = trim($_POST[$name]);
        if (!$date) {
            return 0;
        }
        if (!$format) {
            $format = static ::$format;
        }

        $d = \DateTime::createFromFormat($format, $date);
        if ($d && $d->format($format) == $date) {
            $d->setTime(0, 0, 0);
            return $d->getTimestamp() + static ::gettime($name . '-time');
        }

        return 0;
    }

    public static function getTime($name)
    {
        $result = 0;
        if (!empty($_POST[$name]) && ($time = trim($_POST[$name]))) {
            if (preg_match('/^([01]?[0-9]|2[0-3]):([0-5][0-9])(:([0-5][0-9]))?$/', $time, $m)) {
                $result = intval($m[1]) * 3600 + intval($m[2]) * 60;
                if (isset($m[4])) {
                    $result+= (int)$m[4];
                }
            }
        }

        return $result;
    }
}

//Factory.php
namespace litepubl\admin;

use litepubl\view\Args;
use litepubl\view\Lang;

trait Factory
{

    public function getLang(string $name = ''): Lang
    {
        return Lang::admin($name);
    }

    public function newTable($admin = null): Table
    {
        return new Table($admin ? $admin : $this->admintheme);
    }

    public function tableItems(array $items, array $struct): string
    {
        $table = $this->newTable();
        $table->setStruct($struct);
        return $table->build($items);
    }

    public function newList(): UList
    {
        return new UList($this->admintheme);
    }

    public function newTabs(): Tabs
    {
        return new Tabs($this->admintheme);
    }

    public function newForm($args = null): Form
    {
        return new Form($args ? $args : new Args());
    }

    public function newArgs(): Args
    {
        return new Args();
    }

    public function getNotfound(): string
    {
        return $this->admintheme->geterr(Lang::i()->notfound);
    }

    public function getFrom(int $perpage, int $count): int
    {
        if ($this->getApp()->context->request->page <= 1) {
            return 0;
        }

        return min($count, ($this->getApp()->context->request->page - 1) * $perpage);
    }

    public function confirmDelete($id, $mesg = false)
    {
        $args = new Args();
        $args->id = $id;
        $args->action = 'delete';
        $args->adminurl = $this->adminurl;
        $args->confirm = $mesg ? $mesg : Lang::i()->confirmdelete;

        $admin = $this->admintheme;
        return $admin->parseArg($admin->templates['confirmform'], $args);
    }

    public function confirmDeleteItem($owner)
    {
        $id = (int)$this->getparam('id', 0);
        $admin = $this->admintheme;
        $lang = Lang::i();

        if (!$owner->itemExists($id)) {
            return $admin->geterr($lang->notfound);
        }

        if (isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1)) {
            $owner->delete($id);
            return $admin->success($lang->successdeleted);
        }

        $args = new Args();
        $args->id = $id;
        $args->adminurl = $this->adminurl;
        $args->action = 'delete';
        $args->confirm = $lang->confirmdelete;
        return $admin->parseArg($admin->templates['confirmform'], $args);
    }

    /*
    * method can used as: extract($this->getStdInstances());
    */
    public function getStdInstances(): array
    {
        return [
        'result' => '',
        'lang' => $this->getLang(),
        'admin' => $this->adminTheme,
        'theme' => $this->theme,
        'args' => $this->newArgs(),
        'id' => $this->idGet(),
        ];
    }
}

//FileList.php
namespace litepubl\admin;

use litepubl\core\Str;
use litepubl\post\Files;
use litepubl\view\Admin;
use litepubl\view\Args;

class FileList
{
    public $onFilePerm;
    private $adminTheme;

    public function __construct(Admin $adminTheme)
    {
        $this->adminTheme = $adminTheme;
    }

    public function getFiles()
    {
        return Files::i();
    }

    public function get(array $list): string
    {
        $files = $this->getFiles();
        $app = $this->adminTheme->getApp();
        $args = new Args();
        $args->fileperm = '';

        if (is_callable($this->onFilePerm)) {
            call_user_func_array($this->onFilePerm, [$args]);
        } elseif ($app->options->show_file_perm) {
            $args->fileperm = GetPerm::combo(0, 'idperm_upload');
        }

        $where = $app->options->inGroup('editor') ? '' : ' and author = ' . $app->options->user;
        $db = $files->db;
        //total count files
        $args->count = (int)$db->getcount(" parent = 0 $where");
        //already loaded files
        $args->items = '{}';
        // attrib for hidden input
        $args->files = '';

        if (count($list)) {
            $items = implode(',', $list);
            $args->files = $items;
            $args->items = Str::toJson($db->res2items($db->query("select * from $files->thistable where id in ($items) or parent in ($items)")));
        }

        return $this->adminTheme->parseArg($this->adminTheme->templates['posteditor.filelist'], $args);
    }

}

//Form.php
namespace litepubl\admin;

use litepubl\view\Admin;
use litepubl\view\Lang;
use litepubl\view\Theme;

class Form
{
    public $args;
    public $title;
    public $before;
    public $body;
    //items deprecated
    public $items;
    public $submit;
    public $inline;

    //attribs for <form>
    public $action;
    public $method;
    public $enctype;
    public $id;
    public $class;
    public $target;

    public function __construct($args = null)
    {
        $this->args = $args;
        $this->title = '';
        $this->before = '';
        $this->body = '';
        $this->items = & $this->body;
        $this->submit = 'update';
        $this->inline = false;

        $this->action = '';
        $this->method = 'post';
        $this->enctype = '';
        $this->id = '';
        $this->class = '';
        $this->target = '';
    }

    public function line(string $content): string
    {
        return str_replace('$content', $content, $this->getAdminTheme()->templates['inline']);
    }

    public function getAdmintheme(): Admin
    {
        return Admin::i();
    }

    public function __set(string $k, $v)
    {
        switch ($k) {
        case 'upload':
            if ($v) {
                $this->enctype = 'multipart/form-data';
                $this->submit = 'upload';
            } else {
                $this->enctype = '';
                $this->submit = 'update';
            }
            break;
        }
    }

    public function centerGroup(string $buttons): string
    {
        return str_replace('$buttons', $buttons, $this->getAdminTheme()->templates['centergroup']);
    }

    public function hidden(string $name, string $value): string
    {
        return sprintf('<input type="hidden" name="%s" value="%s" />', $name, $value);
    }

    public function getDelete(string $table): string
    {
        $this->body = $table;
        $this->body.= $this->hidden('action', 'delete');
        $this->submit = 'delete';

        return $this->get();
    }

    public function __tostring()
    {
        return $this->get();
    }

    public function getTml(): string
    {
        $admin = $this->getadmintheme();
        $title = $this->title ? str_replace('$title', $this->title, $admin->templates['form.title']) : '';

        $attr = "action=\"$this->action\"";
        foreach ([
            'method',
            'enctype',
            'target',
            'id',
            'class'
        ] as $k) {
            if ($v = $this->$k) {
                $attr.= sprintf(' %s="%s"', $k, $v);
            }
        }

        $theme = Theme::i();
        $lang = Lang::i();
        $body = $this->body;

        if ($this->inline) {
            if ($this->submit) {
                $body.= $theme->getInput('button', $this->submit, '', $lang->__get($this->submit));
            }

            $body = $this->line($body);
        } else {
            if ($this->submit) {
                $body.= $theme->getInput('submit', $this->submit, '', $lang->__get($this->submit));
            }
        }

        return strtr(
            $admin->templates['form'], [
            '$title' => $title,
            '$before' => $this->before,
            '$attr' => $attr,
            '$body' => $body,
            ]
        );
    }

    public function get(): string
    {
        return $this->getAdminTheme()->parseArg($this->getTml(), $this->args);
    }

    public function getButtons(): string
    {
        $result = '';
        $theme = Theme::i();
        $lang = Lang::i();

        $a = func_get_args();
        foreach ($a as $name) {
            $result.= strtr(
                $theme->templates['content.admin.button'], [
                '$lang.$name' => $lang->__get($name) ,
                '$name' => $name,
                ]
            );
        }

        return $result;
    }
}

//GetPerm.php
namespace litepubl\admin;

use litepubl\core\UserGroups;
use litepubl\perms\Perms;
use litepubl\view\Admin;
use litepubl\view\Lang;
use litepubl\view\Theme;

class GetPerm
{

    public static function combo($idperm, $name = 'idperm')
    {
        $lang = Lang::admin();
        $section = $lang->section;
        $lang->section = 'perms';
        $theme = Theme::i();
        $result = strtr(
            $theme->templates['content.admin.combo'], [
            '$lang.$name' => $lang->perm,
            '$name' => $name,
            '$value' => static ::items($idperm)
            ]
        );

        $lang->section = $section;
        return $result;
    }

    public static function items($idperm)
    {
        $result = sprintf('<option value="0" %s>%s</option>', $idperm == 0 ? 'selected="selected"' : '', Lang::get('perms', 'nolimits'));
        $perms = Perms::i();
        foreach ($perms->items as $id => $item) {
            $result.= sprintf('<option value="%d" %s>%s</option>', $id, $idperm == $id ? 'selected="selected"' : '', $item['name']);
        }

        return $result;
    }

    public static function groups(array $idgroups)
    {
        $result = '';
        $groups = UserGroups::i();
        $theme = Theme::context();

        foreach ($groups->items as $id => $item) {
            $checked = in_array($id, $idgroups) ? 'checked="checked"' : '';
            $result .= $theme->getInput('checkbox', "idgroup-$id", "value=\"$id\" $checked", $item['title']);

        }

        return $result;
    }
}

//GetSchema.php
namespace litepubl\admin;

use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Schemes;
use litepubl\view\Theme;

class GetSchema
{
    use \litepubl\core\AppTrait;

    public static function form($url)
    {
        Lang::admin();
        $args = new Args();
        $id = !empty($_GET['idschema']) ? (int)$_GET['idschema'] : (!empty($_POST['idschema']) ? (int)$_POST['idschema'] : 0);
        $args->idschema = static ::items($id);
        $form = new Form($args);
        $form->action = static ::getAppInstance()->site->url . $url;
        $form->inline = true;
        $form->method = 'get';
        $form->body = '[combo=idschema]';
        $form->submit = 'select';
        return $form->get();
    }

    public static function combo($idschema, $name = 'idschema')
    {
        $lang = Lang::admin();
        $lang->addsearch('views');
        $theme = Theme::i();
        return strtr(
            $theme->templates['content.admin.combo'], [
            '$lang.$name' => $lang->schema,
            '$name' => $name,
            '$value' => static ::items($idschema)
            ]
        );
    }

    public static function items($idschema)
    {
        $result = '';
        $schemes = schemes::i();
        foreach ($schemes->items as $id => $item) {
            $result.= sprintf('<option value="%d" %s>%s</option>', $id, $idschema == $id ? 'selected="selected"' : '', $item['name']);
        }

        return $result;
    }
}

//Link.php
namespace litepubl\admin;

use litepubl\core\Str;
use litepubl\view\Admin as AdminTheme;

class Link
{
    use \litepubl\core\AppTrait;

    public static function url($path, $params = false)
    {
        $site = static ::getAppInstance()->site;
        if ($params) {
            return $site->url . $path . $site->q . $params;
        } else {
            return $site->url . str_replace('?', $site->q, $path);
        }
    }

    public static function parse(string $s): string
    {
        $list = explode(',', $s);
        $a = [];
        foreach ($list as $item) {
            if ($i = strpos($item, '=')) {
                $a[trim(substr($item, 0, $i)) ] = trim(substr($item, $i + 1));
            } else {
                $a['text'] = trim($item);
            }
        }

        $site = static ::getAppInstance()->site;
        $a['href'] = str_replace('?', $site->q, $a['href']);
        if (!Str::begin($a['href'], 'http')) {
            $a['href'] = $site->url . $a['href'];
        }

        if (isset($a['icon'])) {
            $a['text'] = AdminTheme::admin()->getIcon($a['icon']) . (empty($a['text']) ? '' : ' ' . $a['text']);
        }

        if (isset($a['tooltip'])) {
            $a['title'] = $a['tooltip'];
            $a['class'] = empty($a['class']) ? 'tooltip-toggle' : $a['class'] . ' tooltip-toggle';
        }

        $attr = '';
        foreach ([
            'class',
            'title',
            'role'
        ] as $name) {
            if (!empty($a[$name])) {
                $attr.= sprintf(' %s="%s"', $name, $a[$name]);
            }
        }

        return sprintf('<a href="%s"%s>%s</a>', $a['href'], $attr, $a['text']);
    }
}

//Menu.php
namespace litepubl\admin;

use litepubl\core\Context;
use litepubl\core\UserGroups;
use litepubl\view\Lang;
use litepubl\view\Schemes;

class Menu extends \litepubl\pages\Menu
{
    use Factory;
    use Params;

    public static $adminownerprops = [
        'title',
        'url',
        'idurl',
        'parent',
        'order',
        'status',
        'name',
        'group'
    ];

    public static function getInstanceName()
    {
        return 'adminmenu';
    }

    public static function getOwner()
    {
        return Menus::i();
    }

    public function get_owner_props()
    {
        return static ::$adminownerprops;
    }

    public function load()
    {
        return true;
    }

    public function save()
    {
        return true;
    }

    public function getHead(): string
    {
        return Menus::i()->heads;
    }

    public function getIdSchema(): int
    {
        return Schemes::i()->defaults['admin'];
    }

    public function auth(Context $context, $group)
    {
        if ($context->checkAttack()) {
            return;
        }

        $response = $context->response;
        $options = $this->getApp()->options;
        if (!$options->user) {
            $response->cache = false;
            $response->redir('/admin/login/' . $this->getApp()->site->q . 'backurl=' . urlencode($context->request->url));
            return;
        }

        if (!$options->hasGroup($group)) {
            $url = UserGroups::i()->gethome($options->group);
            $response->cache = false;
            $response->redir($url);
            return;
        }
    }

    public function request(Context $context)
    {
        error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING);
        ini_set('display_errors', 1);
        $id = $context->id;
        if (is_null($id)) {
            $id = $this->owner->class2id(get_class($this));
        }

        $this->data['id'] = (int)$id;
        if ($id > 0) {
            $this->basename = $this->parent == 0 ? $this->name : $this->owner->items[$this->parent]['name'];
        }

        $this->auth($context, $this->group);
        if ($context->response->status != 200) {
            return;
        }

        Lang::usefile('admin');
        if ($status = $this->canRequest()) {
            $context->response->status = $status;
            return;
        }

        $this->doProcessForm();
    }

    public function canRequest()
    {
        return false;
    }

    protected function doProcessForm()
    {
        if (isset($_POST) && count($_POST)) {
            $this->getApp()->cache->clear();
        }

        return parent::doProcessForm();
    }

    public function getCont(): string
    {
        $app = $this->getApp();
        if ($app->options->admincache) {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $filename = 'adminmenu.' . $app->options->user . '.' . md5($_SERVER['REQUEST_URI'] . '&id=' . $id) . '.php';
            if ($result = $app->cache->getString($filename)) {
                return $result;
            }

            $result = parent::getcont();
            $app->cache->setString($filename, $result);
            return $result;
        } else {
            return parent::getCont();
        }
    }

    public function getAdminurl(): string
    {
        return $this->getApp()->site->url . $this->url . $this->getApp()->site->q . 'id';
    }

    public function getLang(string $name = ''): Lang
    {
        return Lang::i($name ? $name : ($this->name ? $this->name : ''));
    }
}

//Menus.php
namespace litepubl\admin;

use litepubl\core\UserGroups;
use litepubl\pages\Menu as StdMenu;
use litepubl\view\Lang;

/**
 * Admin menu items
 *
 * @property       string $heads
 * @property-write callable $onExclude
 * @method         array onExclude(array $params)
 */

class Menus extends \litepubl\pages\Menus
{

    protected function create()
    {
        parent::create();
        $this->basename = 'adminmenu';
        $this->addEvents('onexclude');
        $this->data['heads'] = '';
    }

    public function setTitle($id, $title)
    {
        if ($id && isset($this->items[$id])) {
            $this->items[$id]['title'] = $title;
            $this->save();
            $this->getApp()->cache->clear();
        }
    }

    public function getDir(): string
    {
        return $this->getApp()->paths->data . 'adminmenus' . DIRECTORY_SEPARATOR;
    }

    public function getAdmintitle($name)
    {
        $lang = Lang::i();
        $ini = & $lang->ini;
        if (isset($ini[$name]['title'])) {
            return $ini[$name]['title'];
        }

        Lang::usefile('install');
        if (!in_array('adminmenus', $lang->searchsect)) {
            array_unshift($lang->searchsect, 'adminmenus');
        }

        if ($result = $lang->__get($name)) {
            return $result;
        }

        return $name;
    }

    public function createUrl(int $parent, string $name): string
    {
        return $parent == 0 ? "/admin/$name/" : $this->items[$parent]['url'] . "$name/";
    }

    public function createItem(int $parent, string $name, string $group, string $class): int
    {
        $title = $this->getAdminTitle($name);
        $url = $this->createUrl($parent, $name);
        return $this->addItem(
            [
            'parent' => $parent,
            'url' => $url,
            'title' => $title,
            'name' => $name,
            'class' => $class,
            'group' => $group
            ]
        );
    }

    public function addItem(array $item): int
    {
        if (empty($item['group'])) {
            $groups = UserGroups::i();
            $item['group'] = $groups->items[$groups->defaults[0]]['name'];
        }
        return parent::addItem($item);
    }

    public function addFakeMenu(StdMenu $menu): int
    {
        $this->lock();
        $id = parent::addfakemenu($menu);
        if (empty($this->items[$id]['group'])) {
            $groups = UserGroups::i();
            $group = count($groups->defaults) ? $groups->items[$groups->defaults[0]]['name'] : 'commentator';
            $this->items[$id]['group'] = $group;
        }

        $this->unlock();
        return $id;
    }

    public function getChilds(int $id): array
    {
        if ($id == 0) {
            $result = [];
            $options = $this->getApp()->options;
            foreach ($this->tree as $iditem => $items) {
                if ($options->hasgroup($this->items[$iditem]['group'])) {
                    $result[] = $iditem;
                }
            }
            return $result;
        }

        $parents = [
            $id
        ];
        $parent = $this->items[$id]['parent'];
        while ($parent != 0) {
            array_unshift($parents, $parent);
            $parent = $this->items[$parent]['parent'];
        }

        $tree = $this->tree;
        foreach ($parents as $parent) {
            foreach ($tree as $iditem => $items) {
                if ($iditem == $parent) {
                    $tree = $items;
                    break;
                }
            }
        }
        return array_keys($tree);
    }

    public function exclude(int $id): bool
    {
        if (!$this->getApp()->options->hasGroup((string) $this->items[$id]['group'])) {
            return true;
        }

        $r = $this->onExclude(['id' => $id, 'exclude' => false]);
        return $r['exclude'];
    }
}

//Panel.php
namespace litepubl\admin;

class Panel implements AdminInterface
{
            use PanelTrait;
    use Params;
    use \litepubl\core\AppTrait;

    public function __construct()
    {
        $this->createInstances($this->getSchema());
    }

    public function getContent(): string
    {
    }

    public function processForm()
    {
    }
}

//PanelTrait.php
namespace litepubl\admin;

use litepubl\core\Plugins as PluginItems;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Schema;
use litepubl\view\Schemes;

trait PanelTrait
{
    public $admin;
    public $theme;
    public $lang;
    public $args;

    public function createInstances(Schema $schema)
    {
        $this->admin = $schema->admintheme;
        $this->theme = $schema->theme;
        $this->lang = Lang::admin();
        $this->args = new Args();
    }

    public function getSchema(): Schema
    {
        $app = $this->getApp();
        if (isset($app->context) && isset($app->context->view)) {
            return Schema::getSchema($app->context->view);
        } else {
            return Schema::i(Schemes::i()->defaults['admin']);
        }
    }

    public function getLangAbout(): Lang
    {
        $reflector = new \ReflectionClass($this);
        $filename = $reflector->getFileName();
        return PluginItems::getLangAbout($filename);
    }
}

//Params.php
namespace litepubl\admin;

trait Params
{

    public function idGet()
    {
        return (int)$this->getparam('id', 0);
    }

    public function getParam($name, $default)
    {
        return !empty($_GET[$name]) ? $_GET[$name] : (!empty($_POST[$name]) ? $_POST[$name] : $default);
    }

    public function idParam()
    {
        return (int)$this->getparam('id', 0);
    }

    public function getAction()
    {
        return isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
    }

    public function getConfirmed()
    {
        return isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1);
    }
}

//Table.php
namespace litepubl\admin;

use litepubl\core\Items;
use litepubl\post\Post;
use litepubl\view\Admin;
use litepubl\view\Args;
use litepubl\view\Base;
use litepubl\view\Lang;

class Table
{
    const LEFT = 'text-left';
    const RIGHT = 'text-right';
    const CENTER = 'text-center';

    //current item in items
    public $item;
    //id or index of current item
    public $id;
    //template head and body table
    public $head;
    public $body;
    public $footer;
    //targs
    public $args;
    public $data;
    public $admintheme;
    public $callbacks;

    public static function fromItems(array $items, array $struct): string
    {
        $classname = get_called_class();
        $self = new $classname();
        return $self->buildItems($items, $struct);
    }

    public function __construct()
    {
        $this->head = '';
        $this->body = '';
        $this->footer = '';
        $this->callbacks = [];
        $this->args = new Args();
        $this->data = [];
    }

    public function setStruct(array $struct)
    {
        $this->head = '';
        $this->body = '<tr>';

        foreach ($struct as $index => $item) {
            if (!$item || !count($item)) {
                continue;
            }

            if (count($item) == 2) {
                $colclass = 'text-left';
            } else {
                $colclass = static ::getcolclass(array_shift($item));
            }

            $this->head.= sprintf('<th class="%s">%s</th>', $colclass, array_shift($item));

            $s = array_shift($item);
            if (is_string($s)) {
                $this->body.= sprintf('<td class="%s">%s</td>', $colclass, $s);
            } elseif (is_callable($s)) {
                $name = '$callback' . $index;
                $this->body.= sprintf('<td class="%s">%s</td>', $colclass, $name);

                array_unshift($item, $this);
                $this->callbacks[$name] = [
                    'callback' => $s,
                    'params' => $item,
                ];
            } else {
                throw new Exception('Unknown column ' . var_export($s, true));
            }
        }

        $this->body.= '</tr>';
    }

    public function addCallback(string $varname, $callback, $param = null)
    {
        $this->callbacks[$varname] = [
            'callback' => $callback,
            'params' => [
                $this,
                $param
            ] ,
        ];
    }

    public function addFooter(string $footer)
    {
        $this->footer = sprintf('<tfoot><tr>%s</tr></tfoot>', $footer);
    }

    public function td(string $colclass, string $content): string
    {
        return sprintf('<td class="%s">%s</td>', static ::getcolclass($colclass), $content);
    }

    public function getAdmintheme(): Admin
    {
        if (!$this->admintheme) {
            $this->admintheme = Admin::i();
        }

        return $this->admintheme;
    }

    public function build(array $items): string
    {
        $body = '';

        foreach ($items as $id => $item) {
            $body.= $this->parseitem($id, $item);
        }

        return $this->getadmintheme()->gettable($this->head, $body, $this->footer);
    }

    public function parseItem($id, $item)
    {
        $args = $this->args;

        if (is_array($item)) {
            $this->item = $item;
            $args->add($item);
            if (!isset($item['id'])) {
                $this->id = $id;
                $args->id = $id;
            }
        } else {
            $this->id = $item;
            $args->id = $item;
        }

        foreach ($this->callbacks as $name => $callback) {
            $args->data[$name] = call_user_func_array($callback['callback'], $callback['params']);
        }

        return $this->getAdminTheme()->parseArg($this->body, $args);
    }

    public function buildItems(array $items, array $struct): string
    {
        $this->setStruct($struct);
        return $this->build($items);
    }

    //predefined callbacks
    public function itemsCallback(Table $self, Items $owner)
    {
        $self->item = $owner->getItem($self->id);
        $self->args->add($self->item);
    }

    public function setOwner(Items $owner)
    {
        $this->addCallback(
            '$tempcallback' . count($this->callbacks), [
            $this,
            'itemsCallback'
            ], $owner
        );
    }

    public function posts_callback(Table $self)
    {
        $post = Post::i($self->id);
        Base::$vars['post'] = $post->getView();
        $self->args->poststatus = Lang::i()->__get($post->status);
    }

    public function setPosts(array $struct)
    {
        array_unshift($struct, $this->checkbox('checkbox'));
        $this->setStruct($struct);
        $this->addCallback(
            '$tempcallback' . count($this->callbacks), [
            $this,
            'posts_callback'
            ], false
        );
    }

    public function props(array $props): string
    {
        $lang = Lang::i();
        $this->setStruct(
            [
            [
                $lang->name,
                '$name'
            ] ,

            [
                $lang->property,
                '$value'
            ]
            ]
        );

        $body = '';
        $args = $this->args;
        $admintheme = $this->getadmintheme();

        foreach ($props as $k => $v) {
            if (($k === false) || ($v === false)) {
                continue;
            }

            if (is_array($v)) {
                foreach ($v as $kv => $vv) {
                    if ($k2 = $lang->__get($kv)) {
                        $kv = $k2;
                    }
                    $args->name = $kv;
                    $args->value = $vv;
                    $body.= $admintheme->parseArg($this->body, $args);
                }
            } else {
                if ($k2 = $lang->__get($k)) {
                    $k = $k2;
                }

                $args->name = $k;
                $args->value = $v;
                $body.= $admintheme->parseArg($this->body, $args);
            }
        }

        return $admintheme->getTable($this->head, $body);
    }

    public function inputs(array $inputs): string
    {
        $lang = Lang::i();
        $this->setStruct(
            [
            [
                $lang->name,
                '<label for="$name-input">$title</label>'
            ] ,

            [
                $lang->property,
                '$input'
            ]
            ]
        );

        $body = '';
        $args = $this->args;
        $admintheme = $this->getadmintheme();

        foreach ($inputs as $name => $type) {
            if (($name === false) || ($type === false)) {
                {
                    continue;
                }
            }

            switch ($type) {
            case 'combo':
                $input = '<select name="$name" id="$name-input">$value</select>';
                break;


            case 'text':
                $input = '<input type="text" name="$name" id="$name-input" value="$value" />';
                break;


            default:
                $this->error('Unknown input type ' . $type);
            }

            $args->name = $name;
            $args->title = $lang->$name;
            $args->value = $args->$name;
            $args->input = $admintheme->parseArg($input, $args);
            $body.= $admintheme->parseArg($this->body, $args);
        }

        return $admintheme->getTable($this->head, $body);
    }

    public function action(string $action, string $adminurl): array
    {
        $title = Lang::i()->__get($action);

        return [
            $title,
            "<a href=\"$adminurl=\$id&action=$action\">$title</a>"
        ];
    }

    public function checkbox(string $name): array
    {
        $admin = $this->getadmintheme();

        return [
            'text-center col-checkbox',
            $admin->templates['checkbox.invert'],
            str_replace('$name', $name, $admin->templates['checkbox.id'])
        ];
    }

    public function nameCheck(): array
    {
        $admin = Admin::i();

        return [
            'text-center col-checkbox',
            $admin->templates['checkbox.stub'],
            $admin->templates['checkbox.name']
        ];
    }

    public static function getColclass($s): string
    {
        //most case
        if (!$s || $s == 'left') {
            return 'text-left';
        }

        $map = [
            'left' => 'text-left',
            'right' => 'text-right',
            'center' => 'text-center'
        ];

        $list = explode(' ', $s);
        foreach ($list as $i => $v) {
            if (isset($map[$v])) {
                $list[$i] = $map[$v];
            }
        }

        return implode(' ', $list);
    }

    public function date($date): string
    {
return Lang::i()->getDate($date);
    }

    public function dateTime($date): string
    {
return Lang::i()->getDateTime($date);
    }
}

//Tabs.php
namespace litepubl\admin;

use litepubl\view\Admin;

class Tabs
{
    public $tabs;
    public $panels;
    public $id;
    public $_admintheme;
    private static $index = 0;

    public function __construct($admintheme = null)
    {
        $this->_admintheme = $admintheme;
        $this->tabs = [];
        $this->panels = [];
    }

    public function getAdmintheme()
    {
        if (!$this->_admintheme) {
            $this->_admintheme = Admin::i();
        }

        return $this->_admintheme;
    }

    public function get()
    {
        return strtr(
            $this->getadmintheme()->templates['tabs'], [
            '$id' => $this->id ? $this->id : 'tabs-' . static ::$index++,
            '$tab' => implode("\n", $this->tabs) ,
            '$panel' => implode("\n", $this->panels) ,
            ]
        );
    }

    public function add($title, $content)
    {
        $this->addtab('', $title, $content);
    }

    public function ajax($title, $url)
    {
        $this->addtab($url, $title, '');
    }

    public function addtab($url, $title, $content)
    {
        $id = static ::$index++;
        $this->tabs[] = $this->gettab($id, $url, $title);
        $this->panels[] = $this->getpanel($id, $content);
    }

    public function getTab($id, $url, $title)
    {
        return strtr(
            $this->getadmintheme()->templates['tabs.tab'], [
            '$id' => $id,
            '$title' => $title,
            '$url' => $url,
            ]
        );
    }

    public function getPanel($id, $content)
    {
        return strtr(
            $this->getadmintheme()->templates['tabs.panel'], [
            '$id' => $id,
            '$content' => $content,
            ]
        );
    }
}

//UList.php
namespace litepubl\admin;

class UList
{
    use \litepubl\core\AppTrait;

    const aslinks = true;
    public $ul;
    public $item;
    public $link;
    public $value;
    public $result;

    public function __construct($admin = null, $islink = false)
    {
        if ($admin) {
            $this->ul = $admin->templates['list'];
            $this->item = $admin->templates['list.item'];
            $this->link = $admin->templates['list.link'];
            $this->value = $admin->templates['list.value'];

            if ($islink == static ::aslinks) {
                $this->item = $this->link;
            }
        }

        $this->result = '';
    }

    public function li($name, $value)
    {
        return strtr(
            is_int($name) ? $this->value : $this->item, [
            '$name' => $name,
            '$value' => $value,
            '$site.url' => $this->getApp()->site->url,
            ]
        );
    }

    public function link($url, $title)
    {
        return strtr(
            $this->link, [
            '$name' => $url,
            '$value' => $title,
            ]
        );
    }

    public function ul($items)
    {
        return str_replace('$item', $items, $this->ul);
    }

    public function getResult()
    {
        return $this->ul($this->result);
    }

    public function add($name, $value)
    {
        $this->result.= $this->li($name, $value);
    }

    public function get(array $props)
    {
        $result = '';
        foreach ($props as $name => $value) {
            if ($value === false) {
                continue;
            }

            if (is_array($value)) {
                $value = $this->get($value);
            }

            $result.= $this->li($name, $value);
        }

        if ($result) {
            return $this->ul($result);
        }

        return '';
    }

    public function links(array $props)
    {
        $this->item = $this->link;
        $result = $this->get($props);
        return str_replace('$site.url', $this->getApp()->site->url, $result);
    }

public function props2links(array $props): array
{
$result = [];
foreach ($props as $url => $title) {
$result[] = sprintf('<a href="%s">%s</a>', $url, $title);
}

return $result;
}
}

