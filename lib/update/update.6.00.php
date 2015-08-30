<?php
function update600() {
$mparser = tmediaparser::i();
if (!isset($mparser->data['midlewidth'])) {
    $mparser->data['midlewidth'] = 760;
    $mparser->data['midleheight'] = 570;
    $mparser->data['enablemidle'] = true;
$mparser->save();
}

$man = tdbmanager::i();
if (!$man->column_exists('files', 'midle')) {
$man->alter('files', "add midle int unsigned NOT NULL default '0' after parent");
$man->alter('files', 'add key (midle)');
}

if (litepublisher::$classes->exists('ulogin')) {
unset(litepublisher::$classes->items['adminulogin']);

$js = tjsmerger::i();
$js->lock();
  $js->delete('default', '/plugins/ulogin/resource/' . litepublisher::$options->language . '.ulogin.popup.min.js');
  $js->deletefile('default', '/plugins/ulogin/resource/' . litepublisher::$options->language . '.email.auth.min.js');

  $js->add('default', '/plugins/ulogin/resource/' . litepublisher::$options->language . '.authdialog.min.js');
  $js->add('default', '/plugins/ulogin/resource/authdialog.min.js');
$js->unlock();

  tcssmerger::i()->deletefile('default', '/plugins/ulogin/resource/ulogin.popup.css');

$ulogin = ulogin::i();
$ulogin->panel = '<div id="ulogin-autoinit"></div>';
    unset($ulogin->data['button']);
$ulogin->save();

  $alogin = tadminlogin::i();
  $alogin ->widget = ulogin_deletepanel($alogin ->widget);
$alogin->widget .= $ulogin->panel;
  $alogin->save();
  
  $areg = tadminreguser::i();
  $areg->widget = ulogin_deletepanel($areg->widget);
$areg->widget .= $ulogin->panel;
  $areg->save();
}
}

function ulogin_deletepanel($s) {
    $open = '<!--ulogin-->';
    $close = '<!--/ulogin-->';
    if (false !== ($i = strpos($s, $open))) {
      if ($j = strpos($s, $close)) {
        $s = trim(substr($s, 0, $i)) .
        trim(substr($s, $j + strlen($close) + 1));
      } else {
        $s = trim(substr($s, 0, $i));
      }
    }
    return $s;
  }