<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\admin\posts;

use litepubl\admin\DateFilter;
use litepubl\core\Str;
use litepubl\post\Post;
use litepubl\post\Posts as PostItems;
use litepubl\tag\Cats;
use litepubl\view\Args;
use litepubl\view\Base;
use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\MainView;
use litepubl\view\Vars;

class Editor extends \litepubl\admin\Menu
{
    public $idpost;
    protected $isauthor;

    public function getHead(): string
    {
        $result = parent::gethead();

        $mainView = MainView::i();
        $mainView->ltoptions['idpost'] = $this->idget();
        $result.= $mainView->getJavaScript($mainView->jsmerger_posteditor);
        return $result;
    }

    public static function getComboCategories(array $items, $idselected)
    {
        $result = '';
        $categories = Cats::i();
        $categories->loadall();

        if (!count($items)) {
            $items = array_keys($categories->items);
        }

        foreach ($items as $id) {
            $result.= sprintf('<option value="%s" %s>%s</option>', $id, $id == $idselected ? 'selected' : '', Base::quote($categories->getvalue($id, 'title')));
        }

        return $result;
    }

    protected function getCategories(Post $post)
    {
        $postitems = $post->categories;
        $categories = Cats::i();
        if (!count($postitems)) {
            $postitems = array(
                $categories->defaultid
            );
        }

        return $this->admintheme->getcats($postitems);
    }

    public function getVarPost($post)
    {
        if (!$post) {
            return Base::$vars['post']->post;
        }

        return $post;
    }

    public function getAjaxlink($idpost)
    {
        $site = $this->getApp()->site;
        return $site->url . '/admin/ajaxposteditor.htm' . $site->q . "id=$idpost&get";
    }

    public function getTabs($post = null)
    {
        $post = $this->getVarPost($post);
        $args = new Args();
        $this->getArgsTab($post, $args);
        return $this->admintheme->parseArg($this->getTabsTemplate(), $args);
    }

    public function getTabsTemplate()
    {
        $admintheme = $this->admintheme;
        return strtr(
            $admintheme->templates['tabs'], array(
            '$id' => 'tabs',
            '$tab' => $admintheme->templates['posteditor.tabs.tabs'],
            '$panel' => $admintheme->templates['posteditor.tabs.panels'],
            )
        );
    }

    public function getArgsTab(Post $post, Args $args)
    {
        $args->id = $post->id;
        $args->ajax = $this->getajaxlink($post->id);
        //categories tab
        $args->categories = $this->getCategories($post);

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
    public function getFilelist($post = null)
    {
        $post = $this->getVarPost($post);
        return $this->admintheme->getfilelist($post->id ? $post->factory->files->itemsposts->getitems($post->id) : array());
    }

    public function getText($post = null)
    {
        $post = $this->getVarPost($post);
        $ajax = Ajax::i();
        return $ajax->gettext($post->rawcontent, $this->admintheme);
    }

    public function canRequest()
    {
        Lang::admin()->searchsect[] = 'editor';
        $this->isauthor = false;
        $this->basename = 'editor';
        $this->idpost = $this->idGet();
        if ($this->idpost > 0) {
            $posts = PostItems::i();
            if (!$posts->itemExists($this->idpost)) {
                return 404;
            }
        }

        $post = Post::i($this->idpost);
        if (!$this->getApp()->options->hasgroup('editor')) {
            if ($this->getApp()->options->hasgroup('author')) {
                $this->isauthor = true;
                if (($post->id != 0) && ($this->getApp()->options->user != $post->author)) {
                    return 403;
                }
            }
        }
    }

    public function getTitle(): string
    {
        if ($this->idpost == 0) {
            return parent::gettitle();
        } else {
            if (isset(Lang::admin()->ini[$this->name]['editor'])) {
                return Lang::get($this->name, 'editor');
            }

            return Lang::get('editor', 'editor');
        }
    }

    public function getExternal()
    {
        $this->basename = 'editor';
        $this->idpost = 0;
        return $this->getcontent();
    }

    public function getPostargs(Post $post, Args $args)
    {
        $args->id = $post->id;
        $args->ajax = $this->getajaxlink($post->id);
        $args->title = Filter::unescape($post->title);
    }

    public function getContent(): string
    {
        $result = '';
        $admintheme = $this->admintheme;
        $lang = Lang::admin('editor');
        $args = new Args();

        $post = $this->idpost ? Post::i($this->idpost) : $this->newpost();
        $vars = new Vars();
        $vars->post = $post->getView();
        $vars->posteditor = $this;

        if ($post->id != 0) {
            $result.= $admintheme->h($lang->formhead . $post->view->bookmark);
        }

        $args->id = $post->id;
        $args->title = $post->title;
        $args->adminurl = $this->url;
        $result.= $admintheme->parseArg($admintheme->templates['posteditor'], $args);
        return $result;
    }

    protected function processtab(Post $post)
    {
        extract($_POST, EXTR_SKIP);

        $post->title = $title;
        $post->categories = $this->admintheme->processcategories();

        if (($post->id == 0) && ($this->getApp()->options->user > 1)) {
            $post->author = $this->getApp()->options->user;
        }

        if (isset($tags)) {
            $post->tagnames = $tags;
        }

        if (isset($icon)) {
            $post->icon = (int)$icon;
        }

        if (isset($idschema)) {
            $post->idschema = (int)$idschema;
        }

        if (isset($posted) && $posted) {
            $post->posted = DateFilter::getdate('posted');
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

    protected function processfiles(Post $post)
    {
        if (isset($_POST['files'])) {
            $post->files = Str::toIntArray(trim($_POST['files'], ', '));
        }
    }

    public function newpost()
    {
        return new Post();
    }

    public function canProcess()
    {
        if (empty($_POST['title'])) {
            $lang = Lang::admin('editor');
            return $lang->emptytitle;
        }
    }

    public function afterProcess(Post $post)
    {
    }

    public function processForm()
    {
        $lang = Lang::admin('editor');
        $admintheme = $this->admintheme;

        if ($error = $this->canProcess()) {
            return $admintheme->geterr($lang->error, $error);
        }

        $id = (int)$_POST['id'];
        $post = $id ? Post::i($id) : $this->newPost();
        $this->processTab($post);
        $this->processFiles($post);

        $posts = $post->factory->posts;
        if ($id == 0) {
            $this->idpost = $posts->add($post);
            $_POST['id'] = $this->idpost;
        } else {
            $posts->edit($post);
        }
        $_GET['id'] = $this->idpost;

        $this->afterProcess($post);
        return $admintheme->success($lang->success);
    }
}
