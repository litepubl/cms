
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
  litepublisher::$site->jquery_version = '1.11.3';
litepublisher::$site->jqueryui_version = '1.11.4';
litepublisher::$site->save();

$home = thomepage::i();
    $home->data['smallimage'] = '';
$home->save();

$fs = tfiles::i();
if (isset($fs->data['videoplayer'])) {
    unset($fs->data['videoplayer']);
$fs->save();
}

$js = tjsmerger::i();
$js->lock();

$system = array(
'/js/plugins/tojson.min.js',
'/js/plugins/fontfaceobserver.js',

// litepubl/system
'/js/litepubl/system/css-loader.min.js',
'/js/litepubl/system/escape.min.js',
'/js/litepubl/system/get_get.min.js',
'/js/litepubl/system/html-comments.min.js',
'/js/litepubl/system/json-rpc.min.js',
'/js/litepubl/system/load-font.min.js',
'/js/litepubl/system/load-script.min.js',
'/js/litepubl/system/parsetml.min.js',

//litepubl/common
'/js/litepubl/common/litepubl.namespace.min.js',
'/js/litepubl/common/litepubl.init.min.js',
'/js/litepubl/common/dialog.min.js',
'/js/litepubl/common/players.min.js',
'/js/litepubl/common/templates.min.js',
'/js/litepubl/common/widgets.min.js',

  '/js/litepubl/effects/homeimage.min.js'
);

$prev = '/js/plugins/jquery.cookie.min.js';
foreach ($system as $filename) {
  jsafter('default', $prev, $filename);
$prev = $filename;
}

$delete = array(
'/js/litepublisher/litepublisher.utils.min.js',
'/js/litepublisher/simpletml.min.js',
'/js/litepublisher/templates.min.js',
'/js/litepublisher/filelist.min.js',
'/js/litepublisher/dialog.min.js',
'/js/litepublisher/players.min.js',
'/js/litepublisher/widgets.min.js',
);

foreach ($delete as $filename) {
$js->deletefile('default', $filename);
}

$js->replacefile('default',
'/js/litepublisher/widgets.bootstrap.min.js',
'/js/litepubl/bootstrap/widgets.bootstrap.min.js'
);

$js->replacefile('default',
'/js/litepublisher/dialog.pretty.min.js',
'/js/litepubl/pretty/dialog.pretty.min.js'
);

$js->replacefile('default',
'/js/litepublisher/dialog.bootstrap.min.js',
'/js/litepubl/bootstrap/dialog.bootstrap.min.js'
);


$js->replacefile('default',
'/js/litepublisher/pretty.init.min.js',
'/js/litepubl/pretty/pretty.init.min.js'
);

$js->replacefile('default',
'/js/litepublisher/youtubefix.min.js',
'/js/litepubl/pretty/youtubefix.min.js'
);

$js->replacefile('comments',
'/js/litepublisher/comments.min.js',
'/js/litepubl/coments/comments.template.min.js'
);

  jsafter('comments', 
'/js/litepubl/coments/comments.template.min.js',
'/js/litepubl/coments/comments.quote.min.js'
);

$js->replacefile('comments',
'/js/litepublisher/confirmcomment.min.js',
'/js/litepubl/comments/confirmcomment.min.js'
);

$js->replacefile('comments',
'/js/litepublisher/moderate.min.js',
'/js/litepubl/comments/moderate.min.js'
);

$js->replacefile('admin',
'/js/litepublisher/admin.min.js',
'/js/litepubl/admin/admin.min.js'
);

$js->replacefile('admin',
'/js/litepublisher/calendar.min.js',
'/js/litepubl/admin/calendar.min.js'
);

$js->replacefile('adminviews',
'/js/litepublisher/admin.views.min.js',
'/js/litepubl/admin/admin.views.min.js'
);

$js->replacefile('posteditor',
'/js/litepublisher/uploader.min.js',
'/js/litepubl/admin/uploader.min.js'
);

$js->replacefile('posteditor',
'/js/litepublisher/uploader.html.min.js',
'/js/litepubl/admin/uploader.html.min.js'
);

