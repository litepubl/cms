<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function tpolltypesInstall($self) {
  $lang = tlocal::admin('polls');
  $theme = ttheme::i();
  $res = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  $self->data['closed'] = $theme->replacelang(file_get_contents($res . 'microformat.tml') , $lang);
  $self->data['itemclosed'] = '';

  $ini = parse_ini_file($res . 'types.ini', true);
  foreach ($ini as $type => $item) {
    foreach ($item as $k => $v) $item[$k] = str_replace("'", '"', $v);
    $item['type'] = $type;
    $item['item'] = $theme->replacelang($item['item'], $lang);
    $item['opened'] = $theme->replacelang($item['opened'], $lang);
    if (isset($item['closed'])) $item['closed'] = $theme->replacelang($item['closed'], $lang);
    if (isset($item['itemclosed'])) $item['itemclosed'] = $theme->replacelang($item['itemclosed'], $lang);
    $self->add($item);
  }
  $self->save();
}