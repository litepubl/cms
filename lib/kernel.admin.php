<?php
//theme.admin.class.php
namespace litepubl;

class admintheme extends basetheme {
    public $onfileperm;

    public static function i() {
        $result = getinstance(get_called_class());
        if (!$result->name && ($context = litepubl::$urlmap->context) && isset($context->idview)) {
            $result->name = tview::getview($context)->adminname;
            $result->load();
        }

        return $result;
    }

    public static function admin() {
        return tview::i(tviews::i()->defaults['admin'])->admintheme;
    }

    public function getparser() {
        return adminparser::i();
    }

    public function shortcode($s, targs $args) {
        $result = trim($s);
        //replace [tabpanel=name{content}]
        if (preg_match_all('/\[tabpanel=(\w*+)\{(.*?)\}\]/ims', $result, $m, PREG_SET_ORDER)) {
            foreach ($m as $item) {
                $name = $item[1];
                $replace = strtr($this->templates['tabs.panel'], array(
                    '$id' => $name,
                    '$content' => trim($item[2]) ,
                ));

                $result = str_replace($item[0], $replace, $result);
            }
        }

        if (preg_match_all('/\[(editor|text|email|password|upload|checkbox|combo|hidden|submit|button|calendar|tab|ajaxtab|tabpanel)[:=](\w*+)\]/i', $result, $m, PREG_SET_ORDER)) {
            $theme = ttheme::i();
            $lang = tlocal::i();

            foreach ($m as $item) {
                $type = $item[1];
                $name = $item[2];
                $varname = '$' . $name;

                switch ($type) {
                    case 'editor':
                    case 'text':
                    case 'email':
                    case 'password':
                        if (isset($args->data[$varname])) {
                            $args->data[$varname] = static ::quote($args->data[$varname]);
                        } else {
                            $args->data[$varname] = '';
                        }

                        $replace = strtr($theme->templates["content.admin.$type"], array(
                            '$name' => $name,
                            '$value' => $varname
                        ));
                        break;


                    case 'calendar':
                        $replace = $this->getcalendar($name, $args->data[$varname]);
                        break;


                    case 'tab':
                        $replace = strtr($this->templates['tabs.tab'], array(
                            '$id' => $name,
                            '$title' => $lang->__get($name) ,
                            '$url' => '',
                        ));
                        break;


                    case 'ajaxtab':
                        $replace = strtr($this->templates['tabs.tab'], array(
                            '$id' => $name,
                            '$title' => $lang->__get($name) ,
                            '$url' => "\$ajax=$name",
                        ));
                        break;


                    case 'tabpanel':
                        $replace = strtr($this->templates['tabs.panel'], array(
                            '$id' => $name,
                            '$content' => isset($args->data[$varname]) ? $varname : '',
                        ));
                        break;


                    default:
                        $replace = strtr($theme->templates["content.admin.$type"], array(
                            '$name' => $name,
                            '$value' => $varname
                        ));
                }

                $result = str_replace($item[0], $replace, $result);
            }
        }

        return $result;
    }

    public function parsearg($s, targs $args) {
        $result = $this->shortcode($s, $args);
        $result = strtr($result, $args->data);
        $result = $args->callback($result);
        return $this->parse($result);
    }

    public function parselink($s) {
        $list = explode(',', $s);
        $a = array();
        foreach ($list as $item) {
            if ($i = strpos($item, '=')) {
                $a[trim(substr($item, 0, $i)) ] = trim(substr($item, $i + 1));
            } else {
                $a['text'] = trim($item);
            }
        }

        $a['href'] = str_replace('?', litepubl::$site->q, $a['href']);
        if (!strbegin($a['href'], 'http')) {
            $a['href'] = litepubl::$site->url . $a['href'];
        }

        if (isset($a['icon'])) {
            $a['text'] = $this->geticon($a['icon']) . (empty($a['text']) ? '' : ' ' . $a['text']);
        }

        if (isset($a['tooltip'])) {
            $a['title'] = $a['tooltip'];
            $a['class'] = empty($a['class']) ? 'tooltip-toggle' : $a['class'] . ' tooltip-toggle';
        }

        $attr = '';
        foreach (array(
            'class',
            'title',
            'role'
        ) as $name) {
            if (!empty($a[$name])) {
                $attr.= sprintf(' %s="%s"', $name, $a[$name]);
            }
        }

        return sprintf('<a href="%s"%s>%s</a>', $a['href'], $attr, $a['text']);
    }

    public function form($tml, targs $args) {
        return $this->parsearg(str_replace('$items', $tml, ttheme::i()->templates['content.admin.form']) , $args);
    }

    public function gettable($head, $body, $footer = '') {
        return strtr($this->templates['table'], array(
            '$class' => ttheme::i()->templates['content.admin.tableclass'],
            '$head' => $head,
            '$body' => $body,
            '$footer' => $footer,
        ));
    }

    public function success($text) {
        return str_replace('$text', $text, $this->templates['success']);
    }

    public function getcount($from, $to, $count) {
        return $this->h(sprintf(tlocal::i()->itemscount, $from, $to, $count));
    }

    public function geticon($name, $screenreader = false) {
        return str_replace('$name', $name, $this->templates['icon']) . ($screenreader ? str_replace('$text', $screenreader, $this->templates['screenreader']) : '');
    }

    public function getsection($title, $content) {
        return strtr($this->templates['section'], array(
            '$title' => $title,
            '$content' => $content
        ));
    }

    public function geterr($content) {
        return strtr($this->templates['error'], array(
            '$title' => tlocal::i()->error,
            '$content' => $content
        ));
    }

    public function help($content) {
        return str_replace('$content', $content, $this->templates['help']);
    }

    public function getcalendar($name, $date) {
        $date = datefilter::timestamp($date);

        $args = new targs();
        $args->name = $name;
        $args->title = tlocal::i()->__get($name);
        $args->format = datefilter::$format;

        if ($date) {
            $args->date = date(datefilter::$format, $date);
            $args->time = date(datefilter::$timeformat, $date);
        } else {
            $args->date = '';
            $args->time = '';
        }

        return $this->parsearg($this->templates['calendar'], $args);
    }

