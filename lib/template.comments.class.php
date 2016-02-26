<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class ttemplatecomments extends tevents {

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->basename = 'comments.templates';
  }

  public function getcomments($idpost) {
    $result = '';
    $idpost = (int)$idpost;
    $post = tpost::i($idpost);
    $comments = tcomments::i($idpost);
    $lang = tlocal::i('comment');
    $list = $comments->getcontent();

    $theme = $post->theme;
    $args = new targs();
    $args->count = $post->cmtcount;
    $result.= $theme->parsearg($theme->templates['content.post.templatecomments.comments.count'], $args);
    $result.= $list;

    if ((litepublisher::$urlmap->page == 1) && ($post->pingbackscount > 0)) {
      $pingbacks = tpingbacks::i($post->id);
      $result.= $pingbacks->getcontent();
    }

    if (litepublisher::$options->commentsdisabled || ($post->comstatus == 'closed')) {
      $result.= $theme->parse($theme->templates['content.post.templatecomments.closed']);
      return $result;
    }

    $args->postid = $post->id;
    $args->antispam = base64_encode('superspamer' . strtotime("+1 hour"));

    $cm = tcommentmanager::i();
    // if user can see hold comments
    $result.= sprintf('<?php if (litepublisher::$options->ingroups(array(%s))) { ?>', implode(',', $cm->idgroups));

    $holdmesg = '<?php if ($ismoder = litepublisher::$options->ingroup(\'moderator\')) { ?>' . $theme->templates['content.post.templatecomments.form.mesg.loadhold'] .
    //hide template hold comments in html comment
    '<!--' . $theme->templates['content.post.templatecomments.holdcomments'] . '-->' . '<?php } ?>';

    $args->comment = '';
    $mesg = $theme->parsearg($holdmesg, $args);
    $mesg.= $this->getmesg('logged', $cm->canedit || $cm->candelete ? 'adminpanel' : false);
    $args->mesg = $mesg;

    $result.= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
    $result.= $this->getjs(($post->idperm == 0) && $cm->confirmlogged, 'logged');

    $result.= '<?php } else { ?>';

    switch ($post->comstatus) {
      case 'reg':
        $args->mesg = $this->getmesg('reqlogin', litepublisher::$options->reguser ? 'regaccount' : false);
        $result.= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
        break;


      case 'guest':
        $args->mesg = $this->getmesg('guest', litepublisher::$options->reguser ? 'regaccount' : false);
        $result.= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
        $result.= $this->getjs(($post->idperm == 0) && $cm->confirmguest, 'guest');
        break;


      case 'comuser':
        $args->mesg = $this->getmesg('comuser', litepublisher::$options->reguser ? 'regaccount' : false);

        foreach (array(
          'name',
          'email',
          'url'
        ) as $field) {
          $args->$field = "<?php echo (isset(\$_COOKIE['comuser_$field']) ? \$_COOKIE['comuser_$field'] : ''); ?>";
        }

        $args->subscribe = false;
        $args->content = '';

        $result.= $theme->parsearg($theme->templates['content.post.templatecomments.form'], $args);
        $result.= $this->getjs(($post->idperm == 0) && $cm->confirmcomuser, 'comuser');
        break;
    }

    $result.= '<?php } ?>';

    return $result;
  }

  public function getmesg($k1, $k2) {
    $theme = ttheme::i();
    $result = $theme->templates['content.post.templatecomments.form.mesg.' . $k1];
    if ($k2) {
      $result.= "\n" . $theme->templates['content.post.templatecomments.form.mesg.' . $k2];
    }

    //normalize uri
    $result = str_replace('&backurl=', '&amp;backurl=', $result);

    //insert back url
    $result = str_replace('backurl=', 'backurl=' . urlencode(litepublisher::$urlmap->url) , $result);

    return $theme->parse($result);
  }

  public function getjs($confirmcomment, $authstatus) {
    $cm = tcommentmanager::i();
    $params = array(
      'confirmcomment' => $confirmcomment,
      'comuser' => 'comuser' == $authstatus,
      'canedit' => $cm->canedit,
      'candelete' => $cm->candelete,
      'ismoder' => $authstatus != 'logged' ? false : '<?php echo ($ismoder ? \'true\' : \'false\'); ?>'
    );

    $args = new targs();
    $args->params = json_encode($params);

    $theme = ttheme::i();
    return $theme->parsearg($theme->templates['content.post.templatecomments.form.js'], $args);
  }

} //class