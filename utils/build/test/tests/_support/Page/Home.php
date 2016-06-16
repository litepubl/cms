<?php
namespace Page;

class Home extends Base
{
    public  $url = '/admin/options/home/';
public $image = '#text-image';
public $smallimage = '#text-smallimage';

public function setimage(string $filename)
{
$this->upload($filename);
$r = $this->js('upload.home.js');
if ($r) {
codecept_debug(var_export($r, true));
}
}

}