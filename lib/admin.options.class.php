<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tadminoptions extends tadminmenu {
  private $_form;
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getautoform($name) {
    if (isset($this->_form)) return $this->_form;
    switch ($name) {
      case 'rss':
      $form = new tautoform(trss::i(), 'options', 'rssoptions');
      $form->add($form->feedburner, $form->feedburnercomments, $form->template('editor'));
      break;
      
      case 'ping':
      $form = new tautoform(tpinger::i(), 'options', 'optionsping');
      $form->add($form->enabled, $form->services('editor'));
      break;
      
      case 'notfound404':
      $form = new tautoform(tnotfound404::i(), 'options', 'edit404');
      $form->add($form->notify, $form->text('editor'));
      break;
      
      default:
      return false;
    }
    $this->_form = $form;
    return $form;
  }
  
  public function getcontent() {
    if ($form = $this->getautoform($this->name)) return $form->getform();
    $options = litepublisher::$options;
    $template = ttemplate::i();
    ttheme::$vars['template'] = $template;
    $result = '';
    $args = new targs();
    $lang = tlocal::admin('options');
    $html = $this->html;
    
    switch ($this->name) {
      case 'options':
      $site = litepublisher::$site;
      $args->fixedurl = $site->fixedurl;
      $args->redirdom = litepublisher::$urlmap->redirdom;
      $args->url = $site->url;
      $args->name = $site->name;
      $args->description = $site->description;
      $args->keywords = $site->keywords;
      $args->author = $site->author;
      $args->footer= $template->footer;
      
      $args->formtitle = $lang->options;
      return $html->adminform('
      [checkbox=fixedurl]
      [checkbox=redirdom]
      [text=url]
      [text=name]
      [text=description]
      [text=keywords]
      [text=author]
      [editor=footer]
      ', $args);
      
      case 'mail':
      $args->adminemail = $options->email;
      $args->fromemail = $options->fromemail;
      $args->mailer = $options->mailer == 'smtp';
      
      $subscribers = tsubscribers::i();
      $args->subscribeemail = $subscribers->fromemail;
      
      $mailer = TSMTPMailer ::i();
      $args->host = $mailer->host;
      $args->smtplogin = $mailer->login;
      $args->password = $mailer->password;
      $args->port = $mailer->port;
      
      $args->formtitle = $lang->mailoptions;
      return $html->adminform('
      [text=adminemail]
      [text=fromemail]
      [text=subscribeemail]
      [checkbox=mailer]
      [text=host]
      [text=smtplogin]
      [password=password]
      [text=port]
      ', $args);
      
      case'view':
      $args->perpage = $options->perpage;
      $filter = tcontentfilter::i();
      $args->usefilter = $filter->usefilter;
      $args->automore = $filter->automore;
      $args->automorelength = $filter->automorelength;
      $args->autolinks = $filter->autolinks;
      $args->commentautolinks = $filter->commentautolinks;
      $args->icondisabled = $options->icondisabled;
      $args->hidefilesonpage = $options->hidefilesonpage;
      
      $themeparser = tthemeparser::i();
      $args->replacelang = $themeparser->replacelang;
      
      $args->formtitle = $lang->viewoptions;
      return $html->adminform('
      [text=perpage]
      [checkbox=usefilter]
      [checkbox=automore]
      [text=automorelength]
      [checkbox=autolinks]
      [checkbox=commentautolinks]
      [checkbox=hidefilesonpage]
      [checkbox=icondisabled]
      [checkbox=replacelang]
      ', $args);
      break;
      
      case 'files':
      $parser = tmediaparser::i();
      $args->previewwidth = $parser->previewwidth;
      $args->previewheight = $parser->previewheight;
      $args->previewmode = $html->getradioitems('previewmode', array(
      'fixed' => $lang->fixedsize,
      'max' => $lang->maxsize,
      'min' => $lang->minsize,
      'none' => $lang->disablepreview,
      ), $parser->previewmode);
      
      $args->maxwidth = $parser->maxwidth;
      $args->maxheight = $parser->maxheight;
      $args->alwaysresize = $parser->alwaysresize;
      
      $args->enablemidle = $parser->enablemidle;
      $args->midlewidth = $parser->midlewidth;
      $args->midleheight = $parser->midleheight;
      
      $args->quality_original = $parser->quality_original;
      $args->quality_snapshot = $parser->quality_snapshot;
      
      $args->audioext = $parser->audioext;
      $args->videoext = $parser->videoext;
      
      $args->video_width = litepublisher::$site->video_width;
      $args->video_height = litepublisher::$site->video_height;
      
      $args->formtitle = $lang->files;
      return $html->adminform('
      <h4>$lang.imagesize</h4>
      [checkbox=alwaysresize]
      [text=maxwidth]
      [text=maxheight]
      [text=quality_original]
      
      [checkbox=enablemidle]
      [text=midlewidth]
      [text=midleheight]
      
      <h4>$lang.previewoptions</h4>
      $previewmode
      [text=previewwidth]
      [text=previewheight]
      [text=quality_snapshot]
      
      <h4>$lang.extfile</h4>
      [text=audioext]
      [text=videoext]
      
      [text=video_width]
      [text=video_height]
      ', $args);
      break;
      
      case 'links':
      $linkgen = tlinkgenerator::i();
      $args->urlencode = $linkgen->urlencode;
      $args->post = $linkgen->post;
      $args->menu = $linkgen->menu;
      $args->category = $linkgen->category;
      $args->tag = $linkgen->tag;
      $args->archive = $linkgen->archive;
      
      $args->formtitle = $lang->schemalinks;
      return $html->adminform('
      <p>$lang.taglinks</p>
      [checkbox=urlencode]
      [text=post]
      [text=menu]
      [text=category]
      [text=tag]
      [text=archive]
      ', $args);
      
      case 'cache':
      $args->enabledcache = $options->cache;
      $args->expiredcache = $options->expiredcache;
      $args->admincache = $options->admincache;
      $args->ob_cache = $options->ob_cache;
      $args->compress = $options->compress;
      $args->commentspool = $options->commentspool;
      
      $args->formtitle = $lang->optionscache;
      $result = $html->adminform('
      [checkbox=enabledcache]
      [text=expiredcache]
      [checkbox=ob_cache]
      [checkbox=admincache]
      [checkbox=commentspool]
      ', $args);
      
      $form = new adminform($args);
      $form->submit = 'clearcache';
      $result .= $form->get();
      return $result;
      
      case 'catstags':
      case 'lite': //old version suports
      $cats = litepublisher::$classes->categories;
      $args->parentcats = $cats->includeparents;
      $args->childcats = $cats->includechilds;
      
      $tags = litepublisher::$classes->tags;
      $args->parenttags = $tags->includeparents;
      $args->childtags = $tags->includechilds;
      $lang = tlocal::admin('options');
      $args->formtitle = $lang->catstags;
      $html = $this->html;
      return $html->adminform('
      [checkbox=parentcats] [checkbox=childcats]
      [checkbox=parenttags] [checkbox=childtags]', $args);
      
      case 'robots':
      $html = $this->html;
      $args->formtitle = 'robots.txt';
      $args->robots = trobotstxt::i()->text;
      $args->appcache = appcache_manifest::i()->text;
      $tabs = new tuitabs();
      $tabs->add('robots.txt', '[editor=robots]');
      $tabs->add('manifest.appcache', '[editor=appcache]');
      return tuitabs::gethead() . $html->adminform($tabs->get(), $args);
      break;
      
      case 'secure':
      $args->echoexception = $options->echoexception;
      $args->usersenabled = $options->usersenabled;
      $args->reguser = $options->reguser;
      $args->parsepost = $options->parsepost;
      $args->show_draft_post = $options->show_draft_post ;
      $args->xxxcheck = $options->xxxcheck;
      $filter = tcontentfilter::i();
      $args->phpcode = $filter->phpcode;
$parser = tthemeparser::i();
      $args->removephp = $parser->removephp;
      $args->removespaces = $parser->removespaces;

      $args->useshell = tupdater::i()->useshell;
      $backuper = tbackuper::i();
      $args->filertype = tadminhtml::array2combo(array(
      'auto' => 'auto',
      'file' => 'file',
      'ftp' => 'ftp',
      'ftpsocket' => 'ftpsocket',
      //'ssh2' => 'ssh2'
      ), $backuper->filertype);
      
      $args->formtitle = $lang->securehead;
      $result = $html->adminform('
      [checkbox=echoexception]
      [checkbox=xxxcheck]
      [checkbox=usersenabled]
      [checkbox=reguser]
      [checkbox=removephp]
[checkbox=removespaces]
      [checkbox=phpcode]
      [checkbox=parsepost]
      [checkbox=show_draft_post]
      [combo=filertype]
      [checkbox=useshell]
      ', $args);
      
      $form = new adminform($args);
      $form->title = $lang->changepassword;
      $args->oldpassword = '';
      $args->newpassword = '';
      $args->repassword = '';
      $form->items = '[password=oldpassword]
      [password=newpassword]
      [password=repassword]';
      
      $form->submit = 'changepassword';
      $result .= $form->get();
      return $result;
    }
    
  $result  = $this->html->{$this->name}($args);
    return $this->html->fixquote($result);
  }
  
  public function processform() {
    if ($form = $this->getautoform($this->name)) return $form->processform();
    extract($_POST, EXTR_SKIP);
    $options = litepublisher::$options;
    
    switch ($this->name) {
      case 'options':
      litepublisher::$urlmap->redirdom = isset($redirdom);
      $site = litepublisher::$site;
      $site->fixedurl = isset($fixedurl);
      $site->url = $url;
      $site->name = $name;
      $site->description = $description;
      $site->keywords = $keywords;
      $site->author = $author;
      $this->getdb('users')->setvalue(1, 'name', $author);
      ttemplate::i()->footer = $footer;
      break;
      
      case 'mail':
      if(!empty($adminemail)) {
        $options->email = $adminemail;
        $this->getdb('users')->setvalue(1, 'email', $adminemail);
      }
      
      if(!empty($fromemail)) $options->fromemail = $fromemail;
      $options->mailer = empty($mailer) ? '': 'smtp';
      
      if (!empty($subscribeemail )) {
        $subscribe = tsubscribers::i();
        $subscribe->fromemail = $subscribeemail ;
        $subscribe->save();
        $options->fromemail = $subscribeemail ;
      }
      
      $mailer = TSMTPMailer ::i();
      $mailer->lock();
      $mailer->host = $host;
      $mailer->login = $smtplogin;
      $mailer->password = $password;
      $mailer->port= (int) $port;
      $mailer->unlock();
      break;
      
      case 'view':
      $options->icondisabled = isset($icondisabled);
      if (!empty($perpage)) $options->perpage = (int) $perpage;
      $options->hidefilesonpage = isset($hidefilesonpage);
      $filter = tcontentfilter::i();
      $filter->usefilter = isset($usefilter);
      $filter->automore = isset($automore);
      $filter->automorelength = (int) $automorelength;
      $filter->autolinks = isset($autolinks);
      $filter->commentautolinks = isset($commentautolinks);
      $filter->save();
      
      $themeparser = tthemeparser::i();
      $themeparser->replacelang = isset($replacelang );
      $themeparser->save();
      break;
      
      case 'files':
      $parser = tmediaparser::i();
      $parser->previewmode = $previewmode;
      $parser->previewwidth = (int) trim($previewwidth);
      $parser->previewheight = (int) trim($previewheight);
      
      $parser->maxwidth = (int) trim($maxwidth);
      $parser->maxheight = (int) trim($maxheight);
      $parser->alwaysresize = isset($alwaysresize);
      
      $parser->quality_snapshot= (int) trim($quality_snapshot);
      $parser->quality_original = (int) trim($quality_original);
      
      $parser->enablemidle = isset($enablemidle);
      $parser->midlewidth = (int) trim($midlewidth);
      $parser->midleheight = (int) trim($midleheight);
      
      $parser->audioext = trim($audioext);
      $parser->videoext = trim($videoext);
      
      $parser->save();
      
      litepublisher::$site->video_width = $video_width;
      litepublisher::$site->video_height = $video_height;
      break;
      
      case 'links':
      $linkgen = tlinkgenerator::i();
      $linkgen->urlencode = isset($urlencode);
      if (!empty($post)) $linkgen->post = $post;
      if (!empty($menu)) $linkgen->menu = $menu;
      if (!empty($category)) $linkgen->category = $category;
      if (!empty($tag)) $linkgen->tag = $tag;
      if (!empty($archive)) $linkgen->archive = $archive;
      $linkgen->save();
      break;
      
      case 'cache':
      if (isset($clearcache)) {
        ttheme::clearcache();
      } else {
        $options->lock();
        $options->cache = isset($enabledcache);
        $options->admincache = isset($admincache );
        if (!empty($expiredcache)) $options->expiredcache = (int) $expiredcache;
        $options->ob_cache = isset($ob_cache);
        $options->compress = isset($compress);
        $options->commentspool = isset($commentspool);
        $options->unlock();
      }
      break;
      
      case 'lite':
      case 'catstags':
      $cats = litepublisher::$classes->categories;
      $cats->includeparents = isset($parentcats);
      $cats->includechilds = isset($childcats);
      $cats->save();
      
      $tags = litepublisher::$classes->tags;
      $tags->includeparents = isset($parenttags);
      $tags->includechilds = isset($childtags);
      $tags->save();
      break;
      
      case 'robots':
      $robo = trobotstxt::i();
      $robo->text = $robots;
      $robo->save();
      
      $appcache_manifest  = appcache_manifest::i();
      $appcache_manifest->text = $appcache;
      $appcache_manifest->save();
      break;
      
      case 'secure':
      if (isset($_POST['oldpassword'])) {
        $h2 = $this->html->h2;
        if ($oldpassword == '') return $h2->badpassword;
        if (($newpassword == '') || ($newpassword != $repassword))  return $h2->difpassword;
        if (!$options->auth($options->email, $oldpassword)) return $h2->badpassword;
        $options->changepassword($newpassword);
        $options->logout();
        return $h4->passwordchanged;
      } else {
        $options->echoexception = isset($echoexception);
        $options->reguser = isset($reguser);
        $this->usersenabled = isset($usersenabled);
        $options->parsepost = isset($parsepost);
        $options->show_draft_post  = isset($show_draft_post);
        $options->xxxcheck = isset($xxxcheck);
        $filter = tcontentfilter::i();
        $filter->phpcode = isset($phpcode);
        $filter->save();
        
        $parser = tthemeparser::i();
        $parser->removephp =isset($removephp );
        $parser->removespaces =isset($removespaces);
        $parser->save();

        $parser = adminparser::i();
        $parser->removephp =isset($removephp );
        $parser->removespaces =isset($removespaces);
        $parser->save();

                $backuper = tbackuper::i();
        if ($backuper->filertype != $filertype) {
          $backuper->filertype = $filertype;
          $backuper->save();
        }
        
        $useshell = isset($useshell);
        $updater = tupdater::i();
        if ($useshell !== $updater->useshell) {
          $updater->useshell = $useshell;
          $updater->save();
        }
      }
      break;
    }
    
    return '';
  }
  
  public function setusersenabled($value) {
    if (litepublisher::$options->usersenabled == $value) return;
    litepublisher::$options->usersenabled = $value;
    $menus = tadminmenus::i();
    $menus->lock();
    if ($value) {
      if (!$menus->url2id('/admin/users/')) {
        $id = $menus->createitem(0, 'users', 'admin', 'tadminusers');
        $menus->createitem($id, 'pages', 'author', 'tadminuserpages');
        $menus->createitem($id, 'groups', 'admin', 'tadmingroups');
        $menus->createitem($id, 'options', 'admin', 'tadminuseroptions');
        $menus->createitem($id, 'perms', 'admin', 'tadminperms');
        $menus->createitem($id, 'search', 'admin', 'tadminusersearch');
        
        $menus->createitem($menus->url2id('/admin/posts/'),
        'authorpage', 'author', 'tadminuserpages');
      }
    } else {
      $menus->deletetree($menus->url2id('/admin/users/'));
      $menus->deleteurl('/admin/posts/authorpage/');
      
    }
    $menus->unlock();
  }
  
}//class