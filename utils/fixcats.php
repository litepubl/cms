<?php
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);

define('litepublisher_mode', 'xmlrpc');
include('index.php');
litepublisher::$debug = true;

$cats = tcategories::i();
$cats->loadall();
foreach ($cats->items as $id => $item) {
if ($parent = (int) $item['parent']) {
if (!isset($cats->items[$parent])) {
$cats->setvalue($id, 'parent', 0);
echo $item['title'], ' has invalid parent, category moved to up<br>';
}
}
}