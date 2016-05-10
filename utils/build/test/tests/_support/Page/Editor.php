<?php
namespace Page;

class Editor extends Base
{

    public $url = '/admin/posts/editor/';
public $title = '#text-title';
public $content = '#editor-raw';
public $upload = '#file-input';


public function fillTitleContent($title, $content)
{
$i = $this->tester;
$i->fillField($this->title, $title);
$i->fillField($this->content, $content);
}

public function upload($filename)
{
$this->tester->attachFile($this->upload, $filename);

}

}