<?php

function twidgetscacheInstall($self) {
  litepublisher::$urlmap->onclearcache = $self->onclearcache;
}

function twidgetscacheUninstall($self) {
  turlmap::unsub($self);
}