$js->replacefile('posteditor',
'/js/litepublisher/uploader.flash.min.js',
'/js/litepubl/admin/uploader.flash.min.js'
);

$js->replacefile('posteditor',
'/js/litepublisher/posteditor.min.js',
'/js/litepubl/admin/posteditor.min.js'
);

$js->replacefile('posteditor',
'/js/litepublisher/fileman.min.js',
'/js/litepubl/admin/fileman.min.js'
);

jsafter('posteditor',
'/js/litepubl/admin/fileman.min.js',
'/js/litepubl/admin/fileman.browser.min.js'
);

jsafter('posteditor',
'/js/litepubl/admin/fileman.browser.min.js',
'/js/litepubl/admin/fileman.propedit.min.js'
);

$js->replacefile('posteditor',
'/js/litepublisher/fileman.templates.min.js',
'/js/litepubl/admin/fileman.templates.min.js'
);

  $section = 'media';
  $js->add($section, '/js/mediaelement/mediaelement-and-player.min.js');
  $language = litepublisher::$options->language;
  if ($language != 'en') $js->add($section, "/lib/languages/$language/mediaplayer.min.js");

      if (in_array('/js/prettyphoto/js/jquery.prettyPhoto.js', $js->items['default']['files'])) {
$js->add('default',    '/js/litepubl/pretty/player.pretty.min.js');
}

$js->unlock();

$css = tcssmerger::i();
$css->lock();
$css->replacefile('default',
'/js/litepublisher/css/prettyphoto.dialog.min.css',
'/js/litepubl/pretty/dialog.pretty.min.css'
);

$css->replacefile('default',
'/js/litepublisher/css/button.min.css',
	'/js/litepubl/deprecated/css/button.min.css'
);

$css->replacefile('default',
'/js/litepublisher/css/filelist.min.css',
'/js/litepubl/common/css/filelist.min.css'
);

$css->replacefile('default',
'/js/litepublisher/css/form-inline.min.css',
'/js/litepubl/common/css/form-inline.min.css'
);

$css->replacefile('default',
'/js/litepublisher/css/table.min.css',
'/js/litepubl/deprecated/css/table.min.css'
);

$css->add('default', '/js/litepubl/common/css/common.min.css');
$css->add('default', '/js/litepubl/deprecated/css/align.min.css');
   $css->add('default', '/js/litepubl/effects/css/homeimage.min.css');
$css->deletetext('default', 'hidden');

$css->replacefile('admin',
'/js/litepublisher/css/fileman.min.css',
'/js/litepubl/admin/css/fileman.min.css'
);

$css->replacefile('admin',
'/js/litepublisher/css/calendar.css',
'/js/litepubl/admin/css/calendar.css'
);

$css->replacefile('admin',
'/js/litepublisher/css/admin.views.min.css',
'/js/litepubl/admin/css/admin.views.min.css'
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

$cs = '<link type="text/css" href="$site.files$template.cssmerger_default" rel="stylesheet" />';        
$t->heads = str_replace("<!--$cs-->", $cs, $t->heads);

$t->save();

if (!litepublisher::$classes->exists('appcache_manifest')) {
//$a = tprefetchtxt::i()->items;
$data = new tdata();
$data->basename = 'prefetch.txt';
$data->load();

litepublisher::$classes->delete('tprefetchtxt');
litepublisher::$classes->add('appcache_manifest', 'appcache.manifest.class.php');
$c = appcache_manifest::i();
if (isset($data->data['items'])) {
$c->items = $data->data['items'];
$c->save();
}
tredirector::i()->add('/prefetch.txt', $c->url);
}

unset(litepublisher::$classes->interfaces['imenu']);
litepublisher::$classes->delete('tmenuwidget');
litepublisher::$classes->save();

$parser = tthemeparser::i();
if (isset($parser->data['stylebefore'])) {
unset($parser->data['stylebefore']);
$parser->save();
}

if (tview::i()->theme->name == 'default') {
tplugins::i()->add('bootstrap-theme');
}
}