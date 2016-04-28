/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;<?php
use litepubl\core\Str;

function update615() {
$cl =  $this->getApp()->classes;
unset($cl->data['factories']);
unset($cl->data['classes']);
$cl->kernel = [];

unset($cl->items['tini2array']);
unset($cl->items['inifiles']);
unset($cl->items['tabstractpingbacks']);
unset($cl->items['tautoform']);
unset($cl->items['adminitems']);

$a = include(__DIR__ . '/classmap.php');
foreach ($a as $oldclass => $newclass) {
unset($cl->items[$oldclass]);
unset($cl->kernel[$oldclass]);
}

$cl->save();

$m = tmenus::i();
$admin = Menus::i();
foreach ([
'tadminservice' => 'litepubl\admin\Service',

] as $oldclass => $newclass) {
             $this->getApp()->router->db->update('class =' . Str::quote($newclass) , 'class = ' . Str::quote($oldclass));
$m->renameClass($oldclass, $newclass);
$admin->renameClass($oldclass, $newclass);
}

$m->save();
$admin->save();

    $xmlrpc = TXMLRPC::i();
    $xmlrpc->deleteclass('twidgets');

$man = dbmanager::i();
foreach ([
'posts',
'userpage',
'categories',
'tags',
] as $table) {
if ($man->columnExists($table, 'idview')) {
$man->alter($table, "change idview idschema int unsigned NOT NULL default '1'");
}
}

}