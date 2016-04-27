<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\post;
use litepubl\core\Context;
use litepubl\view\Lang;
use litepubl\view\Filter;
use litepubl\core\Str;
use litepubl\core\Arr;
use litepubl\view\Args;
use litepubl\view\Theme;

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

    public static function getInstancename() {
        return 'post';
    }

    public static function getChildtable() {
        return '';
    }

    public static function selectitems(array $items) {
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
            'idschema' => 1,
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
            'comstatus' =>  $this->getApp()->options->comstatus,
            'pingenabled' =>  $this->getApp()->options->pingenabled,
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
            $coinstance =  $this->getApp()->classes->newinstance($class);
            $coinstance->post = $this;
            $this->coinstances[] = $coinstance;
        }
    }

    public function getFactory() {
        return Factory::i();
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

    public static function getAssoc($id) {
        $db =  $this->getApp()->db;
        return $db->selectassoc("select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
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
        return  $this->getApp()->router->add($this->url, get_class($this) , (int)$this->id);
    }

    public function onid() {
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

    public function setOnid($call) {
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

    public function getPrev() {
        if (!is_null($this->aprev)) {
            return $this->aprev;
        }

        $this->aprev = false;
        if ($id = $this->db->findid("status = 'published' and posted < '$this->Str::sqlDate' order by posted desc")) {
            $this->aprev = static ::i($id);
        }
        return $this->aprev;
    }

    public function getNext() {
        if (!is_null($this->anext)) {
            return $this->anext;
        }

        $this->anext = false;
        if ($id = $this->db->findid("status = 'published' and posted > '$this->Str::sqlDate' order by posted asc")) {
            $this->anext = static ::i($id);
        }
        return $this->anext;
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
        $this->data['title'] = tcontentfilter::escape(tcontentfilter::unescape($title));
    }

    public function getTheme() {
        if ($this->themeInstance) {
$this->themeInstance->setvar('post', $this);
            return $this->themeInstance;
        }

$mainview = $this->factory->mainview;
        $this->themeInstance = $mainview->schema ? $mainview->schema->theme : Schema::getSchema($this)->theme;
$this->themeInstance->setvar('post', $this);
        return $this->themeInstance;
    }

    public function parsetml($path) {
        $theme = $this->theme;
        return $theme->parse($theme->templates[$path]);
    }

    public function getExtra() {
        $theme = $this->theme;
        return $theme->parse($theme->extratml);
    }

    public function getBookmark() {
        return $this->theme->parse('<a href="$post.link" rel="bookmark" title="$lang.permalink $post.title">$post.iconlink$post.title</a>');
    }

    public function getRsscomments() {
        return  $this->getApp()->site->url . "/comments/$this->id.xml";
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

    public function getIdimage() {
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

    public function getImage() {
        if ($id = $this->getidimage()) {
            return $this->factory->files->geturl($id);
        }

        return false;
    }

    public function getThumb() {
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

    public function getFirstimage() {
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

        $tags = Str::begin($name, 'tag') ? $this->factory->tags : $this->factory->categories;
        $tags->loaditems($items);

        $args = new Args();
        $list = array();

        foreach ($items as $id) {
            $item = $tags->getitem($id);
            $args->add($item);
            if (($item['icon'] == 0) ||  $this->getApp()->options->icondisabled) {
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

    public function getDate() {
        return Lang::date($this->posted, $this->theme->templates['content.post.date']);
    }

    public function getExcerptdate() {
        return Lang::date($this->posted, $this->theme->templates['content.excerpts.excerpt.date']);
    }

    public function getDay() {
        return date($this->posted, 'D');
    }

    public function getMonth() {
        return Lang::date($this->posted, 'M');
    }

    public function getYear() {
        return date($this->posted, 'Y');
    }

    public function getMorelink() {
        if ($this->moretitle) {
            return $this->parsetml('content.excerpts.excerpt.morelink');
        }

        return '';
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

    //ITemplate
    public function request(Context $context) {
        $this->loadItem($context->id);
$app = $this->getApp();
        if ($this->status != 'published') {
            if (! $app->options->show_draft_post) {
$context->response->status = 404;
                return;
            }

            $groupname =  $app->options->group;
            if (($groupname == 'admin') || ($groupname == 'editor')) {
                return;
            }

            if ($this->author ==  $app->options->user) {
                return;
            }

$context->response->status = 404;
            return;
        }
    }

    public function getTitle() {
        return $this->data['title'];
    }

    public function getHead() {
        $result = $this->rawhead;
        $this->factory->mainview->ltoptions['idpost'] = $this->id;
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

    public function getAnhead() {
        $result = '';
        $this->factory->posts->callevent('onanhead', array(
            $this, &$result
        ));
        return $result;
    }

    public function getKeywords() {
        return empty($this->data['keywords']) ? $this->Gettagnames() : $this->data['keywords'];
    }
    //fix for file version. For db must be deleted
    public function setKeywords($s) {
        $this->data['keywords'] = $s;
    }

    public function getDescription() {
        return $this->data['description'];
    }

    public function getIdschema() {
        return $this->data['idschema'];
    }

    public function setIdschema($id) {
        if ($id != $this->idschema) {
            $this->data['idschema'] = $id;
            if ($this->id) $this->db->setvalue($this->id, 'idschema', $id);
        }
    }

    public function setId_view($id_view) {
        $this->data['idschema'] = $id_view;
    }

    public function getIconurl() {
        if ($this->icon == 0) {
 return '';
}


        $files = $this->factory->files;
        if ($files->itemexists($this->icon)) {
 return $files->geturl($this->icon);
}


        $this->icon = 0;
        $this->save();
        return '';
    }

    public function getIconlink() {
        if (($this->icon == 0) ||  $this->getApp()->options->icondisabled) {
 return '';
}


        $files = $this->factory->files;
        if ($files->itemexists($this->icon)) {
 return $files->geticon($this->icon);
}


        $this->icon = 0;
        $this->save();
        return '';
    }

    public function setFiles(array $list) {
        Arr::clean($list);
        $this->data['files'] = $list;
    }

    public function getFilelist() {
        if ((count($this->files) == 0) || (( $this->getApp()->router->page > 1) &&  $this->getApp()->options->hidefilesonpage)) {
            return '';
        }

        $files = $this->factory->files;
        return $files->getfilelist($this->files, false);
    }

    public function getExcerptfilelist() {
        if (count($this->files) == 0) {
 return '';
}


        $files = $this->factory->files;
        return $files->getfilelist($this->files, true);
    }

    public function getIndex_tml() {
        $theme = $this->theme;
        if (!empty($theme->templates['index.post'])) {
 return $theme->templates['index.post'];
}


        return false;
    }

    public function getCont() {
        return $this->parsetml('content.post');
    }

    public function getContexcerpt($tml_name) {
        Theme::$vars['post'] = $this;
        //no use self theme because post in other context
        $theme = $this->factory->theme;
        $tml_key = $tml_name == 'excerpt' ? 'excerpt' : $tml_name . '.excerpt';
        return $theme->parse($theme->templates['content.excerpts.' . $tml_key]);
    }

    public function getRsslink() {
        if ($this->hascomm) {
            return $this->parsetml('content.post.rsslink');
        }
        return '';
    }

    public function onrssitem($item) {
    }

    public function getPrevnext() {
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

    public function getCommentslink() {
        $tml = sprintf('<a href="%s%s#comments">%%s</a>',  $this->getApp()->site->url, $this->getlastcommenturl());
        if (($this->comstatus == 'closed') || ! $this->getApp()->options->commentspool) {
            if (($this->commentscount == 0) && (($this->comstatus == 'closed'))) {
                return '';
            }

            return sprintf($tml, $this->getcmtcount());
        }

        //inject php code
        return sprintf('<?php echo litepubl\tcommentspool::i()->getlink(%d, \'%s\'); ?>', $this->id, $tml);
    }

    public function getCmtcount() {
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

    public function getTemplatecomments() {
        $result = '';
        $page =  $this->getApp()->router->page;
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

    public function getHascomm() {
        return ($this->data['comstatus'] != 'closed') && ((int)$this->data['commentscount'] > 0);
    }

    public function getExcerptcontent() {
        $posts = $this->factory->posts;
        if ($this->revision < $posts->revision) $this->update_revision($posts->revision);
        $result = $this->excerpt;
        $posts->beforeexcerpt($this, $result);
        $result = $this->replacemore($result, true);
        if ( $this->getApp()->options->parsepost) {
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

    protected function getTeaser() {
        $content = $this->filtered;
        $tag = '<!--more-->';
        if ($i = strpos($content, $tag)) {
            $content = substr($content, $i + strlen($tag));
            if (!Str::begin($content, '<p>')) $content = '<p>' . $content;
            return $content;
        }
        return '';
    }

    protected function getContentpage($page) {
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

    public function getContent() {
        $result = '';
        $posts = $this->factory->posts;
        $posts->beforecontent($this, $result);
        if ($this->revision < $posts->revision) $this->update_revision($posts->revision);
        $result.= $this->getcontentpage( $this->getApp()->router->page);
        if ( $this->getApp()->options->parsepost) {
            $result = $this->theme->parse($result);
        }
        $posts->aftercontent($this, $result);
        return $result;
    }

    public function setContent($s) {
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

    public function clearcache() {
         $this->getApp()->router->setexpired($this->idurl);
    }

    public function getSchemalink() {
        return 'post';
    }

    //author
    protected function getAuthorname() {
        return $this->getusername($this->author, false);
    }

    protected function getAuthorlink() {
        return $this->getusername($this->author, true);
    }

    protected function getUsername($id, $link) {
        if ($id <= 1) {
            if ($link) {
                return sprintf('<a href="%s/" rel="author" title="%2$s">%2$s</a>',  $this->getApp()->site->url,  $this->getApp()->site->author);
            } else {
                return  $this->getApp()->site->author;
            }
        } else {
            $users = $this->factory->users;
            if (!$users->itemexists($id)) {
 return '';
}


            $item = $users->getitem($id);
            if (!$link || ($item['website'] == '')) {
 return $item['name'];
}


            return sprintf('<a href="%s/users.htm%sid=%s">%s</a>',  $this->getApp()->site->url,  $this->getApp()->site->q, $id, $item['name']);
        }
    }

    public function getAuthorpage() {
        $id = $this->author;
        if ($id <= 1) {
            return sprintf('<a href="%s/" rel="author" title="%2$s">%2$s</a>',  $this->getApp()->site->url,  $this->getApp()->site->author);
        } else {
            $pages = $this->factory->userpages;
            if (!$pages->itemexists($id)) {
 return '';
}


            $pages->id = $id;
            if ($pages->url == '') {
 return '';
}


            return sprintf('<a href="%s%s" title="%3$s" rel="author"><%3$s</a>',  $this->getApp()->site->url, $pages->url, $pages->name);
        }
    }

}