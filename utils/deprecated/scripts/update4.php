<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
require(litepublisher::$paths->lib . 'update' . DIRECTORY_SEPARATOR  . 'update.4php');
echo "<pre>\n";
echo litepublisher::$options->version, "\n";
create_storage();
update4();
echo "updated";
return;

if (isset(litepublisher::$site)) {
} else {
tupdater::instance()->autoupdate();
}