    public function getdaterange($from, $to) {
        $from = datefilter::timestamp($from);
        $to = datefilter::timestamp($to);

        $args = new targs();
        $args->from = $from ? date(datefilter::$format, $from) : '';
        $args->to = $to ? date(datefilter::$format, $to) : '';
        $args->format = datefilter::$format;

        return $this->parsearg($this->templates['daterange'], $args);
    }

    public function getcats(array $items) {
        tlocal::i()->addsearch('editor');
        $result = $this->parse($this->templates['posteditor.categories.head']);
        tcategories::i()->loadall();
        $result.= $this->getsubcats(0, $items);
        return $result;
    }

    protected function getsubcats($parent, array $items, $exclude = false) {
        $result = '';
        $args = new targs();
        $tml = $this->templates['posteditor.categories.item'];
        $categories = tcategories::i();
        foreach ($categories->items as $id => $item) {
            if (($parent == $item['parent']) && !($exclude && in_array($id, $exclude))) {
                $args->add($item);
                $args->checked = in_array($item['id'], $items);
                $args->subcount = '';
                $args->subitems = $this->getsubcats($id, $items, $exclude);
                $result.= $this->parsearg($tml, $args);
            }
        }

        if ($result) {
            $result = str_replace('$item', $result, $this->templates['posteditor.categories']);
        }

        return $result;
    }

    public function processcategories() {
        $result = tadminhtml::check2array('category-');
        array_clean($result);
        array_delete_value($result, 0);
        return $result;
    }

    public function getfilelist(array $list) {
        $args = new targs();
        $args->fileperm = '';

        if (is_callable($this->onfileperm)) {
            call_user_func_array($this->onfileperm, array(
                $args
            ));
        } else if (litepubl::$options->show_file_perm) {
            $args->fileperm = tadminperms::getcombo(0, 'idperm_upload');
        }

        $files = tfiles::i();
        $where = litepubl::$options->ingroup('editor') ? '' : ' and author = ' . litepubl::$options->user;

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
            $args->items = tojson($db->res2items($db->query("select * from $files->thistable where id in ($items) or parent in ($items)")));
        }

        return $this->parsearg($this->templates['posteditor.filelist'], $args);
    }

} //class

//menus.admin.class.php
namespace litepubl;

class tadminmenus extends tmenus {

    protected function create() {
        parent::create();
        $this->basename = 'adminmenu';
        $this->addevents('onexclude');
        $this->data['heads'] = '';
    }

    public function settitle($id, $title) {
        if ($id && isset($this->items[$id])) {
            $this->items[$id]['title'] = $title;
            $this->save();
            litepubl::$urlmap->clearcache();
        }
    }

    public function getdir() {
        return litepubl::$paths->data . 'adminmenus' . DIRECTORY_SEPARATOR;
    }

    public function getadmintitle($name) {
        $lang = tlocal::i();
        $ini = & $lang->ini;
        if (isset($ini[$name]['title'])) {
            return $ini[$name]['title'];
        }

        tlocal::usefile('install');
        if (!in_array('adminmenus', $lang->searchsect)) {
            array_unshift($lang->searchsect, 'adminmenus');
        }

        if ($result = $lang->__get($name)) {
            return $result;
        }

        return $name;
    }

    public function createurl($parent, $name) {
        return $parent == 0 ? "/admin/$name/" : $this->items[$parent]['url'] . "$name/";
    }

    public function createitem($parent, $name, $group, $class) {
        $title = $this->getadmintitle($name);
        $url = $this->createurl($parent, $name);
        return $this->additem(array(
            'parent' => $parent,
            'url' => $url,
            'title' => $title,
            'name' => $name,
            'class' => $class,
            'group' => $group
        ));
    }

    public function additem(array $item) {
        if (empty($item['group'])) {
            $groups = tusergroups::i();
            $item['group'] = $groups->items[$groups->defaults[0]]['name'];
        }
        return parent::additem($item);
    }

    public function addfakemenu(tmenu $menu) {
        $this->lock();
        $id = parent::addfakemenu($menu);
        if (empty($this->items[$id]['group'])) {
            $groups = tusergroups::i();
            $group = count($groups->defaults) ? $groups->items[$groups->defaults[0]]['name'] : 'commentator';
            $this->items[$id]['group'] = $group;
        }

        $this->unlock();
        return $id;
    }

    public function getchilds($id) {
        if ($id == 0) {
            $result = array();
            $options = litepubl::$options;
            foreach ($this->tree as $iditem => $items) {
                if ($options->hasgroup($this->items[$iditem]['group'])) $result[] = $iditem;
            }
            return $result;
        }

        $parents = array(
            $id
        );
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

    public function exclude($id) {
        if (!litepubl::$options->hasgroup($this->items[$id]['group'])) return true;
        return $this->onexclude($id);
    }

} //class

//menu.admin.class.php
namespace litepubl;

class tadminmenu extends tmenu {
    public static $adminownerprops = array(
        'title',
        'url',
        'idurl',
        'parent',
        'order',
        'status',
        'name',
        'group'
    );

    public static function getinstancename() {
        return 'adminmenu';
    }

    public static function getowner() {
        return tadminmenus::i();
    }

    protected function create() {
        parent::create();
        $this->cache = false;
    }

    public function get_owner_props() {
        return static ::$adminownerprops;
    }

    public function load() {
        return true;
    }

    public function save() {
        return true;
    }

    public function gethead() {
        return tadminmenus::i()->heads;
    }

    public function getidview() {
        return tviews::i()->defaults['admin'];
    }

    public function getadmintheme() {
        return $this->view->admintheme;
    }

    public static function auth($group) {
        if ($err = tguard::checkattack()) {
            return $err;
        }

        if (!litepubl::$options->user) {
            turlmap::nocache();
            return litepubl::$urlmap->redir('/admin/login/' . litepubl::$site->q . 'backurl=' . urlencode(litepubl::$urlmap->url));
        }

        if (!litepubl::$options->hasgroup($group)) {
            $url = tusergroups::i()->gethome(litepubl::$options->group);
            turlmap::nocache();
            return litepubl::$urlmap->redir($url);
        }
    }

