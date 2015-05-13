<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tcategoriesmenuInstall($self) {
  $categories = tcategories::i();
  $categories->changed = $self->buildtree;
  $self->buildtree();
  
  tadminviews::replacemenu('tmenus', get_class($self));
}

function tcategoriesmenuUninstall($self) {
  tadminviews::replacemenu(get_class($self), 'tmenus');
  
  $categories = tcategories::i();
  $categories->unbind($self);
}