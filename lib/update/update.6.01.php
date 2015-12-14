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
$classes->items['tpostswidget'][2] = 'widget.posts.class.php';
$classes->items['ttagfactory'][2] = 'tags.factory.class.php';
$classes->items['tcommontagswidget'][2] = 'widget.commontags.class.php';
$classes->items['tcategories'][2] = 'tags.categories.class.php';
$classes->items['tcategorieswidget'][2] = 'widget.categories.class.php';
$classes->items['ttagswidget'][2] = 'widget.tags.class.php';
$classes->items['ttags'][2] = 'tags.class.php';
$classes->items['twidget'][2] = 'widget.class.php';
$classes->items['torderwidget'][2] = 'widget.order.class.php';
$classes->items['tclasswidget'][2] = 'widget.class.class.php';
$classes->items['twidgetscache'][2] = 'widgets.cache.class.php';
$classes->items['tevents_itemplate'][2] = 'events.itemplate.class.php';
$classes->items['titems_itemplate'][2] = 'items.itemplate.class.php';
$classes->items['titems'] = array('kernel.php', '', ' items.class.php');
$classes->items['titems_storage'] = array('kernel.php', '', 'items.storage.class.php');
$classes->items['tsingleitems'] = array('kernel.php', '', 'items.single.class.php');
$classes->items['titem'] = array('kernel.php', '', 'item.class.php');
$classes->items['titem_storage'] = array('kernel.php', '', 'item.storage.class.php');
$classes->items['tevents'] = array('kernel.php', '', 'events.class.php');
$classes->items['tevents_storage'] = array('kernel.php', '', 'events.storage.class.php');
$classes->items['tcoevents'] = array('kernel.php', '', 'events.coclass.php');
$classes->items['ECancelEvent'] = array('kernel.php', '', 'events.exception.class.php');
$classes->items['tarray2prop'] = array('kernel.php', '', 'array2prop.class.php');
$classes->items['basetheme'] = array('kernel.templates.php', '', 'theme.base.class.php');
$classes->items['admintheme'] = array('kernel.admin.php', '', 'theme.admin.class.php');
$classes->items['tauthor_rights'] = array('kernel.admin.php', '', 'author-rights.class.php');
$classes->items['tadminmenus'] = array('kernel.admin.php', '', 'menus.admin.class.php');
$classes->items['baseparser'] = array('theme.baseparser.class.php', '',);
$classes->items['adminparser'] = array('theme.adminparser.class.php', '');
$classes->items['inifiles'] = array('kernel.templates.php', '', 'inifiles.class.php');
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
$classes->add('tadminheaders', 'admin.headers.class.php');
$classes->add('tadminviewsspec', 'admin.views.spec.class.php');
$classes->delete('tadminthemefiles');

$m = tadminmenus::i();
$m->lock();

$id = $m->url2id('/admin/views/group/');
$m->items[$id]['class'] = 'tadminviewsgroup';
litepublisher::$urlmap->setvalue(litepublisher::$urlmap->urlexists('/admin/views/group/'),
'class', 'tadminviewsgroup');

$id = $m->url2id('/admin/views/headers/');
$m->items[$id]['class'] = 'tadminheaders';
litepublisher::$urlmap->setvalue(litepublisher::$urlmap->urlexists('/admin/views/headers/'),
'class', 'tadminheaders');

$id = $m->url2id('/admin/views/spec/');
$m->items[$id]['class'] = 'tadminviewsspec';
litepublisher::$urlmap->setvalue(litepublisher::$urlmap->urlexists('/admin/views/spec/'),
'class', 'tadminviewsspec');

$m->deleteurl('/admin/views/themefiles/');

$m->unlock();

foreach (array('tcategories', 'ttags', 'tarchives', 'tuserpages') as $classname) {
$obj = getinstance($classname);
if (isset($obj->data['lite'])) {
unset($obj->data['lite']);
$obj->save();
}
}

$home = thomepage::i();
if (isset($home->data['invertorder'])) {
unset($home->data['invertorder']);
$home->save();
}
}