    public function request($id) {
        error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING);
        ini_set('display_errors', 1);

        if (is_null($id)) {
            $id = $this->owner->class2id(get_class($this));
        }

        $this->data['id'] = (int)$id;
        if ($id > 0) {
            $this->basename = $this->parent == 0 ? $this->name : $this->owner->items[$this->parent]['name'];
        }

        if ($s = static ::auth($this->group)) {
            return $s;
        }

        tlocal::usefile('admin');

        if ($s = $this->canrequest()) {
            return $s;
        }

        $this->doprocessform();
    }

    public function canrequest() {
    }

    protected function doprocessform() {
        if (isset($_POST) && count($_POST)) {
            litepubl::$urlmap->clearcache();
        }

        return parent::doprocessform();
    }

    public function getcont() {
        if (litepubl::$options->admincache) {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $filename = 'adminmenu.' . litepubl::$options->user . '.' . md5($_SERVER['REQUEST_URI'] . '&id=' . $id) . '.php';
            if ($result = litepubl::$urlmap->cache->get($filename)) {
                return $result;
            }

            $result = parent::getcont();
            litepubl::$urlmap->cache->set($filename, $result);
            return $result;
        } else {
            return parent::getcont();
        }
    }

    public static function idget() {
        return (int)tadminhtml::getparam('id', 0);
    }

    public function getaction() {
        return isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
    }

    public function gethtml($name = '') {
        return tadminhtml::i();
    }

    public function getlang() {
        return tlocal::i($this->name);
    }

    public function getadminlang() {
        return tlocal::inifile($this, '.admin.ini');
    }

    public function getconfirmed() {
        return isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1);
    }

    public function getnotfound() {
        return $this->admintheme->geterr(tlocal::i()->notfound);
    }

    public function getadminurl() {
        return litepubl::$site->url . $this->url . litepubl::$site->q . 'id';
    }

    public function getfrom($perpage, $count) {
        if (litepubl::$urlmap->page <= 1) return 0;
        return min($count, (litepubl::$urlmap->page - 1) * $perpage);
    }

} //class

//author-rights.class.php
namespace litepubl;

class tauthor_rights extends tevents {

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->addevents('gethead', 'getposteditor', 'editpost', 'changeposts', 'canupload', 'candeletefile');
        $this->basename = 'authorrights';
    }

}

//admin.posteditor.class.php
namespace litepubl;

class tposteditor extends tadminmenu {
    public $idpost;
    protected $isauthor;

    public static function i($id = 0) {
        return parent::iteminstance(__class__, $id);
    }

    public function gethead() {
        $result = parent::gethead();

        $template = ttemplate::i();
        $template->ltoptions['idpost'] = $this->idget();
        $result.= $template->getjavascript($template->jsmerger_posteditor);

        if ($this->isauthor && ($h = tauthor_rights::i()->gethead())) {
            $result.= $h;
        }

        return $result;
    }

    public static function getcombocategories(array $items, $idselected) {
        $result = '';
        $categories = tcategories::i();
        $categories->loadall();

        if (!count($items)) {
            $items = array_keys($categories->items);
        }

        foreach ($items as $id) {
            $result.= sprintf('<option value="%s" %s>%s</option>', $id, $id == $idselected ? 'selected' : '', basetheme::quote($categories->getvalue($id, 'title')));
        }

        return $result;
    }

    protected function getcategories(tpost $post) {
        $postitems = $post->categories;
        $categories = tcategories::i();
        if (!count($postitems)) {
            $postitems = array(
                $categories->defaultid
            );
        }

        return $this->admintheme->getcats($postitems);
    }

    public function getvarpost($post) {
        if (!$post) {
            return basetheme::$vars['post'];
        }

        return $post;
    }

    public function getajaxlink($idpost) {
        return litepubl::$site->url . '/admin/ajaxposteditor.htm' . litepubl::$site->q . "id=$idpost&get";
    }

    public function gettabs($post = null) {
        $post = $this->getvarpost($post);
        $args = new targs();
        $this->getargstab($post, $args);
        return $this->admintheme->parsearg($this->gettabstemplate() , $args);
    }

    public function gettabstemplate() {
        $admintheme = $this->admintheme;
        return strtr($admintheme->templates['tabs'], array(
            '$id' => 'tabs',
            '$tab' => $admintheme->templates['posteditor.tabs.tabs'],
            '$panel' => $admintheme->templates['posteditor.tabs.panels'],
        ));
    }

    public function getargstab(tpost $post, targs $args) {
        $args->id = $post->id;
        $args->ajax = $this->getajaxlink($post->id);
        //categories tab
        $args->categories = $this->getcategories($post);

        //datetime tab
        $args->posted = $post->posted;

        //seo tab
        $args->url = $post->url;
        $args->title2 = $post->title2;
        $args->keywords = $post->keywords;
        $args->description = $post->description;
        $args->head = $post->rawhead;
    }

    // $posteditor.files in template editor
    public function getfilelist($post = null) {
        $post = $this->getvarpost($post);
        return $this->admintheme->getfilelist($post->id ? $post->factory->files->itemsposts->getitems($post->id) : array());
    }

    public function gettext($post = null) {
        $post = $this->getvarpost($post);
        $ajax = tajaxposteditor::i();
        return $ajax->gettext($post->rawcontent, $this->admintheme);
    }

    public function canrequest() {
        tlocal::admin()->searchsect[] = 'editor';
        $this->isauthor = false;
        $this->basename = 'editor';
        $this->idpost = $this->idget();
        if ($this->idpost > 0) {
            $posts = tposts::i();
            if (!$posts->itemexists($this->idpost)) {
                return 404;
            }
        }

        $post = tpost::i($this->idpost);
        if (!litepubl::$options->hasgroup('editor')) {
            if (litepubl::$options->hasgroup('author')) {
                $this->isauthor = true;
                if (($post->id != 0) && (litepubl::$options->user != $post->author)) {
                    return 403;
                }
            }
        }
    }

    public function gettitle() {
        if ($this->idpost == 0) {
            return parent::gettitle();
        } else {
            if (isset(tlocal::admin()->ini[$this->name]['editor'])) return tlocal::get($this->name, 'editor');
            return tlocal::get('editor', 'editor');
        }
    }

