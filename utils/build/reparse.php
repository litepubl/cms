<?php
error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
ini_set('display_errors', 1);
 Header( 'Cache-Control: no-cache, must-revalidate');
  Header( 'Pragma: no-cache');

set_time_limit(300);
$context = stream_context_create(array(
 'http'=>array(
'timeout' => 300.0,
)
));

echo file_get_contents('http://cms/utils/build/libparse.php?dir=lib', false, $context);
flush();
//@unlink('plugins/sape/blogolet.ru.links.db');
echo file_get_contents('http://cms/utils/build/libparse.php?dir=js', false, $context);
flush();
echo file_get_contents('http://cms/utils/build/libparse.php?dir=plugins', false, $context);
flush();
//echo file_get_contents('http://cms/utils/build/libparse.php?dir=geo', false, $context);
flush();
