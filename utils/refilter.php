<?php
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);
define('litepublisher_mode', 'xmlrpc');
include('index.php');
set_time_limit(600);
echo "<pre>\n";
$posts = tposts::i();
echo "$posts->revision\n";

$db = litepublisher::$db;
$r = $db->res2id($db->query("select max(revision) from $db->posts"));
$revision = (int) $r[0];

if ($posts->revision <= $revision) {
$posts->revision = $revision;
}

$posts->addrevision();
echo "$posts->revision\n";
litepublisher::$options->savemodified();

$filter = tcontentfilter::i();
$from = 0;
while ($a = $db->res2assoc($db->query("select id, rawcontent from $db->rawcomments where id > $from limit 600"))) {
$db->table = 'comments';
foreach ($a as $item) {
$s = $filter->filtercomment($item['rawcontent']);
$db->setvalue($item['id'], 'content', $s);
$from = max($from, $item['id']);
}
unset($a);
echo "$from\n";
flush();
}
echo "$from\n";
echo round(memory_get_usage()/1024/1024, 2), "MB\n"; 
echo round(memory_get_peak_usage(true)/1024/1024, 2), "MB\n"; 
echo round(microtime(true) - litepublisher::$microtime, 2), "Sec\n";