    public function getexternal() {
        $this->basename = 'editor';
        $this->idpost = 0;
        return $this->getcontent();
    }

    public function getpostargs(tpost $post, targs $args) {
        $args->id = $post->id;
        $args->ajax = $this->getajaxlink($post->id);
        $args->title = tcontentfilter::unescape($post->title);
    }

    public function getcontent() {
        $result = '';
        $admintheme = $this->admintheme;
        $lang = tlocal::admin('editor');
        $args = new targs();

        $post = $this->idpost ? tpost::i($this->idpost) : $this->newpost();
        $vars = new themevars();
        $vars->post = $post;
        $vars->posteditor = $this;

        if ($post->id != 0) {
            $result.= $admintheme->h($lang->formhead . $post->bookmark);
        }

        if ($this->isauthor && ($r = tauthor_rights::i()->getposteditor($post, $args))) {
            return $r;
        }

        $args->id = $post->id;
        $args->title = $post->title;
        $args->adminurl = $this->url;
        $result.= $admintheme->parsearg($admintheme->templates['posteditor'], $args);
        return $result;
    }

    protected function processtab(tpost $post) {
        extract($_POST, EXTR_SKIP);

        $post->title = $title;
        $post->categories = $this->admintheme->processcategories();

        if (($post->id == 0) && (litepubl::$options->user > 1)) {
            $post->author = litepubl::$options->user;
        }

        if (isset($tags)) {
            $post->tagnames = $tags;
        }

        if (isset($icon)) {
            $post->icon = (int)$icon;
        }

        if (isset($idview)) {
            $post->idview = (int)$idview;
        }

        if (isset($posted) && $posted) {
            $post->posted = datefilter::getdate('posted');
        }

        if (isset($status)) {
            $post->status = $status == 'draft' ? 'draft' : 'published';
            $post->comstatus = $comstatus;
            $post->pingenabled = isset($pingenabled);
            $post->idperm = (int)$idperm;
            if ($password) {
                $post->password = $password;
            }
        }

        if (isset($url)) {
            $post->url = $url;
            $post->title2 = $title2;
            $post->keywords = $keywords;

            $post->description = $description;
            $post->rawhead = $head;
        }

        $post->content = $raw;
    }

    protected function processfiles(tpost $post) {
        if (isset($_POST['files'])) {
            $post->files = tdatabase::str2array(trim($_POST['files'], ', '));
        }
    }

    public function newpost() {
        return new tpost();
    }

    public function canprocess() {
        if (empty($_POST['title'])) {
            $lang = tlocal::admin('editor');
            return $lang->emptytitle;
        }
    }

    public function afterprocess(tpost $post) {
    }

    public function processform() {
        $lang = tlocal::admin('editor');
        $admintheme = $this->admintheme;

        if ($error = $this->canprocess()) {
            return $admintheme->geterr($lang->error, $error);
        }

        $id = (int)$_POST['id'];
        $post = $id ? tpost::i($id) : $this->newpost();

        if ($this->isauthor && ($r = tauthor_rights::i()->editpost($post))) {
            $this->idpost = $post->id;
            return $r;
        }

        $this->processtab($post);
        $this->processfiles($post);

        $posts = $post->factory->posts;
        if ($id == 0) {
            $this->idpost = $posts->add($post);
            $_POST['id'] = $this->idpost;
        } else {
            $posts->edit($post);
        }
        $_GET['id'] = $this->idpost;

        $this->afterprocess($post);
        return $admintheme->success($lang->success);
    }

} //class

//admin.posteditor.ajax.class.php
namespace litepubl;

class tajaxposteditor extends tevents {
    public $idpost;
    private $isauthor;

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->basename = 'ajaxposteditor';
        $this->data['eventnames'] = & $this->eventnames;
        $this->map['eventnames'] = 'eventnames';

        $this->data['head'] = '';
        $this->data['visual'] = '';
        //'/plugins/ckeditor/init.js';
        $this->data['ajaxvisual'] = true;
    }

    public function addevent($name, $class, $func, $once = false) {
        if (!in_array($name, $this->eventnames)) {
            $this->eventnames[] = $name;
        }

        return parent::addevent($name, $class, $func, $once);
    }

    public function delete_event($name) {
        if (isset($this->events[$name])) {
            unset($this->events[$name]);
            array_delete_value($this->eventnames, $name);
            $this->save();
        }
    }

    protected static function error403() {
        return '<?php header(\'HTTP/1.1 403 Forbidden\', true, 403); ?>' . turlmap::htmlheader(false) . 'Forbidden';
    }

    public static function auth() {
        $options = litepubl::$options;
        if (!$options->user) return static ::error403();
        if (!$options->hasgroup('editor')) {
            if (!$options->hasgroup('author')) return static ::error403();
        }
    }

    public function request($arg) {
        $this->cache = false;
        turlmap::sendheader(false);

        if ($err = static ::auth()) return $err;
        $this->idpost = tadminhtml::idparam();
        $this->isauthor = litepubl::$options->ingroup('author');
        if ($this->idpost > 0) {
            $posts = tposts::i();
            if (!$posts->itemexists($this->idpost)) return static ::error403();
            if (!litepubl::$options->hasgroup('editor')) {
                if (litepubl::$options->hasgroup('author')) {
                    $this->isauthor = true;
                    $post = tpost::i($this->idpost);
                    if (litepubl::$options->user != $post->author) return static ::error403();
                }
            }
        }

        return $this->getcontent();
    }

    public function getcontent() {
        $theme = tview::i(tviews::i()->defaults['admin'])->theme;
        $lang = tlocal::i('editor');
        $post = tpost::i($this->idpost);
        $vars = new themevars();
        $vars->post = $post;

        switch ($_GET['get']) {
            case 'tags':
                $result = $theme->getinput('text', 'tags', $post->tagnames, $lang->tags);
                $lang->section = 'editor';
                $result.= $theme->h($lang->addtags);
                $items = array();
                $tags = $post->factory->tags;
                $list = $tags->getsorted(-1, 'name', 0);
                foreach ($list as $id) {
                    $items[] = '<a href="" class="posteditor-tag">' . $tags->items[$id]['title'] . "</a>";
                }
                $result.= sprintf('<p>%s</p>', implode(', ', $items));
                break;


            case 'status':
            case 'access':
                $args = new targs();
                $args->comstatus = tadminhtml::array2combo(array(
                    'closed' => $lang->closed,
                    'reg' => $lang->reg,
                    'guest' => $lang->guest,
                    'comuser' => $lang->comuser
                ) , $post->comstatus);

                $args->pingenabled = $post->pingenabled;
                $args->status = tadminhtml::array2combo(array(
                    'published' => $lang->published,
                    'draft' => $lang->draft
                ) , $post->status);

                $args->perms = tadminperms::getcombo($post->idperm);
                $args->password = $post->password;
                $result = admintheme::admin()->parsearg('[combo=comstatus]
      [checkbox=pingenabled]
      [combo=status]
      $perms
      [password=password]
      <p>$lang.notepassword</p>', $args);

                break;


            case 'view':
                $result = tadminviews::getcomboview($post->idview);
                break;


            default:
                $name = trim($_GET['get']);
                if (isset($this->events[$name])) {
                    $result = $this->callevent($name, array(
                        $post
                    ));
                } else {
                    $result = var_export($_GET, true);
                }
        }

        //tfiler::log($result);
        return turlmap::htmlheader(false) . $result;
    }

    public function gettext($text, $admintheme = null) {
        if (!$admintheme) {
            $admintheme = admintheme::admin();
        }

        $args = new targs();
        if ($this->visual) {
            if ($this->ajaxvisual) {
                $args->scripturl = $this->visual;
                $args->visual = $admintheme->parsearg($admintheme->templates['posteditor.text.visual'], $args);
            } else {
                $args->visual = ttemplate::i()->getjavascript($this->visual);
            }
        } else {
            $args->visual = '';
        }

        $args->raw = $text;
        return $admintheme->parsearg($admintheme->templates['posteditor.text'], $args);
    }

} //class

