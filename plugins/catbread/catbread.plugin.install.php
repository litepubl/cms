<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function catbreadinstall($self) {
  $self->cats->onbeforecontent = $self->beforecat;
  tthemeparser::i()->parsed = $self->themeparsed;

  $about = tplugins::getabout(basename(dirname(__file__)));
  //bootstrap breadcrumb component
  $self->tml = array(
    'container' => '<div id="breadcrumb-container">%s</div>',
    'items' => '<div id="breadcrumb-items">
  <ol class="breadcrumb" itemprop="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
  $item
  </ol></div>',
    'item' => '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="$link" itemprop="url name">$title</a></li>',
    'active' => '<li class="active" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">$title</li>',
    'child' => '<li><button id="breadcrumbs-toggle" class="btn btn-default" data-target="#breadcrumbs-childs" data-toggle="dropdown"><span class="caret"></span> <span class="sr-only">' . $about['showchilds'] . '</span></button></li>',
    'childitems' => '<div id="breadcrumbs-childs" class="dropdown">
  <ul class="dropdown-menu" role="menu">
  $item
  </ul></div>',
    'childitem' => '<li><a href="$link" title="$title">$title</a>$subitems</li>',
    'childsubitems' => '<ul>$item</ul>',

    'similaritem' => '<a itemprop="sameAs" href="$link">$title</a> ',
    'similaritems' => '<div id="breadcrumbs-similar">' . $about['seealso'] . ' $item</div>',
  );

  $self->save();
}

function catbreaduninstall($self) {
  $self->cats->unbind($self);
  tthemeparser::i()->unbind($self);
}