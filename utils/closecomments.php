<?php
/* close all comments */
$mode = 'fix';
include('index.php');
echo "<pre>\nStart update from $Options->version\n";
  $Options->commentsenabled = false;
  $Options->pingenabled = false;

$posts = tposts::i();
foreach ($posts->items as $id => $item) {
$post = tpost::i($id);
$post->commentsenabled = false;
$post->pingenabled = false;
$post->save();
}

litepublisher::$urlmap->clearcache();
echo "comments must be closed";
