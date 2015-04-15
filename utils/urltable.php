<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
litepublisher::$urlmap->loadall();
foreach (litepublisher::$urlmap->items as $id => $item) {
echo $item['url'], ': ', $item['class'], '<br>';
}
?>