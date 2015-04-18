<?php

function jsafter($section, $src, $dst) {
$js = tjsmerger::i();
    if (!isset($js->items[$section])) return false;
    if (!($src = $js->normfilename($src))) return false;
      if (in_array($dst, $js->items[$section]['files'])) return false;
    if (!($dst = $js->normfilename($dst))) return false;
    if (false === ($i = array_search($src, $js->items[$section]['files']))) {
      //simple add
      $js->items[$section]['files'][] = $dst;
    } else {
      //insert after
      array_splice($js->items[$section]['files'], $i + 1, 0, array($dst));
    }
    $js->save();
  }

function update597() {
  litepublisher::$site->jquery_version = '1.11.2';
litepublisher::$site->jqueryui_version = '1.11.4';
litepublisher::$site->save();

$js = tjsmerger::i();
$js->lock();
jsafter('default', 
'/js/plugins/jquery.cookie.min.js',
'/js/plugins/tojson.min.js');

  jsafter('default', 
'/js/litepublisher/litepublisher.utils.min.js',
'/js/litepublisher/ready2.min.js');

  jsafter('default', 
'/js/litepublisher/ready2.min.js',
'/js/litepublisher/css-loader.min.js');

  jsafter('default', 
'/js/litepublisher/css-loader.min.js',
'/js/litepublisher/json-rpc.min.js');

//move files
$js->replacefile('default',
'/js/litepublisher/widgets.bootstrap.min.js',
'/js/bootstrap/widgets.bootstrap.min.js'
);

$js->replacefile('default',
'/js/litepublisher/dialog.pretty.min.js',
'/js/prettyphoto/litepubl/dialog.pretty.min.js'
);

$js->replacefile('default',
'/js/litepublisher/dialog.bootstrap.min.js',
'/js/bootstrap/dialog.bootstrap.min.js',
);

$js->replacefile('default',
'/js/litepublisher/pretty.init.min.js',
'/js/prettyphoto/litepubl/pretty.init.min.js',
);

$js->replacefile('default',
'/js/litepublisher/youtubefix.min.js',
'/js/prettyphoto/litepubl/youtubefix.min.js'
);

$js->replacefile('admin',
'/js/litepublisher/admin.min.js',
'/js/admin/admin.min.js'
);

$js->replacefile('admin',
'/js/litepublisher/calendar.min.js',
'/js/admin/calendar.min.js'
);

$js->replacefile('adminviews',
'/js/litepublisher/admin.views.min.js',
'/js/admin/admin.views.min.js'
);

$js->replacefile('posteditor',
'/js/litepublisher/uploader.min.js',
'/js/admin/uploader.min.js'
);

$js->replacefile('posteditor',
'/js/litepublisher/uploader.html.min.js',
'/js/admin/uploader.html.min.js'
);

$js->replacefile('posteditor',
'/js/litepublisher/uploader.flash.min.js',
'/js/admin/uploader.flash.min.js'
);

$js->replacefile('posteditor',
'/js/litepublisher/posteditor.min.js',
'/js/admin/posteditor.min.js'
);

$js->replacefile('posteditor',
'/js/litepublisher/fileman.min.js',
'/js/admin/fileman.min.js'
);

$js->replacefile('posteditor',
'/js/litepublisher/fileman.templates.min.js',
'/js/admin/fileman.templates.min.js'
);

  $section = 'media';
  $js->add($section, '/js/mediaelement/mediaelement-and-player.min.js');
  $language = litepublisher::$options->language;
  if ($language != 'en') $js->add($section, "/lib/languages/$language/mediaplayer.min.js");

$js->unlock();

$css = tcssmerger::i();
$css->lock();
$css->replacefile('default',
'/js/litepublisher/css/prettyphoto.dialog.min.css',
'/js/prettyphoto/litepubl/dialog.pretty.js'
);

$css->replacefile('admin',
'/js/litepublisher/css/fileman.min.css',
'/js/admin/css/fileman.min.css'
);

$css->replacefile('admin',
'/js/litepublisher/css/calendar.css',
'/js/admin/css/calendar.css'
);

$css->replacefile('admin',
'/js/litepublisher/css/admin.views.min.css',
'/js/admin/css/admin.views.min.css'
);

$css->unlock();

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
$t->footer = str_replace('2013', '2015', $t->footer);

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