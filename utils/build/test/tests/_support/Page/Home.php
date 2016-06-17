<?php
namespace Page;

class Home extends Base
{
    public  $url = '/admin/options/home/';
public $image = '#text-image';
public $smallimage = '#text-smallimage';

public function uploadImage(string $filename)
{
$this->upload($filename);
$r = $this->js('home.upload.js');
if ($r) {
codecept_debug(var_export($r, true));
}

$i->waitForJs($this->getFile(__DIR__ . '/js/home.wait.js', 3);
}

}