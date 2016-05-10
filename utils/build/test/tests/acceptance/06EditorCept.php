<?php 

use Page\Editor;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test post editor');
$editor = new Editor($i);
$editor->open();

$data = $editor->load('editor');
$editor->fillTitleContent($data->title, $data->content);
$editor->upload('img1.jpg');
//$editor->upload('img2.jpg');