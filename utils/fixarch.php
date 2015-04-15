<?php
$mode = 'fix';
include('index.php');
$posts = &TPosts::Instance();
foreach ($posts->items as $id => $item) {
if (isset($item['status')  && ($item['status'] == 'published')) unset($posts->items[$id]['status']);
}
$post = &TPost::Instance($id);
$posts->Updated($post);
$posts->Save();

$arch = &TArchives::Instance();
$arch->PostsChanged();
echo "update finished";
?>