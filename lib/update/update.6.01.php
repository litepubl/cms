<?php
function update601() {
$classes = litepublisher::$classes;
$classes->items['tfilestorage'] = array('kernel.php', '', 'storage.file.class.php');
$classes->items['tstorage'] = array('kernel.php', '', 'storage.class.php');
$classes->items['memstorage'] = array('kernel.php', '', 'storage.mem.class.php');
unset($classes->items['tlitememcache']);
unset($classes->items['tfilecache']);
$classes->items['cachestorage_memcache'] = array('kernel.php', '', 'storage.cache.memcache.class.php');
$classes->items['cachestorage_file'] = array('kernel.php', '', 'storage.cache.file.class.php');

$classes->items['tautoform'] = array('kernel.admin.php', '', 'html.autoform.class.php');
$classes->items['ttablecolumns'] = array('kernel.admin.php', '', 'html.tablecols.class.php');
$classes->items['tuitabs'] = array('kernel.admin.php', '', 'html.uitabs.class.php');
$classes->items['adminform'] = array('kernel.admin.php', '', 'html.adminform.class.php');
$classes->items['tableprop'] = array('kernel.admin.php', '', 'html.tableprop.class.php');
$classes->items['thtmltag'] = array('kernel.admin.php', '', 'html.tag.class.php');

unset($classes->items['tpullitems']);
$classes->items['tpoolitems'] = array('kernel.php', '', 'items.pool.class.php');

unset($classes->items['tcommentspull']);
$classes->items['tcommentspool'] = array('comments.pool.class.php', '');

$classes->items['targs'][2] = 'theme.args.class.php';
$classes->items['tview'][2] = 'view.class.php';
$classes->items['tpostfactory'][2] = 'post.factory.class.php';
$classes->save();

if (isset(litepublisher::$options->commentspull)) {
litepublisher::$options->commentspool = litepublisher::$options->commentspull;
unset(litepublisher::$options->data['commentspull']);
litepublisher::$options->save();
}

$parser = tmediaparser::i();
    if (!isset($parser->data['previewmode'])) {
    $parser->data['previewmode'] = $parser->data['clipbounds'] ? 'fixed' : 'max';
unset($parser->data['clipbounds']);
unset($parser->data['ratio']);
unset($parser->data['enablepreview']);
$parser->save();
}

$views = tviews::i();
foreach ($views->items as $id => &$item) {
$item['postanounce'] = 'excerpt';
$item['invertorder'] = false;
$item['perpage'] = 0;
}

$views->save();

$man = tdbmanager::i();
foreach (array('categories', 'tags') as $table) {
foreach (array('invertorder', 'lite', 'liteperpage') as $column) {
if ($man->column_exists($table, $column)) {
$man->alter($table, "drop $column");
}
}
}

include(dirname(__file__) . '/update.5.99.php');
update599();

tjsmerger::i()->deletesection('adminviews');
  tcssmerger::i()->deletefile('admin', '/js/litepubl/admin/css/admin.views.min.css');

$classes->add('tadminviewsgroup', 'admin.views.group.class.php');

$m = tadminmenus::i();
$m->lock();
$id = $m->url2id('/admin/views/group/');
$m->items[$id]['class'] = 'tadminviewsgroup';
litepublisher::$urlmap->setvalue(litepublisher::$urlmap->urlexists('/admin/views/group/'),
'class', 'tadminviewsgroup');

$m->unlock();
}