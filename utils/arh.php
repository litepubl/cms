<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
$linkgen = tlinkgenerator::instance();
$linkgen->archive = '/[year]/[month]/';
$linkgen->save();

$arch = tarchives::instance();
$arch->postschanged();
litepublisher::$urlmap->clearcache();
?>