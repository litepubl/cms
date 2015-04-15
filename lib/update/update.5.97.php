<?php
function update597() {
  litepublisher::$site->jquery_version = '1.11.2';
litepublisher::$site->jqueryui_version = '1.11.4';
litepublisher::$site->save();

$js = tjsmerger::i();
$js->lock();
$js->after('default', 
'/js/plugins/jquery.cookie.min.js',
'/js/plugins/tojson.min.js');

  $js->after('default', 
'/js/litepublisher/litepublisher.utils.min.js',
'/js/litepublisher/ready2.min.js');

  $js->after('default', 
'/js/litepublisher/ready2.min.js',
'/js/litepublisher/css-loader.min.js');

  $js->after('default', 
'/js/litepublisher/css-loader.min.js',
'/js/litepublisher/json-rpc.min.js');

$js->unlock();

$css = tcssmerger::i();
//$css->add('default', '/js/litepublisher/css/hover.css');

if (litepublisher::$classes->exists('ulogin')) {
$ulogin = ulogin::i();
$ulogin->panel = str_replace(' ready2', ' $.ready2', $ulogin->panel);
$ulogin->save();

  $alogin = tadminlogin::i();
  $alogin ->widget = $ulogin->addpanel($alogin ->widget, $ulogin->panel);
  $alogin->save();
  
  $areg = tadminreguser::i();
  $areg->widget = $ulogin->addpanel($areg->widget, $ulogin->panel);
  $areg->save();
  
  $tc = ttemplatecomments::i();
  $tc->regaccount = $ulogin->addpanel($tc->regaccount, $ulogin->button);
  $tc->save();
}

$t = ttemplate::i();
$t->footer = str_replace('2014', '2015', $t->footer);
    $template = ttemplate::i();
      $t->data[$js->basename] = $js->revision;
      $t->data[$css->basename] = $css->revision;
$t->save();


//$a = tprefetchtxt::i()->items;
$data = new tdata();
$data->basename = 'prefetch.txt';
$data->load();
$a = $data->data['items'];
litepublisher::$classes->delete('tprefetchtxt');
litepublisher::$classes->add('appcache_manifest', 'appcache.manifest.class.php');
$c = appcache_manifest::i();
$c->items = $a;
$c->save();

tredirector::i()->add('/prefetch.txt', $c->url);
}