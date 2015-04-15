<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function CheckSimpleSpamProtection() {
 if (isset($_POST) && isset($_POST['FormValue'])) {
  $TimeKey = substr($_POST['FormValue'], strlen('_Value'));
  return time() < $TimeKey;
 }
 return false;
}
?>