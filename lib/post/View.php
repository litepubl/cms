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

class View extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
public $post;
    private $prevPost;
    private $nextPost;
    private $themeInstance;

public function __get($name)
{

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
}
    public function getPrev() {
        if (!is_null($this->aprev)) {
            return $this->aprev;
        }

        $this->aprev = false;
        if ($id = $this->db->findid("status = 'published' and posted < '$this->sqldate' order by posted desc")) {
            $this->aprev = static ::i($id);
        }
        return $this->aprev;
    }

    public function getNext() {
        if (!is_null($this->anext)) {
            return $this->anext;
        }

        $this->anext = false;
        if ($id = $this->db->findid("status = 'published' and posted > '$this->sqldate' order by posted asc")) {
            $this->anext = static ::i($id);
        }
        return $this->anext;
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
{
