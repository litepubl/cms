<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\posts;
use litepubl\post\Posts as PostItems;
use litepubl\post\Post;
use litepubl\view\Filter;
use litepubl\view\MainView;
use litepubl\view\Base;
use litepubl\view\Vars;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\admin\AuthorRights;
use litepubl\admin\DateFilter;
use litepubl\core\DB;
use litepubl\tag\Cats;

class Editor extends \litepubl\admin\Menu
{
    public $idpost;
    protected $isauthor;

    public function gethead() {
        $result = parent::gethead();

        $mainView = MainView::i();
        $mainView->ltoptions['idpost'] = $this->idget();
        $result.= $mainView->getjavascript($template->jsmerger_posteditor);

        if ($this->isauthor && ($h = AuthorRights::i()->gethead())) {
            $result.= $h;
        }

        return $result;
    }

    public static function getcombocategories(array $items, $idselected) {
        $result = '';
        $categories = Cats::i();
        $categories->loadall();

        if (!count($items)) {
            $items = array_keys($categories->items);
        }

        foreach ($items as $id) {
            $result.= sprintf('<option value="%s" %s>%s</option>',
 $id, $id == $idselected ? 'selected' : '', Base::quote($categories->getvalue($id, 'title')));
        }

        return $result;
    }

    protected function getcategories(tpost $post) {
        $postitems = $post->categories;
        $categories = Cats::i();
        if (!count($postitems)) {
            $postitems = array(
                $categories->defaultid
            );
        }

        return $this->admintheme->getcats($postitems);
    }

    public function getvarpost($post) {
        if (!$post) {
            return Base::$vars['post'];
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
        $Ajax= Ajax::i();
        return $ajax->gettext($post->rawcontent, $this->admintheme);
    }

    public function canrequest() {
        tlocal::admin()->searchsect[] = 'editor';
        $this->isauthor = false;
        $this->basename = 'editor';
        $this->idpost = $this->idget();
        if ($this->idpost > 0) {
            $posts = PostItems::i();
            if (!$posts->itemexists($this->idpost)) {
                return 404;
            }
        }

        $post = Post::i($this->idpost);
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
        $args->title = Filter::unescape($post->title);
    }

    public function getcontent() {
        $result = '';
        $admintheme = $this->admintheme;
        $lang = tlocal::admin('editor');
        $args = new targs();

        $post = $this->idpost ? Post::i($this->idpost) : $this->newpost();
        $vars = new Vars();
        $vars->post = $post;
        $vars->posteditor = $this;

        if ($post->id != 0) {
            $result.= $admintheme->h($lang->formhead . $post->bookmark);
        }

        if ($this->isauthor && ($r = AuthorRights::i()->getposteditor($post, $args))) {
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

    protected function processfiles(tpost $post) {
        if (isset($_POST['files'])) {
            $post->files = DB::str2array(trim($_POST['files'], ', '));
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
        $post = $id ? Post::i($id) : $this->newpost();

        if ($this->isauthor && ($r = AuthorRights::i()->editpost($post))) {
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