//htmlresource.class.php
namespace litepubl;

class tadminhtml {
    public static $tags = array(
        'h1',
        'h2',
        'h3',
        'h4',
        'p',
        'li',
        'ul',
        'strong',
        'div',
        'span'
    );

    public static function i() {
        return getinstance(__class__);
    }

    public static function getinstance($section) {
        tlocal::i($section);
        return static ::i();
    }

    public function __construct() {
        tlocal::usefile('admin');
    }

    public function __get($name) {
        if (in_array($name, static ::$tags)) return new thtmltag($name);
        if (strend($name, 'red') && in_array(substr($name, 0, -3) , static ::$tags)) return new redtag($name);

        throw new Exception("the requested $name item not found");
    }

    public function __call($name, $params) {
        if ($name == 'getinput') return call_user_func_array(array(
            ttheme::i() ,
            'getinput'
        ) , $params);

        $s = $this->__get($name);
        if (is_object($s) && ($s instanceof thtmltag)) return sprintf('<%1$s>%2$s</%1$s>', $name, $params[0]);

        if ($name == 'h4error') return sprintf('<h4 class="red">%s</h4>', $params[0]);

        $args = isset($params[0]) && $params[0] instanceof targs ? $params[0] : new targs();
        return $this->parsearg($s, $args);
    }

    public function parsearg($s, targs $args) {
        return admintheme::i()->parsearg($s, $args);
    }

    public static function specchars($s) {
        return strtr(htmlspecialchars($s) , array(
            '"' => '&quot;',
            "'" => '&#39;',
            '$' => '&#36;',
            '%' => '&#37;',
            '_' => '&#95;'
        ));
    }

    public function fixquote($s) {
        $s = str_replace("\\'", '\"', $s);
        $s = str_replace("'", '"', $s);
        return str_replace('\"', "'", $s);
    }

    public function load() {
    }

    public function loadinstall() {
        if (isset($this->ini['installation'])) return;
        tlocal::usefile('install');
        if ($v = parse_ini_file(litepubl::$paths->languages . 'install.ini', true)) {
            $this->ini = $v + $this->ini;
        }
    }

    public static function getparam($name, $default) {
        return !empty($_GET[$name]) ? $_GET[$name] : (!empty($_POST[$name]) ? $_POST[$name] : $default);
    }

    public static function idparam() {
        return (int)static ::getparam('id', 0);
    }

    public static function getadminlink($path, $params) {
        return litepubl::$site->url . $path . litepubl::$site->q . $params;
    }

    public static function getlink($url, $title) {
        return sprintf('<a href="%s%s">%s</a>', litepubl::$site->url, $url, $title);
    }

    public static function array2combo(array $items, $selected) {
        $result = '';
        foreach ($items as $i => $title) {
            $result.= sprintf('<option value="%s" %s>%s</option>', $i, $i == $selected ? 'selected' : '', static ::specchars($title));
        }
        return $result;
    }

    public static function getcombobox($name, array $items, $selected) {
        return sprintf('<select name="%1$s" id="%1$s">%2$s</select>', $name, static ::array2combo($items, $selected));
    }

    public function adminform($tml, targs $args) {
        return admintheme::i()->form($tml, $args);
    }

    public function getupload($name) {
        return $this->getinput('upload', $name, '', '');
    }

    public function getcheckbox($name, $value) {
        return $this->getinput('checkbox', $name, $value ? 'checked="checked"' : '', '$lang.' . $name);
    }

    public function getradioitems($name, array $items, $selected) {
        $result = '';
        $theme = ttheme::i();
        foreach ($items as $index => $title) {
            $result.= $theme->getradio($name, $index, static ::specchars($title) , $index == $selected);
        }
        return $result;
    }

    public function getsubmit() {
        $result = '';
        $theme = ttheme::i();
        $lang = tlocal::i();

        $a = func_get_args();
        foreach ($a as $name) {
            $result.= strtr($theme->templates['content.admin.button'], array(
                '$lang.$name' => $lang->__get($name) ,
                '$name' => $name,
            ));
        }

        return $result;
    }

    public function getedit($name, $value, $title) {
        return $this->getinput('text', $name, $value, $title);
    }

