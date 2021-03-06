<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

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
