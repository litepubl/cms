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

$classes->save();
}