    public function getcombo($name, $value, $title) {
        return $this->getinput('combo', $name, $value, $title);
    }

    public static function datestr($date) {
        if ($date == '0000-00-00 00:00:00') return tlocal::i()->noword;
        return tlocal::date(strtotime($date) , 'd F Y');
    }

    public function gettable($head, $body) {
        return admintheme::i()->gettable($head, $body);
    }

    public function buildtable(array $items, array $tablestruct) {
        $tb = new tablebuilder();
        $tb->setstruct($tablestruct);
        return $tb->build($items);
    }

    public function getitemscount($from, $to, $count) {
        return sprintf($this->h4->itemscount, $from, $to, $count);
    }

    public function confirmdelete($id, $adminurl, $mesg) {
        $args = new targs();
        $args->id = $id;
        $args->action = 'delete';
        $args->adminurl = $adminurl;
        $args->confirm = $mesg;

        $admin = admintheme::i();
        return $admin->parsearg($admin->templates['confirmform'], $args);
    }

    public function confirm_delete($owner, $adminurl) {
        $id = (int)static ::getparam('id', 0);
        if (!$owner->itemexists($id)) return $this->h4red->notfound;
        if (isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1)) {
            $owner->delete($id);
            return $this->h4->successdeleted;
        } else {
            $args = new targs();
            $args->id = $id;
            $args->adminurl = $adminurl;
            $args->action = 'delete';
            $args->confirm = tlocal::i()->confirmdelete;

            $admin = admintheme::i();
            return $this->parsearg($admin->templates['confirmform'], $args);
        }
    }

    public static function check2array($prefix) {
        $result = array();
        foreach ($_POST as $key => $value) {
            if (strbegin($key, $prefix)) {
                $result[] = is_numeric($value) ? (int)$value : $value;
            }
        }
        return $result;
    }

}

//filter.datetime.class.php
namespace litepubl;

// namespace litepubl\admin;
class datefilter {
    //only date without time
    public static $format = 'd.m.Y';
    public static $timeformat = 'H:i';

