<?php

namespace litepubl\admin;

use litepubl\post\Files;
use litepubl\view\Admin;
use litepubl\view\Args;

class FileList
{
    public $onFilePerm;
private $adminTheme;

public function __construct(Admin $adminTheme)
{
$this->adminTheme = $adminTheme;
}

public function getFiles()
{
return Files::i();
}

    public function get(array $list): string
    {
        $files = $this->getFiles();
$app = $this->adminTheme->getApp();
        $args = new Args();
        $args->fileperm = '';

        if (is_callable($this->onFilePerm)) {
            call_user_func_array($this->onFilePerm, [$args]);
        } elseif ($app->options->show_file_perm) {
            $args->fileperm = GetPerm::combo(0, 'idperm_upload');
        }

        $where = $app->options->inGroup('editor') ? '' : ' and author = ' . $app->options->user;
        $db = $files->db;
        //total count files
        $args->count = (int)$db->getcount(" parent = 0 $where");
        //already loaded files
        $args->items = '{}';
        // attrib for hidden input
        $args->files = '';

        if (count($list)) {
            $items = implode(',', $list);
            $args->files = $items;
            $args->items = Str::toJson($db->res2items($db->query("select * from $files->thistable where id in ($items) or parent in ($items)")));
        }

        return $this->adminTheme->parseArg($this->adminTheme->templates['posteditor.filelist'], $args);
    }

}
