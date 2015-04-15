<?php

function checkperm($path) {
echoperms($path , '0777');
    if ( $h = @opendir($path)) {
      while(FALSE !== ($filename = @readdir($h))) {
        if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) continue;
        if (@is_dir($path . $filename)) {
checkperm($path . $filename . DIRECTORY_SEPARATOR);
        } else {
echoperms($path . $filename, '0666');
        }
      }
      @closedir($h);
    }
  }

function echoperms($filename, $perms) {
$p = substr(sprintf('%o', fileperms($filename)), -4);
if ($p == '0777') return;
if ($p != $perms)
echo " $p $filename<br>\n";
}
  
checkperm('data/');
?>