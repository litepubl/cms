<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

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
      $result.= sprintf('<option value="%s" %s>%s</option>', $id, $id == $idselected ? 'selected' : '', tadminhtml::specchars($categories->getvalue($id, 'title')));
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

public function getvarpost($post ) {
if (!$post) {
return  basetheme::$vars['post'];
}

return $post;
}

public function getajaxlink($idpost) {
    return litepublisher::$site->url . '/admin/ajaxposteditor.htm' . litepublisher::$site->q . "id=$idpost&get";
}

public function gettabs($post = null) {
$post = $this->getvarpost($post);
$args = new targs();
$this->getargstab($post, $args);

$admintheme = $this->admintheme;
return $admintheme->parsearg($admintheme->templates['posteditor.tabs'], $args);
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
    if (!litepublisher::$options->hasgroup('editor')) {
      if (litepublisher::$options->hasgroup('author')) {
        $this->isauthor = true;
        if (($post->id != 0) && (litepublisher::$options->user != $post->author)) {
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
    $html = $this->html;
    $args = new targs();

    $post = tpost::i($this->idpost);
$vars = new themevars();
$vars->post = $post;
$vars->posteditor = $this;

if ($post->id == 0) {
$result .= $admintheme->h($lang->formhead . ' ' . $post->bookmark);
}

    if ($this->isauthor && ($r = tauthor_rights::i()->getposteditor($post, $args))) {
return $r;
}

$args->title = $post->title;
$args->id = $post->id;
$result .= $admintheme->parsearg($admintheme->templates['posteditor'], $args);
return $result;
  }

  protected function set_post(tpost $post) {
    extract($_POST, EXTR_SKIP);
    $post->title = $title;

    $post->categories = $this->admintheme->processcategories();

    if (($post->id == 0) && (litepublisher::$options->user > 1)) $post->author = litepublisher::$options->user;
    if (isset($tags)) $post->tagnames = $tags;
    if (isset($icon)) $post->icon = (int)$icon;
    if (isset($idview)) $post->idview = $idview;
    if (isset($files)) {
      $files = trim($files, ', ');
      $post->files = tdatabase::str2array($files);
    }
    if (isset($posted) && $posted) {
      $post->posted = datefilter::getdate('posted');
    }

    if (isset($status)) {
      $post->status = $status == 'draft' ? 'draft' : 'published';
      $post->comstatus = $comstatus;
      $post->pingenabled = isset($pingenabled);
      $post->idperm = (int)$idperm;
      if ($password != '') $post->password = $password;
    }

    if (isset($url)) {
      $post->url = $url;
      $post->title2 = $title2;
      $post->keywords = $keywords;
      $post->description = $description;
      $post->rawhead = $head;
    }

    $post->content = $raw;
    if (isset($excerpt)) $post->excerpt = $excerpt;
    if (isset($rss)) $post->rss = $rss;
    if (isset($more)) $post->moretitle = $more;
    if (isset($filtered)) $post->filtered = $filtered;
    if (isset($upd)) {
      $update = sprintf($this->lang->updateformat, tlocal::date(time()) , $upd);
      $post->content = $post->rawcontent . "\n\n" . $update;
    }

  }

  public function processform() {
    $this->basename = 'editor';
$lang = $this->lang;
    if (empty($_POST['title'])) {
return $this->admintheme->geterr($lang->error, $lang->emptytitle);
}

    $id = (int)$_POST['id'];
    $post = tpost::i($id);

    if ($this->isauthor && ($r = tauthor_rights::i()->editpost($post))) {
      $this->idpost = $post->id;
      return $r;
    }

    $this->set_post($post);
    $posts = tposts::i();
    if ($id == 0) {
      $this->idpost = $posts->add($post);
      $_POST['id'] = $this->idpost;
    } else {
      $posts->edit($post);
    }
    $_GET['id'] = $this->idpost;

    return $this->admintheme->success($lang->success);
  }

} //class