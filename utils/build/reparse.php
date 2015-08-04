<?php
error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
ini_set('display_errors', 1);
 Header( 'Cache-Control: no-cache, must-revalidate');
  Header( 'Pragma: no-cache');

echo file_get_contents('http://cms/utils/build/libparse.php?dir=lib');
flush();
//@unlink('plugins/sape/blogolet.ru.links.db');
echo file_get_contents('http://cms/utils/build/libparse.php?dir=js');
flush();
echo file_get_contents('http://cms/utils/build/libparse.php?dir=plugins');
flush();
//echo file_get_contents('http://cms/utils/build/libparse.php?dir=geo');
flush();
