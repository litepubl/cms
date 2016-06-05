<?php
namespace Page;

class Editor extends Base
{
    public $url = '/admin/posts/editor/';
public $title = '#text-title';
public $content = '#editor-raw';
public $category = 'input[name=category-1]';
public $calendar = '#calendar-posted';
public $upload = null;
 //'#file-input';
public $uploadJS;

public function fillTitleContent($title, $content)
{
$i = $this->tester;
$i->fillField($this->title, $title);
$i->fillField($this->content, $content);
}

public function upload($filename)
{
if (!$this->uploadJS) {
$this->uploadJS = file_get_contents(__DIR__ . '/js/upload.js');
}

$i = $this->tester;
if (!$this->upload) {
$this->upload = '#tempfile-input';
$i->executeJs(
'$(\'<input type="file" id="tempfile-input" />\').appendTo(\'body\');'
);
} else {
$i->executeJs('$(\'#tempfile-input\').removeClass(\'hidden\');');
}

$i->attachFile($this->upload, $filename);
$i->checkError();
$r = $i->executeJs($this->uploadJS);
if ($r) {
codecept_debug(var_export($r, true));
}

return $this;
}

public function submit()
{
$i = $this->tester;
$i->executeJs('$("form:last").submit();');
$i->checkError();
}

}
