<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');

$db = litepublisher::$db;;
$db->table = 'posts';
$items = $db->res2assoc($db->query("select id, categories, tags from $db->posts"));

$tags = ttags::instance();
$tags->lock();
$tags->itemsposts->lock();
$cats = tcategories::instance();
$cats->lock();
$cats->itemsposts->lock();

foreach ($items as $item) {
  $cats->itemsposts->setitems($item['id'], explode(',', $item['categories']));
  $tags->itemsposts->setitems($item['id'], explode(',', $item['tags']));
}
$tags->itemsposts->unlock();
$tags->unlock();
$cats->itemsposts->unlock();
$cats->unlock();

litepublisher::$urlmap->clearcache();
echo "updated<br>\n";
?>