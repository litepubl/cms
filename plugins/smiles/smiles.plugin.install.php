<?php

function tsmilesInstall($self) {
  $filter = tcontentfilter::i();
  $filter->lock();
  $filter->onsimplefilter = $self->filter;
  $filter->oncomment = $self->filter;
  $filter->unlock();
  
  tposts::i()->addrevision();
}

function tsmilesUninstall($self) {
  tcontentfilter::i()->unbind($self);
  tposts::i()->addrevision();
}