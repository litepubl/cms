<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmincommentmanager extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    return parent::gethead() . tuitabs::gethead();
  }
  
  public function getcontent() {
    $result = '';
    $cm = tcommentmanager::i();
    $options = litepublisher::$options;
    $html = $this->gethtml('comments');
    $lang = tlocal::admin('commentmanager');
    $args = new targs();
    $tabs = new tuitabs();
    $args->comstatus = tadminhtml::array2combo(array(
    'closed' => $lang->closed,
    'reg' => $lang->reg,
    'guest' => $lang->guest,
    'comuser' => $lang->comuser
    ), $options->comstatus);
    
    $args->filterstatus = $cm->filterstatus;
    $args->commentsapproved = $cm->defstatus == 'approved';
    $args->checkduplicate = $cm->checkduplicate;
    $args->commentsdisabled  = $options->commentsdisabled;
    $args->pingenabled  = $options->pingenabled;
    
    $tabs->add($lang->options, '
    [combo=comstatus]
    [checkbox=filterstatus]
    [checkbox=commentsapproved]
    [checkbox=checkduplicate]
    [checkbox=commentsdisabled]
    [checkbox=pingenabled]
    ');
    
    $args->commentpages  = $options->commentpages;
    $args->commentsperpage  = $options->commentsperpage;
    $args->comments_invert_order  = $options->comments_invert_order;
    $args->hidelink =  $cm->hidelink;
    $args->redir = $cm->redir;
    $args->nofollow = $cm->nofollow;
    
    $tabs->add($lang->templates, '
    [checkbox=commentpages]
    [text=commentsperpage]
    [checkbox=comments_invert_order]
    [checkbox=hidelink]
    [checkbox=redir]
    [checkbox=nofollow]
    ');
    
    $rss =  trssholdcomments::i();
    $args->rsscount = $rss->count;
    $args->rsstemplate = $rss->template;
    
    $tabs->add($lang->holdrss, '
    <h4><a href="$site.url/rss/holdcomments.xml">$lang.holdrss</a></h4>
    [text=rsscount]
    [editor=rsstemplate]
    ');
    
    $args->canedit = $cm->canedit;
    $args->candelete = $cm->candelete;
    $args->confirmlogged = $cm->confirmlogged;
    $args->confirmguest = $cm->confirmguest ;
    $args->confirmcomuser = $cm->confirmcomuser;
    $args->confirmemail = $cm->confirmemail;
    
    $tabs->add($lang->perms, '
    [checkbox=canedit]
    [checkbox=candelete]
    [checkbox=confirmlogged]
    [checkbox=confirmguest]
    [checkbox=confirmcomuser]
    [checkbox=confirmemail]
    ');
    
    $args->sendnotification = $cm->sendnotification;
    $args->comuser_subscribe = $cm->comuser_subscribe;
    
    $useroptions = tuseroptions::i();
    $args->defaultsubscribe = $useroptions->defvalues['subscribe'] == 'enabled';
    $args->authorpost_subscribe = $useroptions->defvalues['authorpost_subscribe'] == 'enabled';
    
    $subscribe = tsubscribers::i();
    $args->locklist = $subscribe->locklist;
    $args->subscribe_enabled = $subscribe->enabled;
    
    $tab = new tuitabs();
    $tab->add($lang->options, '
    [checkbox=sendnotification]
    [checkbox=defaultsubscribe]
    [checkbox=subscribe_enabled]
    [checkbox=authorpost_subscribe]
    [checkbox=comuser_subscribe]
    ');
    
    $tab->add($lang->locklist, '[editor=locklist]');
    
    $tabs->add($lang->subscribe, $tab->get());
    
    $mesgtabs = new tuitabs();
    $tc = ttemplatecomments::i();
    foreach (array('logged', 'reqlogin', 'regaccount', 'guest', 'comuser', 'loadhold') as $name) {
      $args->$name = $tc->$name;
      $mesgtabs->add($lang->$name, "[editor=$name]");
    }
    $tabs->add($lang->mesgtabs, $mesgtabs->get());
    $args->formtitle = $lang->title;
    return $html->adminform($tabs->get(), $args);
  }
  
  public function processform() {
    extract($_POST, EXTR_SKIP);
    $options = litepublisher::$options;
    $cm = tcommentmanager::i();
    $cm->lock();
    
    $options->comstatus = $comstatus;
    $cm->filterstatus =isset($filterstatus);
    $cm->defstatus = isset($commentsapproved) ? 'approved' : 'hold';
    $cm->checkduplicate = isset($checkduplicate);
    
    $useroptions = tuseroptions::i();
    $useroptions->defvalues['subscribe'] = isset($defaultsubscribe) ? 'enabled' : 'disabled';
    $useroptions->defvalues['authorpost_subscribe'] = isset($authorpost_subscribe) ? 'enabled' : 'disabled';
    $useroptions->save();
    
    $options->commentsdisabled  = isset($commentsdisabled);
    $options->pingenabled  = isset($pingenabled);
    $options->commentpages  = isset($commentpages);
    $options->commentsperpage  = (int) trim($commentsperpage);
    $options->comments_invert_order  = isset($comments_invert_order);
    
    $cm->sendnotification = isset($sendnotification);
    $cm->hidelink =  isset($hidelink);
    $cm->redir = isset($redir);
    $cm->nofollow = isset($nofollow);
    
    $cm->comuser_subscribe = isset($comuser_subscribe);
    
    $cm->unlock();
    
    $tc = ttemplatecomments::i();
    foreach (array('logged', 'reqlogin', 'regaccount', 'guest', 'comuser', 'loadhold') as $name) {
      $tc->$name = $_POST[$name];
    }
    $tc->save();
    
    $subscr = tsubscribers::i();
    $subscr->lock();
    $subscr->locklist = $locklist;
    $subscr->enabled = isset($subscribe_enabled);
    $subscr->unlock();
    
    $rss =  trssholdcomments::i();
    $rss->count = $rsscount;
    $rss->template = $rsstemplate;
    $rss->save();
  }
  
}//class