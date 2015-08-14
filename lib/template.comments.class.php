<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

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
    $idpost = (int) $idpost;
    $post = tpost::i($idpost);
    $comments = tcomments::i($idpost);
    $lang = tlocal::i('comment');
    $list = $comments->getcontent();
    
    $theme = $post->theme;
    $args = new targs();
    $args->count = $post->cmtcount;
    $result .= $theme->parsearg($theme->templates['content.post.templatecomments.comments.count'], $args);
    $result .= $list;
    
    if ((litepublisher::$urlmap->page == 1) && ($post->pingbackscount > 0))  {
      $pingbacks = tpingbacks::i($post->id);
      $result .= $pingbacks->getcontent();
    }
    
    if (!litepublisher::$options->commentsdisabled && ($post->comstatus != 'closed')) {
      $args->postid = $post->id;
      $args->antispam = base64_encode('superspamer' . strtotime ("+1 hour"));
      
      $cm = tcommentmanager::i();
      $result .=  sprintf('<?php if (litepublisher::$options->ingroups(array(%s))) {', implode(',', $cm->idgroups));
        //add hold list because we need container when comment will be hold
        $result .= 'if ($ismoder = litepublisher::$options->ingroup(\'moderator\')) { ?>';
          $args->comment = '';
          $result .= $theme->parsearg($theme->templates['content.post.templatecomments.holdcomments'] .
          $theme->templates['content.post.templatecomments.form.mesg.loadhold'], $args);
        $result .= '<?php } ?>';
        
        $mesg = $theme->templates['content.post.templatecomments.form.mesg.logged'];
        if ($cm->canedit || $cm->candelete) {
          $mesg .= "\n" . $theme->templates['content.post.templatecomments.form.mesg.adminpanel'];
        }
        
        $args->mesg = $this->fixmesg($mesg, $theme);
        $result .= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
        $result .= $this->getjs(($post->idperm == 0) && $cm->confirmlogged, 'logged');
      $result .= '<?php } else { ?>';
        
        switch ($post->comstatus) {
          case 'reg':
          $mesg = $theme->templates['content.post.templatecomments.form.mesg.reqlogin;'];
          if (litepublisher::$options->reguser) {
            $mesg .= "\n" .$theme->templates['content.post.templatecomments.form.mesg.regaccount'];
          }
          
          $args->mesg = $this->fixmesg($mesg, $theme);
          $result .= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
          break;
          
          case 'guest':
          $mesg = $theme->templates['content.post.templatecomments.form.mesg.guest'];
          if (litepublisher::$options->reguser) {
            $mesg .= "\n" . $theme->templates['content.post.templatecomments.form.mesg.regaccount'];
          }
          
          $args->mesg = $this->fixmesg($mesg, $theme);
          $result .= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
          $result .= $this->getjs(($post->idperm == 0) && $cm->confirmguest, 'guest');
          break;
          
          case 'comuser':
          $mesg = $theme->templates['content.post.templatecomments.form.mesg.comuser'];
          if (litepublisher::$options->reguser) {
            $mesg .= "\n" . $theme->templates['content.post.templatecomments.form.mesg.regaccount'];
          }
          
          $args->mesg = $this->fixmesg($mesg, $theme);
          
          foreach (array('name', 'email', 'url') as $field) {
            $args->$field = "<?php echo (isset(\$_COOKIE['comuser_$field']) ? \$_COOKIE['comuser_$field'] : ''); ?>";
          }
          
          $args->subscribe = false;
          $args->content = '';
          
          $result .= $theme->parsearg($theme->templates['content.post.templatecomments.form'], $args);
          $result .= $this->getjs(($post->idperm == 0) && $cm->confirmcomuser, 'comuser');
          break;
        }
        
      $result .= '<?php } ?>';
    } else {
      $result .= $theme->parse($theme->templates['content.post.templatecomments.closed']);
    }
    return $result;
  }
  
  public function fixmesg($mesg, $theme) {
    return $theme->parse(str_replace('backurl=', 'backurl=' . urlencode(litepublisher::$urlmap->url),
    str_replace('&backurl=', '&amp;backurl=', $mesg)));
  }
  
  public function getjs($confirmcomment, $authstatus) {
    $cm = tcommentmanager::i();
    $params = array(
    'confirmcomment' => $confirmcomment,
    'comuser' => 'comuser' == $authstatus,
    'canedit' => $cm->canedit,
    'candelete' => $cm->candelete,
    );

  if ($authstatus == 'logged') {
$params['ismoder'] = '<?php echo ($ismoder ? \'true\' : \'false\'); ?>';
}

$args = new targs();
$args->params = json_encode($params);

$theme = ttheme::i();    
return $theme->parsearg($theme->templates['content.post.templatecomments.form.js'], $args);
  }
  
} //class