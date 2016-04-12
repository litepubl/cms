<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\post;
use litepubl\view\Lang;
use litepubl\view\Filter;

class Post extends \litepubl\core\Item implements \litepubl\view\ViewInterface
{
    public $childdata;
    public $childtable;
    public $factory;
    public $syncdata;
    private $aprev;
    private $anext;
    private $metaInstance;
    private $themeInstance;
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
            $result = parent::iteminstance(get_called_class() , $id);
        }

        return $result;
    }

    public static function getinstancename() {
        return 'post';
    }

    public static function getchildtable() {
        return '';
    }

    public static function selectitems(array $items) {
        return array();
    }

    public static function select_child_items($table, array $items) {
        if (!$table || !count($items)) {
            return array();
        }

        $db = litepubl::$db;
        $childtable = $db->prefix . $table;
        $list = implode(',', $items);
        return $db->res2items($db->query("select $childtable.*
    from $childtable where id in ($list)"));
    }

    public static function newpost($classname) {
        $classname = $classname ? str_replace('-', '\\', $classname) : get_called_class();
        return new $classname();
    }

    protected function create() {
        $this->table = 'posts';
        $this->syncdata = array();
        $this->childtable = static ::getchildtable();

        $this->data = array(
            'id' => 0,
            'idview' => 1,
            'idurl' => 0,
            'parent' => 0,
            'author' => 0,
            'revision' => 0,
            'icon' => 0,
            'idperm' => 0,
            'class' => str_replace('\\', '-', get_class($this)) ,
            'posted' => 0,
            'modified' => 0,
            'url' => '',
            'title' => '',
            'title2' => '',
            'filtered' => '',
            'excerpt' => '',
            'rss' => '',
            'rawcontent' => false,
            'keywords' => '',
            'description' => '',
            'rawhead' => '',
            'moretitle' => '',
            'categories' => array() ,
            'tags' => array() ,
            'files' => array() ,
            'status' => 'published',
            'comstatus' => litepubl::$options->comstatus,
            'pingenabled' => litepubl::$options->pingenabled,
            'password' => '',
            'commentscount' => 0,
            'pingbackscount' => 0,
            'pagescount' => 0,
            'pages' => array()
        );

        $this->data['childdata'] = & $this->childdata;
        $this->factory = $this->getfactory();
        $posts = $this->factory->posts;
        foreach ($posts->itemcoclasses as $class) {
            $coinstance = litepubl::$classes->newinstance($class);
            $coinstance->post = $this;
            $this->coinstances[] = $coinstance;
        }
    }

    public function getfactory() {
        return litepubl::$classes->getfactory($this);
    }

    public function __get($name) {
        if ($this->childtable) {
            if ($name == 'id') {
                return $this->data['id'];
            }

            if (method_exists($this, $get = 'get' . $name)) {
                return $this->$get();
            }

            if (array_key_exists($name, $this->childdata)) {
                return $this->childdata[$name];
            }
        }

        // tags and categories theme tag
        switch ($name) {
            case 'catlinks':
                return $this->get_taglinks('categories', false);

            case 'taglinks':
                return $this->get_taglinks('tags', false);

            case 'excerptcatlinks':
                return $this->get_taglinks('categories', true);

            case 'excerpttaglinks':
                return $this->get_taglinks('tags', true);

            default:
                return parent::__get($name);
        }
    }

    public function __set($name, $value) {
        if ($this->childtable) {
            if ($name == 'id') {
                return $this->setid($value);
            }

            if (method_exists($this, $set = 'set' . $name)) {
                return $this->$set($value);
            }

            if (array_key_exists($name, $this->childdata)) {
                $this->childdata[$name] = $value;
                return true;
            }
        }

        return parent::__set($name, $value);
    }

    public function __isset($name) {
        return parent::__isset($name) || ($this->childtable && array_key_exists($name, $this->childdata));
    }

    //db
    public function afterdb() {
    }

    public function beforedb() {
    }

    public function load() {
        if ($result = $this->LoadFromDB()) {
            foreach ($this->coinstances as $coinstance) $coinstance->load();
        }
        return $result;
    }

    protected function LoadFromDB() {
        if ($a = static ::getassoc($this->id)) {
            $this->setassoc($a);
            return true;
        }
        return false;
    }

    public static function loadpost($id) {
        if ($a = static ::getassoc($id)) {
            $self = static ::newpost($a['class']);
            $self->setassoc($a);
            return $self;
        }
        return false;
    }

    public static function getassoc($id) {
        $db = litepubl::$db;
        return $db->selectassoc("select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
    where $db->posts.id = $id and  $db->urlmap.id  = $db->posts.idurl limit 1");
    }

    public function setassoc(array $a) {
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

        $this->SaveToDB();
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

    public function create_id() {
        $id = $this->factory->add($this);
        $this->setid($id);
        if ($this->childtable) {
            $this->beforedb();
            $this->childdata['id'] = $id;
            $this->getdb($this->childtable)->insert($this->childdata);
        }

        $this->idurl = $this->create_url();
        $this->db->setvalue($id, 'idurl', $this->idurl);
        $this->onid();

        return $id;
    }

    public function create_url() {
        return litepubl::$urlmap->add($this->url, get_class($this) , (int)$this->id);
    }

    public function onid() {
        if (isset($this->_onid) && count($this->_onid) > 0) {
            foreach ($this->_onid as $call) {
                try {
                    call_user_func($call, $this);
                }
                catch(Exception $e) {
                    litepubl::$options->handexception($e);
                }
            }
            unset($this->_onid);
        }

        if (isset($this->metaInstance)) {
            $this->metaInstance->id = $this->id;
            $this->metaInstance->save();
        }
    }

    public function setonid($call) {
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

    public function getcomments() {
        return $this->factory->getcomments($this->id);
    }

    public function getpingbacks() {
        return $this->factory->getpingbacks($this->id);
    }

    public function getprev() {
        if (!is_null($this->aprev)) {
            return $this->aprev;
        }

        $this->aprev = false;
        if ($id = $this->db->findid("status = 'published' and posted < '$this->sqldate' order by posted desc")) {
            $this->aprev = static ::i($id);
        }
        return $this->aprev;
    }

    public function getnext() {
        if (!is_null($this->anext)) {
            return $this->anext;
        }

        $this->anext = false;
        if ($id = $this->db->findid("status = 'published' and posted > '$this->sqldate' order by posted asc")) {
            $this->anext = static ::i($id);
        }
        return $this->anext;
    }

    public function getmeta() {
        if (!isset($this->metaInstance)) $this->metaInstance = $this->factory->getmeta($this->id);
        return $this->metaInstance;
    }

    public function Getlink() {
        return litepubl::$site->url . $this->url;
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

    public function settitle($title) {
        $this->data['title'] = tcontentfilter::escape(tcontentfilter::unescape($title));
    }

    public function gettheme() {
        if ($this->themeInstance) {
$this->themeInstance->setvar('post, $this);
            return $this->themeInstance;
        }

$mainview = $this->factory->mainview;
        $this->themeInstance = $mainview->schema ? $mainview->schema->theme : Schema::getSchema($this)->theme;
$this->themeInstance->setvar('post, $this);
        return $this->themeInstance;
    }

    public function parsetml($path) {
        $theme = $this->theme;
        return $theme->parse($theme->templates[$path]);
    }

    public function getextra() {
        $theme = $this->theme;
        return $theme->parse($theme->extratml);
    }

    public function getbookmark() {
        return $this->theme->parse('<a href="$post.link" rel="bookmark" title="$lang.permalink $post.title">$post.iconlink$post.title</a>');
    }

    public function getrsscomments() {
        return litepubl::$site->url . "/comments/$this->id.xml";
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

    public function getsqldate() {
        return sqldate($this->posted);
    }

    public function getidimage() {
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

    public function getimage() {
        if ($id = $this->getidimage()) {
            return $this->factory->files->geturl($id);
        }

        return false;
    }

    public function getthumb() {
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

    public function getfirstimage() {
        if (count($this->files)) {
            return $this->factory->files->getfirstimage($this->files);
        }

        return '';
    }

    //template
    protected function get_taglinks($name, $excerpt) {
        $items = $this->__get($name);
        if (!count($items)) {
            return '';
        }

        $theme = $this->theme;
        $tmlpath = $excerpt ? 'content.excerpts.excerpt' : 'content.post';
        $tmlpath.= $name == 'tags' ? '.taglinks' : '.catlinks';
        $tmlitem = $theme->templates[$tmlpath . '.item'];

        $tags = strbegin($name, 'tag') ? $this->factory->tags : $this->factory->categories;
        $tags->loaditems($items);

        $args = new targs();
        $list = array();

        foreach ($items as $id) {
            $item = $tags->getitem($id);
            $args->add($item);
            if (($item['icon'] == 0) || litepubl::$options->icondisabled) {
                $args->icon = '';
            } else {
                $files = $this->factory->files;
                if ($files->itemexists($item['icon'])) {
                    $args->icon = $files->geticon($item['icon']);
                } else {
                    $args->icon = '';
                }
            }
            $list[] = $theme->parsearg($tmlitem, $args);
        }

        $args->items = ' ' . implode($theme->templates[$tmlpath . '.divider'], $list);
        $result = $theme->parsearg($theme->templates[$tmlpath], $args);
        $this->factory->posts->callevent('ontags', array(
            $tags,
            $excerpt, &$result
        ));
        return $result;
    }

    public function getdate() {
        return tlocal::date($this->posted, $this->theme->templates['content.post.date']);
    }

    public function getexcerptdate() {
        return tlocal::date($this->posted, $this->theme->templates['content.excerpts.excerpt.date']);
    }

    public function getday() {
        return date($this->posted, 'D');
    }

    public function getmonth() {
        return tlocal::date($this->posted, 'M');
    }

    public function getyear() {
        return date($this->posted, 'Y');
    }

    public function getmorelink() {
        if ($this->moretitle) {
            return $this->parsetml('content.excerpts.excerpt.morelink');
        }

        return '';
    }

    public function gettagnames() {
        if (count($this->tags)) {
            $tags = $this->factory->tags;
            return implode(', ', $tags->getnames($this->tags));
        }

        return '';
    }

    public function settagnames($names) {
        $tags = $this->factory->tags;
        $this->tags = $tags->createnames($names);
    }

    public function getcatnames() {
        if (count($this->categories)) {
            $categories = $this->factory->categories;
            return implode(', ', $categories->getnames($this->categories));
        }

        return '';
    }

    public function setcatnames($names) {
        $categories = $this->factory->categories;
        $this->categories = $categories->createnames($names);
        if (count($this->categories) == 0) {
            $defaultid = $categories->defaultid;
            if ($defaultid > 0) $this->data['categories '][] = $dfaultid;
        }
    }

    public function getcategory() {
        if ($idcat = $this->getidcat()) {
            return $this->factory->categories->getname($idcat);
        }

        return '';
    }

    public function getidcat() {
        if (($cats = $this->categories) && count($cats)) {
            return $cats[0];
        }

        return 0;
    }

    //ITemplate
    public function request($id) {
        parent::request((int)$id);
        if ($this->status != 'published') {
            if (!litepubl::$options->show_draft_post) {
                return 404;
            }

            $groupname = litepubl::$options->group;
            if (($groupname == 'admin') || ($groupname == 'editor')) {
                return;
            }

            if ($this->author == litepubl::$options->user) {
                return;
            }

            return 404;
        }
    }

    public function gettitle() {
        return $this->data['title'];
    }

    public function gethead() {
        $result = $this->rawhead;
        $this->factory->mainview->ltoptions['idpost'] = $this->id;
        $theme = $this->theme;
        $result.= $theme->templates['head.post'];
        if ($prev = $this->prev) {
            ttheme::$vars['prev'] = $prev;
            $result.= $theme->templates['head.post.prev'];
        }

        if ($next = $this->next) {
            ttheme::$vars['next'] = $next;
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

    public function getanhead() {
        $result = '';
        $this->factory->posts->callevent('onanhead', array(
            $this, &$result
        ));
        return $result;
    }

    public function getkeywords() {
        return empty($this->data['keywords']) ? $this->Gettagnames() : $this->data['keywords'];
    }
    //fix for file version. For db must be deleted
    public function setkeywords($s) {
        $this->data['keywords'] = $s;
    }

    public function getdescription() {
        return $this->data['description'];
    }

    public function getidview() {
        return $this->data['idview'];
    }

    public function setidview($id) {
        if ($id != $this->idview) {
            $this->data['idview'] = $id;
            if ($this->id) $this->db->setvalue($this->id, 'idview', $id);
        }
    }

    public function setid_view($id_view) {
        $this->data['idview'] = $id_view;
    }

    public function geticonurl() {
        if ($this->icon == 0) return '';
        $files = $this->factory->files;
        if ($files->itemexists($this->icon)) return $files->geturl($this->icon);
        $this->icon = 0;
        $this->save();
        return '';
    }

    public function geticonlink() {
        if (($this->icon == 0) || litepubl::$options->icondisabled) return '';
        $files = $this->factory->files;
        if ($files->itemexists($this->icon)) return $files->geticon($this->icon);
        $this->icon = 0;
        $this->save();
        return '';
    }

    public function setfiles(array $list) {
        array_clean($list);
        $this->data['files'] = $list;
    }

    public function getfilelist() {
        if ((count($this->files) == 0) || ((litepubl::$urlmap->page > 1) && litepubl::$options->hidefilesonpage)) {
            return '';
        }

        $files = $this->factory->files;
        return $files->getfilelist($this->files, false);
    }

    public function getexcerptfilelist() {
        if (count($this->files) == 0) return '';
        $files = $this->factory->files;
        return $files->getfilelist($this->files, true);
    }

    public function getindex_tml() {
        $theme = $this->theme;
        if (!empty($theme->templates['index.post'])) return $theme->templates['index.post'];
        return false;
    }

    public function getcont() {
        return $this->parsetml('content.post');
    }

    public function getcontexcerpt($tml_name) {
        ttheme::$vars['post'] = $this;
        //no use self theme because post in other context
        $theme = $this->factory->theme;
        $tml_key = $tml_name == 'excerpt' ? 'excerpt' : $tml_name . '.excerpt';
        return $theme->parse($theme->templates['content.excerpts.' . $tml_key]);
    }

    public function getrsslink() {
        if ($this->hascomm) {
            return $this->parsetml('content.post.rsslink');
        }
        return '';
    }

    public function onrssitem($item) {
    }

    public function getprevnext() {
        $prev = '';
        $next = '';
        $theme = $this->theme;
        if ($prevpost = $this->prev) {
            ttheme::$vars['prevpost'] = $prevpost;
            $prev = $theme->parse($theme->templates['content.post.prevnext.prev']);
        }
        if ($nextpost = $this->next) {
            ttheme::$vars['nextpost'] = $nextpost;
            $next = $theme->parse($theme->templates['content.post.prevnext.next']);
        }

        if (($prev == '') && ($next == '')) return '';
        $result = strtr($theme->parse($theme->templates['content.post.prevnext']) , array(
            '$prev' => $prev,
            '$next' => $next
        ));
        unset(ttheme::$vars['prevpost'], ttheme::$vars['nextpost']);
        return $result;
    }

    public function getcommentslink() {
        $tml = sprintf('<a href="%s%s#comments">%%s</a>', litepubl::$site->url, $this->getlastcommenturl());
        if (($this->comstatus == 'closed') || !litepubl::$options->commentspool) {
            if (($this->commentscount == 0) && (($this->comstatus == 'closed'))) {
                return '';
            }

            return sprintf($tml, $this->getcmtcount());
        }

        //inject php code
        return sprintf('<?php echo litepubl\tcommentspool::i()->getlink(%d, \'%s\'); ?>', $this->id, $tml);
    }

    public function getcmtcount() {
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

    public function gettemplatecomments() {
        $result = '';
        $page = litepubl::$urlmap->page;
        $countpages = $this->countpages;
        if ($countpages > 1) $result.= $this->theme->getpages($this->url, $page, $countpages);

        if (($this->commentscount > 0) || ($this->comstatus != 'closed') || ($this->pingbackscount > 0)) {
            if (($countpages > 1) && ($this->commentpages < $page)) {
                $result.= $this->getcommentslink();
            } else {
                $result.= $this->factory->templatecomments->getcomments($this->id);
            }
        }

        return $result;
    }

    public function gethascomm() {
        return ($this->data['comstatus'] != 'closed') && ((int)$this->data['commentscount'] > 0);
    }

    public function getexcerptcontent() {
        $posts = $this->factory->posts;
        if ($this->revision < $posts->revision) $this->update_revision($posts->revision);
        $result = $this->excerpt;
        $posts->beforeexcerpt($this, $result);
        $result = $this->replacemore($result, true);
        if (litepubl::$options->parsepost) {
            $result = $this->theme->parse($result);
        }
        $posts->afterexcerpt($this, $result);
        return $result;
    }

    public function replacemore($content, $excerpt) {
        $more = $this->parsetml($excerpt ? 'content.excerpts.excerpt.morelink' : 'content.post.more');
        $tag = '<!--more-->';
        if ($i = strpos($content, $tag)) {
            return str_replace($tag, $more, $content);
        } else {
            return $excerpt ? $content : $more . $content;
        }
    }

    protected function getteaser() {
        $content = $this->filtered;
        $tag = '<!--more-->';
        if ($i = strpos($content, $tag)) {
            $content = substr($content, $i + strlen($tag));
            if (!strbegin($content, '<p>')) $content = '<p>' . $content;
            return $content;
        }
        return '';
    }

    protected function getcontentpage($page) {
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

    public function getcontent() {
        $result = '';
        $posts = $this->factory->posts;
        $posts->beforecontent($this, $result);
        if ($this->revision < $posts->revision) $this->update_revision($posts->revision);
        $result.= $this->getcontentpage(litepubl::$urlmap->page);
        if (litepubl::$options->parsepost) {
            $result = $this->theme->parse($result);
        }
        $posts->aftercontent($this, $result);
        return $result;
    }

    public function setcontent($s) {
        if (!is_string($s)) {
            $this->error('Error! Post content must be string');
        }

        $this->rawcontent = $s;
        Filter::i()->filterpost($this, $s);
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

    public function getrawcontent() {
        if (($this->id > 0) && ($this->data['rawcontent'] === false)) {
            $this->data['rawcontent'] = $this->rawdb->getvalue($this->id, 'rawcontent');
        }

        return $this->data['rawcontent'];
    }

    protected function getrawdb() {
        return $this->getdb('rawposts');
    }

    public function getpage($i) {
        if (isset($this->data['pages'][$i])) return $this->data['pages'][$i];
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

    public function gethaspages() {
        return ($this->pagescount > 1) || ($this->commentpages > 1);
    }

    public function getpagescount() {
        return $this->data['pagescount'] + 1;
    }

    public function getcountpages() {
        return max($this->pagescount, $this->commentpages);
    }

    public function getcommentpages() {
        if (!litepubl::$options->commentpages || ($this->commentscount <= litepubl::$options->commentsperpage)) return 1;
        return ceil($this->commentscount / litepubl::$options->commentsperpage);
    }

    public function getlastcommenturl() {
        $c = $this->commentpages;
        $url = $this->url;
        if (($c > 1) && !litepubl::$options->comments_invert_order) $url = rtrim($url, '/') . "/page/$c/";
        return $url;
    }

    public function clearcache() {
        litepubl::$urlmap->setexpired($this->idurl);
    }

    public function getschemalink() {
        return 'post';
    }

    //author
    protected function getauthorname() {
        return $this->getusername($this->author, false);
    }

    protected function getauthorlink() {
        return $this->getusername($this->author, true);
    }

    protected function getusername($id, $link) {
        if ($id <= 1) {
            if ($link) {
                return sprintf('<a href="%s/" rel="author" title="%2$s">%2$s</a>', litepubl::$site->url, litepubl::$site->author);
            } else {
                return litepubl::$site->author;
            }
        } else {
            $users = $this->factory->users;
            if (!$users->itemexists($id)) return '';
            $item = $users->getitem($id);
            if (!$link || ($item['website'] == '')) return $item['name'];
            return sprintf('<a href="%s/users.htm%sid=%s">%s</a>', litepubl::$site->url, litepubl::$site->q, $id, $item['name']);
        }
    }

    public function getauthorpage() {
        $id = $this->author;
        if ($id <= 1) {
            return sprintf('<a href="%s/" rel="author" title="%2$s">%2$s</a>', litepubl::$site->url, litepubl::$site->author);
        } else {
            $pages = $this->factory->userpages;
            if (!$pages->itemexists($id)) return '';
            $pages->id = $id;
            if ($pages->url == '') return '';
            return sprintf('<a href="%s%s" title="%3$s" rel="author"><%3$s</a>', litepubl::$site->url, $pages->url, $pages->name);
        }
    }

} //class