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
use litepubl\core\Str;
use litepubl\view\Theme;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\view\MainView;
use litepubl\comments\Templates;

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

public function __get($name)
{
if (method_exists($this, $get = 'get'. $name)) {
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

    public function __set($name, $value) {
if (parent::__set($name, $value)) {
return true;
}

if(isset($this->post->$name)) {
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

    public function getPrev() {
        if (!is_null($this->prevPost)) {
            return $this->prevPost;
        }

        $this->prevPost = false;
        if ($id = $this->db->findid("status = 'published' and posted < '$this->sqldate' order by posted desc")) {
            $this->prevPost = Post::i($id);
        }
        return $this->prevPost;
    }

    public function getNext() {
        if (!is_null($this->nextPost)) {
            return $this->nextPost;
        }

        $this->nextPost = false;
        if ($id = $this->db->findid("status = 'published' and posted > '$this->sqldate' order by posted asc")) {
            $this->nextPost = Post::i($id);
        }
        return $this->nextPost;
    }

    public function getTheme() {
        if ($this->themeInstance) {
$this->themeInstance->setvar('post', $this);
            return $this->themeInstance;
        }

$mainview = MainView::i();
        $this->themeInstance = $mainview->schema ? $mainview->schema->theme : Schema::getSchema($this)->theme;
$this->themeInstance->setvar('post', $this);
        return $this->themeInstance;
    }

    public function parseTml($path) {
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

    public function getFirstImage() {
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

    public function getExcerptDate() {
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

    public function getMoreLink() {
        if ($this->moretitle) {
            return $this->parsetml('content.excerpts.excerpt.morelink');
        }

        return '';
    }


    public function request(Context $context) {
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

$this->context = $context;
    }

public function getPage()
{
return $this->context->request->page;
}

    public function getTitle() {
        return $this->post->title;
    }

    public function getHead() {
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

    public function getAnhead() {
        $result = '';
        $this->factory->posts->callevent('onanhead', array(
            $this, &$result
        ));
        return $result;
    }

    public function getKeywords() {
if ($result = $this->post->keywords) {
return $result;
} else {
return $this->Gettagnames();
}
    }

    public function getDescription() {
        return $this->post->description;
    }

    public function getIdSchema() {
        return $this->post->idschema;
    }

    public function setIdSchema($id) {
        if ($id != $this->idschema) {
            $this->post->idschema = $id;
            if ($this->id) {
$this->post->db->setvalue($this->id, 'idschema', $id);
}
        }
    }

    public function getFileList() {
        if (!count($this->files) || (( $this->page > 1) &&  $this->getApp()->options->hidefilesonpage)) {
            return '';
        }

        $files = $this->factory->files;
        return $files->getFileList($this->files, false);
    }

    public function getExcerptFileList() {
        if (count($this->files) == 0) {
 return '';
}


        $files = $this->factory->files;
        return $files->getfilelist($this->files, true);
    }

    public function getIndexTml() {
        $theme = $this->theme;
        if (!empty($theme->templates['index.post'])) {
 return $theme->templates['index.post'];
}

        return false;
    }

    public function getCont() {
        return $this->parsetml('content.post');
    }

    public function getContExcerpt($tml_name) {
        Theme::$vars['post'] = $this;
        //no use self theme because post in other context
        $theme = Theme::i();
        $tml_key = $tml_name == 'excerpt' ? 'excerpt' : $tml_name . '.excerpt';
        return $theme->parse($theme->templates['content.excerpts.' . $tml_key]);
    }

    public function getRssLink() {
        if ($this->hascomm) {
            return $this->parsetml('content.post.rsslink');
        }
        return '';
    }

    public function onRssItem($item) {
    }

    public function getPrevNext() {
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

    public function getCommentsLink() {
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

    public function getCmtCount() {
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

    public function getTemplateComments() {
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

    public function getHascomm() {
        return ($this->comstatus != 'closed') && ((int)$this->commentscount > 0);
    }

    public function getExcerptContent() {
        $posts = $this->factory->posts;
        if ($this->revision < $posts->revision) {
$this->updateRevision($posts->revision);
}
        $result = $this->excerpt;
        $posts->beforeexcerpt($this, $result);
        $result = $this->replacemore($result, true);
        if ( $this->getApp()->options->parsepost) {
            $result = $this->theme->parse($result);
        }
        $posts->afterexcerpt($this, $result);
        return $result;
    }

    public function replaceMore($content, $excerpt) {
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

    protected function getContentPage($page) {
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
        if ($this->revision < $posts->revision) {
$this->updateRevision($posts->revision);
}

        $result .= $this->getContentPage( $this->page);
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
    //author
    protected function getAuthorname() {
        return $this->getusername($this->author, false);
    }

    protected function getAuthorLink() {
        return $this->getusername($this->author, true);
    }

    protected function getUserName($id, $link) {
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

    public function getAuthorPage() {
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
{

}