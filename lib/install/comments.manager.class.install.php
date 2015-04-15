<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcommentmanagerInstall($self) {
  $self->data['filterstatus'] = true;
  $self->data['checkduplicate'] = true;
  $self->data['defstatus'] = 'approved';
  
  $self->data['sendnotification'] =  true;
  $self->data['trustlevel'] = 2;
  $self->data['hidelink'] = false;
  $self->data['redir'] = true;
  $self->data['nofollow'] = false;
  $self->data['canedit'] =  true;
  $self->data['candelete'] =  true;
  
  $self->data['confirmlogged'] = false;
  $self->data['confirmguest'] = true;
  $self->data['confirmcomuser'] = true;
  $self->data['confirmemail'] = false;
  
  $self->data['comuser_subscribe'] = true;
  
  $self->data['idguest'] =  0; //create user in installer after create users table
  
  $groups = litepublisher::$options->groupnames;
  $self->data['idgroups'] = array($groups['admin'], $groups['editor'], $groups['moderator'], $groups['author'], $groups['commentator']);
  
  $self->save();
  
  $comments = tcomments::i();
  $comments->lock();
  $comments->changed = $self->changed;
  $comments->added = $self->sendmail;
  $comments->unlock();
  
  litepublisher::$urlmap->addget('/comusers.htm', get_class($self));
  
  trobotstxt ::i()->AddDisallow('/comusers.htm');
}

function tcommentmanagerUninstall($self) {
  turlmap::unsub($self);
}