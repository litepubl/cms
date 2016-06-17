<?php
namespace Page;

class Editor extends Base
{
    public $url = '/admin/posts/editor/';
public $title = '#text-title';
public $content = '#editor-raw';
public $category = 'input[name=category-1]';
public $calendar = '#calendar-posted';
public $postbookmark = '.post-bookmark';
public $idpost = 'input[name=id]';
public $datePicker = '.ui-datepicker';

public function fillTitleContent(string $title, string $content)
{
$i = $this->tester;
$i->fillField($this->title, $title);
$i->fillField($this->content, $content);
}

public function upload(string $filename)
{
parent::upload($filename);
$r = $this->js('upload.js');
if ($r) {
codecept_debug(var_export($r, true));
}
}

public function submit()
{
$i = $this->tester;
$i->executeJs('$("form:last").submit();');
usleep(250000);
$i->checkError();
}

public function getPostLink()
{
return $this->tester->grabAttributeFrom($this->postbookmark, 'href');
}

public function getPostId()
{
return $this->tester->grabAttributeFrom($this->idpost, 'value');
}

}