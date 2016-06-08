<?php
namespace Page;

class Posts extends Base
{
    public  $url = '/admin/posts/';
public $deleteButton = '#submitbutton-delete';
public $screenShotName = '';

public function delete()
{
$list = func_get_args();
if (count($list)) {
$this->open();
$i = $this->tester;
$i->wantTo('Delete posts');
$i->screenShot($this->screenShotName . '1table');
foreach ($list as $id) {
$i->checkOption("input[name=checkbox-$id]");
}

$i->screenShot($this->screenShotName . '2checked');
$i->click($this->deleteButton);
$i->checkError();
$i->screenShot($this->screenShotName . '3deleted');
}
}

}