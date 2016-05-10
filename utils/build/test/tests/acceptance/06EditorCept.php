<?php 

use Page\Editor;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test post editor');
$editor = new Editor($i);
$editor->open();
$editor->upload('img1.jpg');

$data = $editor->load('editor');
$editor->fillTitleContent($data->title, $data->content);

$i->checkError();
$i->executeJs('$("form:last").submit();');
$i->checkError();