    public static function timestamp($date) {
        if (is_numeric($date)) {
            $date = (int)$date;
        } else if ($date == '0000-00-00 00:00:00') {
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

    public static function getdate($name, $format = false) {
        if (empty($_POST[$name])) return 0;
        $date = trim($_POST[$name]);
        if (!$date) return 0;

        if (version_compare(PHP_VERSION, '5.3', '>=')) {
            if (!$format) $format = static ::$format;
            $d = DateTime::createFromFormat($format, $date);
            if ($d && $d->format($format) == $date) {
                $d->setTime(0, 0, 0);
                return $d->getTimestamp() + static ::gettime($name . '-time');
            }
        } else {
            if (@sscanf($date, '%d.%d.%d', $d, $m, $y)) {
                return mktime(0, 0, 0, $m, $d, $y) + static ::gettime($name . '-time');
            }
        }

        return 0;
    }

    public static function gettime($name) {
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

} //class

//html.autoform.class.php
namespace litepubl;

class tautoform {
    const editor = 'editor';
    const text = 'text';
    const checkbox = 'checkbox';
    const hidden = 'hidden';

    public $obj;
    public $props;
    public $section;
    public $_title;

    public static function i() {
        return getinstance(__class__);
    }

    public function __construct(tdata $obj, $section, $titleindex) {
        $this->obj = $obj;
        $this->section = $section;
        $this->props = array();
        $lang = tlocal::i($section);
        $this->_title = $lang->$titleindex;
    }

    public function __set($name, $value) {
        $this->props[] = array(
            'obj' => $this->obj,
            'propname' => $name,
            'type' => $value
        );
    }

    public function __get($name) {
        if (isset($this->obj->$name)) {
            return array(
                'obj' => $this->obj,
                'propname' => $name
            );
        }
        //tlogsubsystem::error(sprintf('The property %s not found in class %s', $name, get_class($this->obj));
        
    }

    public function __call($name, $args) {
        if (isset($this->obj->$name)) {
            $result = array(
                'obj' => $this->obj,
                'propname' => $name,
                'type' => $args[0]
            );
            if (($result['type'] == 'combo') && isset($args[1])) $result['items'] = $args[1];
            return $result;
        }
    }

    public function add() {
        $a = func_get_args();
        foreach ($a as $prop) {
            $this->addprop($prop);
        }
    }

    public function addsingle($obj, $propname, $type) {
        return $this->addprop(array(
            'obj' => $obj,
            'propname' => $propname,
            'type' => $type
        ));
    }

    public function addeditor($obj, $propname) {
        return $this->addsingle($obj, $propname, 'editor');
    }

    public function addprop(array $prop) {
        if (isset($prop['type'])) {
            $type = $prop['type'];
        } else {
            $type = 'text';
            $value = $prop['obj']->{$prop['propname']};
            if (is_bool($value)) {
                $type = 'checkbox';
            } elseif (strpos($value, "\n")) {
                $type = 'editor';
            }
        }

        $item = array(
            'obj' => $prop['obj'],
            'propname' => $prop['propname'],
            'type' => $type,
            'title' => isset($prop['title']) ? $prop['title'] : ''
        );
        if (($type == 'combo') && isset($prop['items'])) $item['items'] = $prop['items'];
        $this->props[] = $item;
        return count($this->props) - 1;
    }

    public function getcontent() {
        $result = '';
        $lang = tlocal::i();
        $theme = ttheme::i();

        foreach ($this->props as $prop) {
            $value = $prop['obj']->{$prop['propname']};
            switch ($prop['type']) {
                case 'text':
                case 'editor':
                    $value = tadminhtml::specchars($value);
                    break;


                case 'checkbox':
                    $value = $value ? 'checked="checked"' : '';
                    break;


                case 'combo':
                    $value = tadminhtml::array2combo($prop['items'], $value);
                    break;
            }

            $result.= strtr($theme->templates['content.admin.' . $prop['type']], array(
                '$lang.$name' => empty($prop['title']) ? $lang->{$prop['propname']} : $prop['title'],
                '$name' => $prop['propname'],
                '$value' => $value
            ));
        }
        return $result;
    }

    public function getform() {
        $args = new targs();
        $args->formtitle = $this->_title;
        $args->items = $this->getcontent();
        $theme = ttheme::i();
        $tml = str_replace('[submit=update]', str_replace('$name', 'update', $theme->templates['content.admin.submit']) , $theme->templates['content.admin.form']);
        return $theme->parsearg($tml, $args);
    }

    public function processform() {
        foreach ($this->props as $prop) {
            if (method_exists($prop['obj'], 'lock')) $prop['obj']->lock();
        }

        foreach ($this->props as $prop) {
            $name = $prop['propname'];
            if (isset($_POST[$name])) {
                $value = trim($_POST[$name]);
                if ($prop['type'] == 'checkbox') $value = true;
            } else {
                $value = false;
            }
            $prop['obj']->$name = $value;
        }

        foreach ($this->props as $prop) {
            if (method_exists($prop['obj'], 'unlock')) $prop['obj']->unlock();
        }
    }

} //class

//html.tabs.class.php
namespace litepubl;

class tabs {
    public $tabs;
    public $panels;
    public $id;
    public $_admintheme;
    private static $index = 0;

    public function __construct($admintheme = null) {
        $this->_admintheme = $admintheme;
        $this->tabs = array();
        $this->panels = array();
    }

    public function getadmintheme() {
        if (!$this->_admintheme) {
            $this->_admintheme = admintheme::i();
        }

        return $this->_admintheme;
    }

    public function get() {
        return strtr($this->getadmintheme()->templates['tabs'], array(
            '$id' => $this->id ? $this->id : 'tabs-' . static ::$index++,
            '$tab' => implode("\n", $this->tabs) ,
            '$panel' => implode("\n", $this->panels) ,
        ));
    }

    public function add($title, $content) {
        $this->addtab('', $title, $content);
    }

    public function ajax($title, $url) {
        $this->addtab($url, $title, '');
    }

    public function addtab($url, $title, $content) {
        $id = static ::$index++;
        $this->tabs[] = $this->gettab($id, $url, $title);
        $this->panels[] = $this->getpanel($id, $content);
    }

    public function gettab($id, $url, $title) {
        return strtr($this->getadmintheme()->templates['tabs.tab'], array(
            '$id' => $id,
            '$title' => $title,
            '$url' => $url,
        ));
    }

    public function getpanel($id, $content) {
        return strtr($this->getadmintheme()->templates['tabs.panel'], array(
            '$id' => $id,
            '$content' => $content,
        ));
    }

} //class

//html.adminform.class.php
namespace litepubl;

class adminform {
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

    public function __construct($args = null) {
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

    public function line($content) {
        return str_replace('$content', $content, $this->getadmintheme()->templates['inline']);
    }

    public function getadmintheme() {
        return admintheme::i();
    }

    public function __set($k, $v) {
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

    public function centergroup($buttons) {
        return str_replace('$buttons', $buttons, $this->getadmintheme()->templates['centergroup']);
    }

    public function hidden($name, $value) {
        return sprintf('<input type="hidden" name="%s" value="%s" />', $name, $value);
    }

    public function getdelete($table) {
        $this->body = $table;
        $this->body.= $this->hidden('delete', 'delete');
        $this->submit = 'delete';

        return $this->get();
    }

    public function __tostring() {
        return $this->get();
    }

    public function gettml() {
        $admin = $this->getadmintheme();
        $title = $this->title ? str_replace('$title', $this->title, $admin->templates['form.title']) : '';

        $attr = "action=\"$this->action\"";
        foreach (array(
            'method',
            'enctype',
            'target',
            'id',
            'class'
        ) as $k) {
            if ($v = $this->$k) $attr.= sprintf(' %s="%s"', $k, $v);
        }

        $theme = ttheme::i();
        $lang = tlocal::i();
        $body = $this->body;

        if ($this->inline) {
            if ($this->submit) {
                $body.= $theme->getinput('button', $this->submit, '', $lang->__get($this->submit));
            }

            $body = $this->line($body);
        } else {
            if ($this->submit) {
                $body.= $theme->getinput('submit', $this->submit, '', $lang->__get($this->submit));
            }
        }

        return strtr($admin->templates['form'], array(
            '$title' => $title,
            '$before' => $this->before,
            '$attr' => $attr,
            '$body' => $body,
        ));
    }

    public function get() {
        return tadminhtml::i()->parsearg($this->gettml() , $this->args);
    }

} //class

//html.ulist.class.php
namespace litepubl;

class ulist {
    const aslinks = true;
    public $ul;
    public $item;
    public $link;
    public $value;
    public $result;

    public function __construct($admin = null, $islink = false) {
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

    public function li($name, $value) {
        return strtr(is_int($name) ? $this->value : $this->item, array(
            '$name' => $name,
            '$value' => $value,
            '$site.url' => litepubl::$site->url,
        ));
    }

    public function link($url, $title) {
        return strtr($this->link, array(
            '$name' => $url,
            '$value' => $title,
        ));
    }

    public function ul($items) {
        return str_replace('$item', $items, $this->ul);
    }

    public function getresult() {
        return $this->ul($this->result);
    }

    public function add($name, $value) {
        $this->result.= $this->li($name, $value);
    }

    public function get(array $props) {
        $result = '';
        foreach ($props as $name => $value) {
            if ($value === false) continue;

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

    public function links(array $props) {
        $this->item = $this->link;
        $result = $this->get($props);
        return str_replace('$site.url', litepubl::$site->url, $result);
    }

} //class

//html.tablebuilder.class.php
namespace litepubl;

class tablebuilder {
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

    public static function fromitems(array $items, array $struct) {
        $classname = __class__;
        $self = new $classname();
        $self->setstruct($struct);
        return $self->build($items);
    }

    public function __construct() {
        $this->head = '';
        $this->body = '';
        $this->footer = '';
        $this->callbacks = array();
        $this->args = new targs();
        $this->data = array();
    }

    public function setstruct(array $struct) {
        $this->head = '';
        $this->body = '<tr>';

        foreach ($struct as $index => $item) {
            if (!$item || !count($item)) continue;

            if (count($item) == 2) {
                $colclass = 'text-left';
            } else {
                $colclass = static ::getcolclass(array_shift($item));
            }

            $this->head.= sprintf('<th class="%s">%s</th>', $colclass, array_shift($item));

            $s = array_shift($item);
            if (is_string($s)) {
                $this->body.= sprintf('<td class="%s">%s</td>', $colclass, $s);
            } else if (is_callable($s)) {
                $name = '$callback' . $index;
                $this->body.= sprintf('<td class="%s">%s</td>', $colclass, $name);

                array_unshift($item, $this);
                $this->callbacks[$name] = array(
                    'callback' => $s,
                    'params' => $item,
                );
            } else {
                throw new Exception('Unknown column ' . var_export($s, true));
            }
        }

        $this->body.= '</tr>';
    }

    public function addcallback($varname, $callback, $param = null) {
        $this->callbacks[$varname] = array(
            'callback' => $callback,
            'params' => array(
                $this,
                $param
            ) ,
        );
    }

    public function addfooter($footer) {
        $this->footer = sprintf('<tfoot><tr>%s</tr></tfoot>', $footer);
    }

    public function td($colclass, $content) {
        return sprintf('<td class="%s">%s</td>', static ::getcolclass($colclass) , $content);
    }

    public function getadmintheme() {
        if (!$this->admintheme) {
            $this->admintheme = admintheme::i();
        }

        return $this->admintheme;
    }

    public function build(array $items) {
        $body = '';

        foreach ($items as $id => $item) {
            $body.= $this->parseitem($id, $item);
        }

        return $this->getadmintheme()->gettable($this->head, $body, $this->footer);
    }

    public function parseitem($id, $item) {
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

        return $this->getadmintheme()->parsearg($this->body, $args);
    }

    //predefined callbacks
    public function titems_callback(tablebuilder $self, titems $owner) {
        $self->item = $owner->getitem($self->id);
        $self->args->add($self->item);
    }

    public function setowner(titems $owner) {
        $this->addcallback('$tempcallback' . count($this->callbacks) , array(
            $this,
            'titems_callback'
        ) , $owner);
    }

    public function posts_callback(tablebuilder $self) {
        $post = tpost::i($self->id);
        basetheme::$vars['post'] = $post;
        $self->args->poststatus = tlocal::i()->__get($post->status);
    }

    public function setposts(array $struct) {
        array_unshift($struct, $this->checkbox('checkbox'));
        $this->setstruct($struct);
        $this->addcallback('$tempcallback' . count($this->callbacks) , array(
            $this,
            'posts_callback'
        ) , false);
    }

    public function props(array $props) {
        $lang = tlocal::i();
        $this->setstruct(array(
            array(
                $lang->name,
                '$name'
            ) ,

            array(
                $lang->property,
                '$value'
            )
        ));

        $body = '';
        $args = $this->args;
        $admintheme = $this->getadmintheme();

        foreach ($props as $k => $v) {
            if (($k === false) || ($v === false)) continue;

            if (is_array($v)) {
                foreach ($v as $kv => $vv) {
                    if ($k2 = $lang->__get($kv)) $kv = $k2;
                    $args->name = $kv;
                    $args->value = $vv;
                    $body.= $admintheme->parsearg($this->body, $args);
                }
            } else {
                if ($k2 = $lang->__get($k)) {
                    $k = $k2;
                }

                $args->name = $k;
                $args->value = $v;
                $body.= $admintheme->parsearg($this->body, $args);
            }
        }

        return $admintheme->gettable($this->head, $body);
    }

    public function inputs(array $inputs) {
        $lang = tlocal::i();
        $this->setstruct(array(
            array(
                $lang->name,
                '<label for="$name-input">$title</label>'
            ) ,

            array(
                $lang->property,
                '$input'
            )
        ));

        $body = '';
        $args = $this->args;
        $admintheme = $this->getadmintheme();

        foreach ($inputs as $name => $type) {
            if (($name === false) || ($type === false)) {
                continue;
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
            $args->input = $admintheme->parsearg($input, $args);
            $body.= $admintheme->parsearg($this->body, $args);
        }

        return $admintheme->gettable($this->head, $body);
    }

    public function action($action, $adminurl) {
        $title = tlocal::i()->__get($action);

        return array(
            $title,
            "<a href=\"$adminurl=\$id&action=$action\">$title</a>"
        );
    }

    public function checkbox($name) {
        $admin = $this->getadmintheme();

        return array(
            'text-center col-checkbox',
            $admin->templates['checkbox.invert'],
            str_replace('$name', $name, $admin->templates['checkbox.id'])
        );
    }

    public function namecheck() {
        $admin = admintheme::i();

        return array(
            'text-center col-checkbox',
            $admin->templates['checkbox.stub'],
            $admin->templates['checkbox.name']
        );
    }

    public static function getcolclass($s) {
        //most case
        if (!$s || $s == 'left') {
            return 'text-left';
        }

        $map = array(
            'left' => 'text-left',
            'right' => 'text-right',
            'center' => 'text-center'
        );

        $list = explode(' ', $s);
        foreach ($list as $i => $v) {
            if (isset($map[$v])) {
                $list[$i] = $map[$v];
            }
        }

        return implode(' ', $list);
    }

    public function date($date) {
        if ($date == tdata::zerodate) {
            return tlocal::i()->noword;
        } else {
            return tlocal::date(strtotime($date) , 'd F Y');
        }
    }

    public function datetime($date) {
        if ($date == tdata::zerodate) {
            return tlocal::i()->noword;
        } else {
            return tlocal::date(strtotime($date) , 'd F Y H:i');
        }
    }

}

//html.tag.class.php
namespace litepubl;

class thtmltag {
    public $tag;

    public function __construct($tag = '') {
        $this->tag = $tag;
    }
    public function __get($name) {
        return sprintf('<%1$s>%2$s</%1$s>', $this->tag, tlocal::i()->$name);
    }

} //class
class redtag extends thtmltag {

    public function __get($name) {
        return sprintf('<%1$s class="red">%2$s</%1$s>', $this->tag, tlocal::i()->$name);
    }

} //class

