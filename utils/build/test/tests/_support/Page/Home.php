<?php
namespace Page;

class Home extends Base
{
    public  $url = '/admin/options/home/';
public $imageTab = '#tab-1';
public $image = '#text-image';
public $smallimage = '#text-smallimage';

public function uploadImage(string $filename)
{
$this->upload($filename);
$r = $this->js('home.upload.js');
if ($r) {
codecept_debug(var_export($r, true));
}

$this->tester->waitForJs($this->getFile(__DIR__ . '/js/home.wait.js'), 3);
}

}