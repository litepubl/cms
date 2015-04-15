<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class Tadminoptions extends tadminmenu {
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
      
      case 'home':
      $home = thomepage::i();
      $tabs = new tuitabs();
      $args->image = $home->image;
      $args->parsetags = $home->parsetags;
      $args->showmidle = $home->showmidle;
      $args->midlecat = tposteditor::getcombocategories(array(), $home->midlecat);
      $args->showposts = $home->showposts;
      $args->invertorder = $home->invertorder;
      $args->showpagenator = $home->showpagenator;
      
      $args->idhome =  $home->id;
      $menus = tmenus::i();
      $args->homemenu =  $menus->home;
      $tabs->add($lang->options, '
      [checkbox=homemenu]
      [text=image]
      [checkbox=parsetags]
      [checkbox=showmidle]
      [combo=midlecat]
      [checkbox=showposts]
      [checkbox=invertorder]
      [checkbox=showpagenator]
      ');
      
      $tabs->add($lang->includecats,
      $html->h4->includehome .
      tposteditor::getcategories($home->includecats));
      
      $tabs->add($lang->excludecats,
      $html->h4->excludehome . str_replace('category-', 'exclude_category-',
      tposteditor::getcategories($home->excludecats)));
      
      $args->formtitle = $lang->homeform;
      return tuitabs::gethead() .
      $html->adminform(
    '<h4><a href="$site.url/admin/menu/edit/{$site.q}id=$idhome">$lang.hometext</a></h4>' .
      $tabs->get(), $args);
      
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
      $args->stylebefore = $themeparser->stylebefore;
      
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
      [checkbox=stylebefore]
      ', $args);
      break;
      
      case 'files':      $parser = tmediaparser::i();
      $args->enablepreview = $parser->enablepreview;
      $args->ratio = $parser->ratio;
      $args->clipbounds = $parser->clipbounds;
      $args->previewwidth = $parser->previewwidth;
      $args->previewheight = $parser->previewheight;
      
      $args->maxwidth = $parser->maxwidth;
      $args->maxheight = $parser->maxheight;
      $args->alwaysresize = $parser->alwaysresize;
      
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
      
      <h4>$lang.previewsize</h4>
      [checkbox=enablepreview]
      [checkbox=ratio]
      [checkbox=clipbounds]
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
      $args->commentspull = $options->commentspull;
      $args->memcache_classes = litepublisher::$classes->memcache;
      
      $args->formtitle = $lang->optionscache;
      $result = $html->adminform('
      [checkbox=enabledcache]
      [text=expiredcache]
      [checkbox=ob_cache]
      [checkbox=admincache]
      [checkbox=commentspull]
      [checkbox=memcache_classes]
      ', $args);
      
      $form = new adminform($args);
      $form->submit = 'clearcache';
      $result .= $form->get();
      return $result;
      
      case 'catstags':
      case 'lite': //old version suports
      $args->litearch= litepublisher::$classes->archives->lite;
      $cats = litepublisher::$classes->categories;
      $args->litecats= $cats->lite;
      $args->parentcats = $cats->includeparents;
      $args->childcats = $cats->includechilds;
      $tags = litepublisher::$classes->tags;
      $args->litetags = $tags->lite;
      $args->parenttags = $tags->includeparents;
      $args->childtags = $tags->includechilds;
      $lang = tlocal::admin('options');
      $args->formtitle = $lang->catstags;
      $html = $this->html;
      return $html->adminform('[checkbox=litearch]
      [checkbox=litecats] [checkbox=parentcats] [checkbox=childcats]
      [checkbox=litetags] [checkbox=parenttags] [checkbox=childtags]', $args) .
      $html->p->notecatstags;
      
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
      $args->removephp = tthemeparser::i()->removephp;
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
      
      case 'home':
      $home = thomepage::i();
      $home->lock();
      $home->image = $image;
      $home->parsetags = isset($parsetags);
      $home->showmidle = isset($showmidle);
      $home->midlecat = (int) $midlecat;
      $home->showposts = isset($showposts);
      $home->invertorder = isset($invertorder);
      $home->includecats = tadminhtml::check2array('category-');
      $home->excludecats = tadminhtml::check2array('exclude_category-');
      $home->showpagenator = isset($showpagenator);
      $home->postschanged();
      $home->unlock();
      
      $menus = tmenus::i();
      $menus->home = isset($homemenu);
      $menus->save();
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
      $themeparser->stylebefore = isset($stylebefore);
      $themeparser->save();
      
      // restore style after
      if (!$themeparser->stylebefore) {
        $css = '<link type="text/css" href="$site.files$template.cssmerger_default" rel="stylesheet" />';
        $t = ttemplate::i();
        if (false !== strpos($t->heads, "<!--$css-->")) {
          $t->heads = str_replace("<!--$css-->", $css, $t->heads);
          $t->save();
        }
      }
      break;
      
      case 'files':
      $parser = tmediaparser::i();
      $parser->enablepreview = isset($enablepreview);
      $parser->ratio = isset($ratio);
      $parser->clipbounds = isset($clipbounds);
      $parser->previewwidth = (int) trim($previewwidth);
      $parser->previewheight = (int) trim($previewheight);
      
      $parser->maxwidth = (int) trim($maxwidth);
      $parser->maxheight = (int) trim($maxheight);
      $parser->alwaysresize = isset($alwaysresize);
      
      $parser->quality_snapshot= (int) trim($quality_snapshot);
      $parser->quality_original = (int) trim($quality_original);
      
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
        $classes = litepublisher::$classes;
        if (        $classes->memcache != isset($memcache_classes)) {
          if (isset($memcache_classes)) $classes->revision_memcache++;
          $classes->memcache = isset($memcache_classes);
          $classes->save();
        }
        
        $options->lock();
        $options->cache = isset($enabledcache);
        $options->admincache = isset($admincache );
        if (!empty($expiredcache)) $options->expiredcache = (int) $expiredcache;
        $options->ob_cache = isset($ob_cache);
        $options->compress = isset($compress);
        $options->commentspull = isset($commentspull);
        $options->unlock();
      }
      break;
      
      case 'lite':
      case 'catstags':
      litepublisher::$classes->archives->lite = isset($litearch);
      $cats = litepublisher::$classes->categories;
      $cats->lite = isset($litecats);
      $cats->includeparents = isset($parentcats);
      $cats->includechilds = isset($childcats);
      $cats->save();
      $tags = litepublisher::$classes->tags;
      $tags->lite = isset($litetags);
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