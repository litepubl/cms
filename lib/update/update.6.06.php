<?php
function update606() {
$classes = litepublisher::$classes;
unset($classes->items['tadmincommoncomments']);
unset($classes->items['ttablecolumns']);

$classes->items['tadmintagswidget'][0] = 'admin.widget.tags.class.php';
$classes->items['tadminmaxcount'][0]  = 'admin.widget.maxcount.class.php';
$classes->items['tadminshowcount'][0] = 'admin.widget.showcount.class.php';
$classes->items['tadminorderwidget'][0] = 'admin.widget.order.class.php';
$classes->items['tadmincustomwidget'][0] = 'admin.widget.custom.class.php';
$classes->items['tadminlinkswidget'][0] = 'admin.widget.links.class.php';
$classes->items['tadminmetawidget'][0] = 'admin.widget.meta.class.php';
$classes->save();

tjsmerger::i()->add('default', '/js/litepubl/system/storage.min.js');
}