<?php
$mode = 'clear';
include('index.php');

function clearlinks($tags) {
global $Urlmap;
$Urlmap->DeleteClass(get_class($tags));
foreach ($tags->items as $id => $item) {
$tags->AddUrl($id, $item['url']);
}
}

$Urlmap = TUrlmap::Instance();
$Urlmap->Lock();
clearlinks(TCategories::Instance());
clearlinks(TTags::Instance());
$Urlmap->Unlock();

echo "finished";
?>